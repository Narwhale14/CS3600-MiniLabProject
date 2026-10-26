<?php
require_once __DIR__ . '/db.php';

session_start();

const BASE_URL = 'http://localhost:5173/';

function respond(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function fail(string $error, int $status = 400): never {
    respond(['error' => $error], $status);
}

function query(string $sql, array $params = []): PDOStatement {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function validURL(string $url): bool {
    return preg_match('~^https?://~i', $url) && filter_var($url, FILTER_VALIDATE_URL);
}

function validCode(string $code): bool {
    return preg_match('/^[a-zA-Z0-9_-]{1,24}$/D', $code) === 1;
}

function validUser(string $username, string $email): bool {
    return preg_match('/^[a-zA-Z0-9_]{1,50}$/D', $username) && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 255;
}

function currentUserID(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function requireLogin(): int {
    return currentUserID() ?? fail('Not logged in', 401);
}

function getSession(): array {
    return ['loggedIn' => currentUserID() !== null];
}

function createLink(array $data): array {
    $userID = currentUserID();
    $link = trim($data['link'] ?? '');
    $code = trim($data['code'] ?? '');
    $private = (int) !empty($data['private']);
    if($code === '') $code = bin2hex(random_bytes(6));

    if(!validURL($link)) fail('Invalid URL');
    if($private && $userID === null) fail('Login required for private links', 401);
    if(!validCode($code)) fail('Invalid code');

    query("INSERT INTO links (user_id, link, code, private, created_at) VALUES (?, ?, ?, ?, NOW())", [$userID, $link, $code, $private]);
    return ['shortlink' => BASE_URL . $code];
}

function getLinks(): array {
    $userID = requireLogin();
    return ['links' => query("SELECT link_id, link, code, private FROM links WHERE user_id = ? ORDER BY link_id DESC", [$userID])->fetchAll()];
}

function editLink(array $data): array {
    $userID = requireLogin();
    $link = trim($data['link'] ?? '');
    $code = trim($data['code'] ?? '');
    if(!validURL($link) || !validCode($code)) fail('Invalid link or code');

    query("UPDATE links SET link = ?, code = ?, private = ? WHERE link_id = ? AND user_id = ?", [$link, $code, (int) !empty($data['private']), $data['link_id'] ?? null, $userID]);
    return [];
}

function deleteLink(array $data): array {
    $userID = requireLogin();
    query("DELETE FROM links WHERE link_id = ? AND user_id = ?", [$data['link_id'] ?? null, $userID]);
    return [];
}

function signup(array $data): array {
    global $pdo;
    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    if(!validUser($username, $email) || strlen($password) < 8) fail('Invalid registration information');

    query("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())", [$username, $email, password_hash($password, PASSWORD_DEFAULT)]);
    $_SESSION['user_id'] = (int) $pdo->lastInsertId();
    return [];
}

function login(array $data): array {
    $user = trim($data['user'] ?? '');
    $account = query("SELECT user_id, password FROM users WHERE username = ? OR email = ?", [$user, $user])->fetch();
    if(!$account || !password_verify($data['password'] ?? '', $account['password'])) fail('Invalid credentials', 401);

    $_SESSION['user_id'] = (int) $account['user_id'];
    return [];
}

function getUser(): array {
    $userID = requireLogin();
    return query("SELECT username, email FROM users WHERE user_id = ?", [$userID])->fetch();
}

function editUser(array $data): array {
    $userID = requireLogin();
    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    if(!validUser($username, $email)) fail('Invalid user information');

    query("UPDATE users SET username = ?, email = ? WHERE user_id = ?", [$username, $email, $userID]);
    return [];
}

function changePassword(array $data): array {
    $userID = requireLogin();
    $newPassword = $data['newPassword'] ?? '';
    if(strlen($newPassword) < 8) fail('Invalid password');

    $hash = query("SELECT password FROM users WHERE user_id = ?", [$userID])->fetchColumn();
    if(!password_verify($data['currentPassword'] ?? '', $hash)) fail('Current password is incorrect', 403);

    query("UPDATE users SET password = ? WHERE user_id = ?", [password_hash($newPassword, PASSWORD_DEFAULT), $userID]);
    return [];
}

function logout(): array {
    session_destroy();
    return [];
}

if($_SERVER['REQUEST_METHOD'] === 'GET') {
    $record = query("SELECT link, user_id, private FROM links WHERE code = ?", [$_GET['code'] ?? ''])->fetch();
    if(!$record) fail('Link not found', 404);

    $allowed = !$record['private'] || (int) $record['user_id'] === currentUserID();
    header('Location: ' . ($allowed ? $record['link'] : BASE_URL));
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    respond(match($data['action'] ?? '') {
        'getSession' => getSession(),
        'createLink' => createLink($data),
        'getLinks' => getLinks(),
        'editLink' => editLink($data),
        'deleteLink' => deleteLink($data),
        'signup' => signup($data),
        'login' => login($data),
        'getUser' => getUser(),
        'editUser' => editUser($data),
        'changePassword' => changePassword($data),
        'logout' => logout(),
        default => fail('Unknown action'),
    });
} catch(PDOException $e) {
    if($e->getCode() !== '23000') throw $e;
    fail('Already taken', 409);
}

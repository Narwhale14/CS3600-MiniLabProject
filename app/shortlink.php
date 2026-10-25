
<?php

session_start();

require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

function respond(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function inputString(array $data, string $key): string {
    return is_string($data[$key] ?? null)
        ? trim($data[$key])
        : '';
}

function validURL(string $url): bool {
    $scheme = parse_url($url, PHP_URL_SCHEME);

    return filter_var($url, FILTER_VALIDATE_URL) !== false
        && is_string($scheme)
        && in_array(strtolower($scheme), ['http', 'https'], true);
}

function blockedURL(string $url): bool {
    $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
    $port = parse_url($url, PHP_URL_PORT);

    return in_array($host, ['localhost', '127.0.0.1'], true)
        && $port === 5173;
}

function validCode(string $code): bool {
    return preg_match('/^[a-zA-Z0-9_-]{1,24}$/D', $code) === 1;
}

function currentUserID(): ?int {
    return isset($_SESSION['user_id'])
        ? (int) $_SESSION['user_id']
        : null;
}

function requireLogin(): int {
    $userID = currentUserID();

    if ($userID === null) {
        respond(['error' => 'Not logged in'], 401);
    }

    return $userID;
}

function validID($id): bool {
    return filter_var(
        $id,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    ) !== false;
}

function shortURL(string $code): string {
    return 'http://localhost:5173/' . rawurlencode($code);
}

// Redirect shortened links.
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $code = $_GET['code'] ?? '';

    if (!is_string($code) || !validCode($code)) {
        respond(['error' => 'Invalid code'], 400);
    }

    $stmt = $pdo->prepare(
        "SELECT link, user_id, private
         FROM links
         WHERE code = ?"
    );

    $stmt->execute([$code]);
    $record = $stmt->fetch();

    if (!$record) {
        respond(['error' => 'Link not found'], 404);
    }

    if ($record['private']) {
        $userID = currentUserID();

        if ($userID === null ||
            (int) $record['user_id'] !== $userID) {
            header_remove('Content-Type');
            header('Location: http://localhost:5173/', true, 302);
            exit;
        }
    }

    header_remove('Content-Type');
    header('Location: ' . $record['link'], true, 302);
    exit;
}

// All other operations use POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    respond(['error' => 'Invalid JSON'], 400);
}

$action = $data['action'] ?? '';

if (!is_string($action)) {
    respond(['error' => 'Invalid action'], 400);
}

// Initialize CSRF protection.
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($action === 'getSession') {
    respond([
        'success' => true,
        'loggedIn' => currentUserID() !== null,
        'user_id' => currentUserID(),
        'csrfToken' => $_SESSION['csrf_token']
    ]);
}

// Validate CSRF token.
$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

if (!is_string($csrfToken) ||
    !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    respond(['error' => 'Invalid CSRF token'], 403);
}

try {

    switch ($action) {

        case 'createLink':
            $link = inputString($data, 'link');
            $code = inputString($data, 'code');
            $private = ($data['private'] ?? false) === true ? 1 : 0;
            $userID = currentUserID();

            if (!validURL($link)) {
                respond(['error' => 'Invalid URL'], 400);
            }

            if (blockedURL($link)) {
                respond([
                    'error' => 'Links to this website are not allowed'
                ], 400);
            }

            if ($private && $userID === null) {
                respond([
                    'error' => 'Login required for private links'
                ], 401);
            }

            $customCode = $code !== '';

            if ($customCode && !validCode($code)) {
                respond(['error' => 'Invalid code'], 400);
            }

            for ($attempt = 0; $attempt < 5; $attempt++) {
                if (!$customCode) {
                    $code = bin2hex(random_bytes(6));
                }

                try {
                    $stmt = $pdo->prepare(
                        "INSERT INTO links
                         (user_id, link, code, private, created_at)
                         VALUES (?, ?, ?, ?, NOW())"
                    );

                    $stmt->execute([
                        $userID,
                        $link,
                        $code,
                        $private
                    ]);

                    respond([
                        'success' => true,
                        'link_id' => (int) $pdo->lastInsertId(),
                        'shortlink' => shortURL($code)
                    ]);

                } catch (PDOException $e) {
                    if ($e->getCode() !== '23000') {
                        throw $e;
                    }

                    if ($customCode) {
                        respond([
                            'error' => 'Code already exists'
                        ], 409);
                    }
                }
            }

            respond([
                'error' => 'Could not generate a unique code'
            ], 500);

        case 'getLinks':
            $userID = requireLogin();

            $stmt = $pdo->prepare(
                "SELECT link_id, link, code, private, created_at
                 FROM links
                 WHERE user_id = ?
                 ORDER BY link_id DESC"
            );

            $stmt->execute([$userID]);

            respond([
                'success' => true,
                'links' => $stmt->fetchAll()
            ]);

        case 'editLink':
            $userID = requireLogin();
            $linkID = $data['link_id'] ?? null;

            if (!validID($linkID)) {
                respond(['error' => 'Invalid link ID'], 400);
            }

            $link = inputString($data, 'link');
            $code = inputString($data, 'code');
            $private = ($data['private'] ?? false) === true ? 1 : 0;

            if (!validURL($link) || !validCode($code)) {
                respond(['error' => 'Invalid link or code'], 400);
            }

            if (blockedURL($link)) {
                respond([
                    'error' => 'Links to this website are not allowed'
                ], 400);
            }

            $stmt = $pdo->prepare(
                "SELECT link_id FROM links
                 WHERE link_id = ? AND user_id = ?"
            );

            $stmt->execute([$linkID, $userID]);

            if (!$stmt->fetch()) {
                respond(['error' => 'Link not found'], 404);
            }

            $stmt = $pdo->prepare(
                "UPDATE links
                 SET link = ?, code = ?, private = ?
                 WHERE link_id = ? AND user_id = ?"
            );

            $stmt->execute([
                $link,
                $code,
                $private,
                $linkID,
                $userID
            ]);

            respond([
                'success' => true,
                'shortlink' => shortURL($code)
            ]);

        case 'deleteLink':
            $userID = requireLogin();
            $linkID = $data['link_id'] ?? null;

            if (!validID($linkID)) {
                respond(['error' => 'Invalid link ID'], 400);
            }

            $stmt = $pdo->prepare(
                "DELETE FROM links
                 WHERE link_id = ? AND user_id = ?"
            );

            $stmt->execute([$linkID, $userID]);

            if ($stmt->rowCount() === 0) {
                respond(['error' => 'Link not found'], 404);
            }

            respond(['success' => true]);

        case 'signup':
            $username = inputString($data, 'username');
            $email = inputString($data, 'email');
            $password = $data['password'] ?? '';

            if (!preg_match('/^[a-zA-Z0-9_]+$/D', $username) ||
                strlen($username) > 50 ||
                strlen($username) === 0 ||
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                strlen($email) > 255 ||
                !is_string($password) ||
                strlen($password) < 8) {
                respond([
                    'error' => 'Invalid registration information'
                ], 400);
            }

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare(
                "INSERT INTO users
                 (username, email, password, created_at)
                 VALUES (?, ?, ?, NOW())"
            );

            $stmt->execute([
                $username,
                $email,
                $hashedPassword
            ]);

            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $pdo->lastInsertId();

            respond([
                'success' => true,
                'user_id' => $_SESSION['user_id']
            ]);

        case 'login':
            $user = inputString($data, 'user');
            $password = $data['password'] ?? '';

            if ($user === '' || !is_string($password)) {
                respond(['error' => 'Invalid credentials'], 401);
            }

            $stmt = $pdo->prepare(
                "SELECT user_id, username, password
                 FROM users
                 WHERE username = ? OR email = ?
                 LIMIT 1"
            );

            $stmt->execute([$user, $user]);
            $account = $stmt->fetch();

            if (!$account ||
                !password_verify($password, $account['password'])) {
                respond(['error' => 'Invalid credentials'], 401);
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $account['user_id'];

            respond([
                'success' => true,
                'user_id' => $_SESSION['user_id'],
                'username' => $account['username']
            ]);

        case 'getUser':
            $userID = requireLogin();

            $stmt = $pdo->prepare(
                "SELECT username, email
                 FROM users
                 WHERE user_id = ?"
            );

            $stmt->execute([$userID]);
            $user = $stmt->fetch();

            if (!$user) {
                respond(['error' => 'User not found'], 404);
            }

            respond([
                'success' => true,
                'username' => $user['username'],
                'email' => $user['email']
            ]);

        case 'editUser':
            $userID = requireLogin();

            $username = inputString($data, 'username');
            $email = inputString($data, 'email');

            if (!preg_match('/^[a-zA-Z0-9_]+$/D', $username) ||
                strlen($username) > 50 ||
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                strlen($email) > 255) {
                respond(['error' => 'Invalid user information'], 400);
            }

            $stmt = $pdo->prepare(
                "UPDATE users
                 SET username = ?, email = ?
                 WHERE user_id = ?"
            );

            $stmt->execute([$username, $email, $userID]);

            respond(['success' => true]);

        case 'changePassword':
            $userID = requireLogin();

            $currentPassword = $data['currentPassword'] ?? '';
            $newPassword = $data['newPassword'] ?? '';

            if (!is_string($currentPassword) ||
                !is_string($newPassword) ||
                strlen($newPassword) < 8) {
                respond(['error' => 'Invalid password'], 400);
            }

            $stmt = $pdo->prepare(
                "SELECT password FROM users WHERE user_id = ?"
            );

            $stmt->execute([$userID]);
            $account = $stmt->fetch();

            if (!$account ||
                !password_verify(
                    $currentPassword,
                    $account['password']
                )) {
                respond([
                    'error' => 'Current password is incorrect'
                ], 403);
            }

            $hashedPassword = password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare(
                "UPDATE users SET password = ?
                 WHERE user_id = ?"
            );

            $stmt->execute([$hashedPassword, $userID]);

            session_regenerate_id(true);

            respond(['success' => true]);

        case 'logout':
            $_SESSION = [];

            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();

                setcookie(session_name(), '', [
                    'expires' => time() - 3600,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => 'Lax'
                ]);
            }

            session_destroy();

            respond(['success' => true]);

        default:
            respond(['error' => 'Unknown action'], 400);
    }

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        respond([
            'error' => 'Duplicate or conflicting database entry'
        ], 409);
    }

    error_log($e->getMessage());

    respond(['error' => 'Database error'], 500);
}

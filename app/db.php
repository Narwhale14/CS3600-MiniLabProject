<?php

$host = '127.0.0.1';
$port = 3306;
$user = 'shortlink';
$pass = 'shortlink';
$db = 'shortlink';

$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

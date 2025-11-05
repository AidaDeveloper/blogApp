<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../config.php';

$testPdo = new PDO(
    "mysql:host=localhost;dbname=blog_app;charset=utf8mb4",
    'blog_user',
    'StrongPassword123',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

// --- Создание таблиц, если они не существуют ---
$tablesSql = [

    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        login VARCHAR(255) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        content TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        likes INT DEFAULT 0,
        dislikes INT DEFAULT 0,
        views INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        text TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(post_id) REFERENCES posts(id) ON DELETE CASCADE,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

foreach ($tablesSql as $sql) {
    $testPdo->exec($sql);
}

$GLOBALS['users'] = new users();
$GLOBALS['posts'] = new posts();
$GLOBALS['comments'] = new comments();

foreach (['users', 'posts', 'comments'] as $objName) {
    $ref = new ReflectionClass($GLOBALS[$objName]);
    $pdoProp = $ref->getProperty('pdo');
    $pdoProp->setAccessible(true);
    $pdoProp->setValue($GLOBALS[$objName], $testPdo);
}

if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}
session_start();

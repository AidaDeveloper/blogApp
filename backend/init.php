<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/classes/database.class.php';
require_once __DIR__ . '/classes/users.class.php';
require_once __DIR__ . '/classes/posts.class.php';
require_once __DIR__ . '/classes/comments.class.php';

$GLOBALS['users'] = new users();
$GLOBALS['posts'] = new posts();
$GLOBALS['comments'] = new comments();

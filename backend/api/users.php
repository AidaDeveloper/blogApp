<?php
require_once '../init.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

if ($action === 'list') {
    try {
        $allUsers = $GLOBALS['users']->getAll();
        echo json_encode($allUsers);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>'Не удалось получить пользователей']);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Неизвестное действие']);
    exit;
}

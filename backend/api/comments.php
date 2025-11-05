<?php
require_once '../init.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$user_id = $GLOBALS['users']->isAuth() ? $_SESSION['user_id'] : null;
$data = json_decode(file_get_contents('php://input'), true);

header('Content-Type: application/json; charset=utf-8');

switch($action) {

    case 'by_post':
        $post_id = intval($_GET['id'] ?? 0);
        if (!$post_id) {
            http_response_code(400);
            echo json_encode(['status'=>'error','message'=>'ID поста обязателен']);
            exit;
        }

        $comments = $GLOBALS['comments']->get_by_post($post_id);

        echo json_encode(['status'=>'success','comments'=>$comments]);
        exit;
    case 'create':
        if (!$user_id) {
            http_response_code(401);
            echo json_encode(['status'=>'error','message'=>'Требуется авторизация']);
            exit;
        }

        $post_id = intval($data['post_id'] ?? 0);
        $text = trim($data['text'] ?? '');

        if (!$post_id || !$text) {
            http_response_code(400);
            echo json_encode(['status'=>'error','message'=>'Пост и текст комментария обязательны']);
            exit;
        }

        $id = $GLOBALS['comments']->create_comment($post_id, $user_id, $text);

        $comment = $GLOBALS['comments']->find($id);

        if (!$comment) {
            http_response_code(500);
            echo json_encode(['status'=>'error','message'=>'Не удалось создать комментарий']);
            exit;
        }

        echo json_encode(['status'=>'success','comment'=>$comment]);
        exit;


    case 'delete':
        if (!$user_id) {
            http_response_code(401);
            echo json_encode(['status'=>'error','message'=>'Требуется авторизация']);
            exit;
        }

        $id = intval($data['id'] ?? 0);
        $comment = $GLOBALS['comments']->get_by_user($user_id);
        $found = false;
        foreach($comment as $c) {
            if ($c['id'] == $id) { $found = true; break; }
        }
        if (!$found) {
            http_response_code(403);
            echo json_encode(['status'=>'error','message'=>'Нельзя удалить чужой комментарий']);
            exit;
        }

        $GLOBALS['comments']->delete_comment($id);
        echo json_encode(['status'=>'success']);
        exit;

    default:
        http_response_code(400);
        echo json_encode(['status'=>'error','message'=>'Неизвестное действие']);
        exit;
}

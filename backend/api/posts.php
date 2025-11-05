<?php
require_once '../init.php';
require_once '../classes/posts_votes.class.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$data = json_decode(file_get_contents('php://input'), true);

$user_id = $GLOBALS['users']->isAuth() ? $_SESSION['user_id'] : null;

switch($action) {

    case 'list':
        $posts = $GLOBALS['posts']->get_all_with_authors();
        echo json_encode(['status'=>'success','posts'=>$posts]);
        break;

    case 'single':
        $id = intval($_GET['id'] ?? 0);
        if(!$id){
            http_response_code(400);
            echo json_encode(['status'=>'error','message'=>'ID поста обязателен']);
            exit;
        }

        $post = $GLOBALS['posts']->find($id);
        if(!$post){
            http_response_code(404);
            echo json_encode(['status'=>'error','message'=>'Пост не найден']);
            exit;
        }


        echo json_encode(['status'=>'success','post'=>$post]);
        break;
    case 'single_no_view':
        $id = intval($_GET['id'] ?? 0);
        if(!$id){
            http_response_code(400);
            echo json_encode(['status'=>'error','message'=>'ID поста обязателен']);
            exit;
        }

        $post = $GLOBALS['posts']->find_with_author($id); // 🔹 вот тут метод с автором

        if(!$post){
            http_response_code(404);
            echo json_encode(['status'=>'error','message'=>'Пост не найден']);
            exit;
        }

        echo json_encode(['status'=>'success','post'=>$post]);
        break;
    case 'increment_view':
        $id = intval($data['id'] ?? 0);
        if(!$id){
            http_response_code(400);
            echo json_encode(['status'=>'error','message'=>'ID поста обязателен']);
            exit;
        }

        $post = $GLOBALS['posts']->find($id);
        if(!$post){
            http_response_code(404);
            echo json_encode(['status'=>'error','message'=>'Пост не найден']);
            exit;
        }

        $GLOBALS['posts']->increment_views($id);

        echo json_encode(['status'=>'success']);
        break;

    case 'create':
        if(!$user_id){
            http_response_code(401);
            echo json_encode(['status'=>'error','message'=>'Требуется авторизация']);
            exit;
        }

        $title = trim($data['title'] ?? '');
        $content = trim($data['content'] ?? '');
        if(!$title || !$content){
            http_response_code(400);
            echo json_encode(['status'=>'error','message'=>'Title и Content обязательны']);
            exit;
        }

        $postId = $GLOBALS['posts']->create_post($user_id, $title, $content);
        $post = $GLOBALS['posts']->find($postId);

        echo json_encode(['status'=>'success','post'=>$post]);
        break;
    case 'update':
        if(!$user_id){
            http_response_code(401);
            echo json_encode(['status'=>'error','message'=>'Требуется авторизация']);
            exit;
        }

        $id = intval($data['id'] ?? 0);
        $title = trim($data['title'] ?? '');
        $content = trim($data['content'] ?? '');

        if(!$id || !$title || !$content){
            http_response_code(400);
            echo json_encode(['status'=>'error','message'=>'ID, Title и Content обязательны']);
            exit;
        }

        $post = $GLOBALS['posts']->find($id);
        if(!$post){
            http_response_code(404);
            echo json_encode(['status'=>'error','message'=>'Пост не найден']);
            exit;
        }

        if($post['user_id'] != $user_id){
            http_response_code(403);
            echo json_encode(['status'=>'error','message'=>'Нельзя редактировать чужой пост']);
            exit;
        }

        $GLOBALS['posts']->update($id, ['title'=>$title,'content'=>$content]);

        $updatedPost = $GLOBALS['posts']->find_with_author($id);

        echo json_encode(['status'=>'success','post'=>$updatedPost]);
        break;

    case 'delete':
        if(!$user_id){
            http_response_code(401);
            echo json_encode(['status'=>'error','message'=>'Требуется авторизация']);
            exit;
        }

        $id = intval($data['id'] ?? 0);
        $post = $GLOBALS['posts']->find($id);
        if(!$post){
            http_response_code(404);
            echo json_encode(['status'=>'error','message'=>'Пост не найден']);
            exit;
        }

        if($post['user_id'] != $user_id){
            http_response_code(403);
            echo json_encode(['status'=>'error','message'=>'Нельзя удалить чужой пост']);
            exit;
        }

        $GLOBALS['posts']->delete($id);
        echo json_encode(['status'=>'success']);
        break;

    case 'like':
        if(!$user_id){
            http_response_code(401);
            echo json_encode(['status'=>'error','message'=>'Требуется авторизация']);
            exit;
        }

        $id = intval($data['id'] ?? 0);
        $GLOBALS['posts_votes']->set_vote($id, $user_id, 1);

        $votes = $GLOBALS['posts_votes']->get_votes_count($id);
        $GLOBALS['posts']->update($id, ['likes' => $votes['likes'], 'dislikes' => $votes['dislikes']]);

        echo json_encode(['status'=>'success', 'votes' => $votes]);
        break;

    case 'dislike':
        if(!$user_id){
            http_response_code(401);
            echo json_encode(['status'=>'error','message'=>'Требуется авторизация']);
            exit;
        }

        $id = intval($data['id'] ?? 0);
        $GLOBALS['posts_votes']->set_vote($id, $user_id, -1);

        $votes = $GLOBALS['posts_votes']->get_votes_count($id);
        $GLOBALS['posts']->update($id, ['likes' => $votes['likes'], 'dislikes' => $votes['dislikes']]);

        echo json_encode(['status'=>'success', 'votes' => $votes]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['status'=>'error','message'=>'Неизвестное действие']);
        break;
}

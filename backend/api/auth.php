<?php

require_once '../init.php';
$jwt_secret = 'YOUR_SECRET_KEY';


$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if($method !== 'POST'){
    http_response_code(405);
    echo json_encode(['status'=>'error','message'=>'Method Not Allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

switch($action){
    case 'register':
        $login = trim($data['login'] ?? '');
        $password = $data['password'] ?? '';

        if(!$login || !$password){
            http_response_code(400);
            echo json_encode(['status'=>'error','message'=>'Login и password обязательны']);
            exit;
        }

        if($GLOBALS['users']->exists($login)){
            http_response_code(409);
            echo json_encode(['status'=>'error','message'=>'Пользователь с таким логином уже существует']);
            exit;
        }

        $userId = $GLOBALS['users']->register($login, $password);
        if($userId){
            $_SESSION['user_id'] = $userId;
            echo json_encode(['status'=>'success','user_id'=>$userId,'login'=>$login]);
        } else {
            http_response_code(500);
            echo json_encode(['status'=>'error','message'=>'Не удалось зарегистрировать пользователя']);
        }
        break;

    case 'login':
        $login = trim($data['login'] ?? '');
        $password = $data['password'] ?? '';

        $user = $GLOBALS['users']->login($login, $password);
        if($user){
            $_SESSION['user_id'] = $user['id'];
            echo json_encode(['status'=>'success','user_id'=>$user['id'],'login'=>$user['login']]);
        } else {
            http_response_code(401);
            echo json_encode(['status'=>'error','message'=>'Неверные учетные данные']);
        }
        break;

    case 'logout':
        session_destroy();
        echo json_encode(['status'=>'success']);
        break;

    default:
        http_response_code(400);
        echo json_encode(['status'=>'error','message'=>'Неизвестное действие']);
}

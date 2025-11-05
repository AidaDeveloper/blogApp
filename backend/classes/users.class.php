<?php
class users extends database {
    public $primary_key = 'id';

    public $object_type = 'users';
    public $tablename = 'users';

    public $object_fields = [
        'id' =>
            ['type' => 'integer'],
        'login' =>
            ['type' => 'string'],
        'password_hash' =>
            ['type' => 'string'],
        'content' =>
            ['type' => 'string'],
        'created_at' =>
            ['type' => 'datetime'],
    ];

    public function __construct(){
        parent::__construct('users','id');
    }

    public function register($login, $password){
        $hash = password_hash($password, PASSWORD_BCRYPT);
        return $this->insert([
            'login'=>$login,
            'password_hash'=>$hash
        ]);
    }

    public function exists($login){
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE login=? LIMIT 1');
        $stmt->execute([$login]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    public function login($login, $password){
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE login=? LIMIT 1');
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user && password_verify($password, $user['password_hash'])){
            return $user;
        }
        return false;
    }

    public function isAuth(){
        return $_SESSION['user_id'] ?? false;
    }

    public function getAll(): array {
        $stmt = $this->pdo->prepare("SELECT id, login FROM users ORDER BY login ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$GLOBALS['users'] = new users();

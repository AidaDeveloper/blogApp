<?php

class comments extends database
{
    public $primary_key = 'id';
    public $object_type = 'comments';
    public $tablename = 'comments';

    public $object_fields = [
        'id' => ['type' => 'integer'],
        'post_id' => ['type' => 'integer'],
        'user_id' => ['type' => 'integer'],
        'text' => ['type' => 'string'],
        'created_at' => ['type' => 'datetime'],
    ];

    public function __construct()
    {
        parent::__construct('comments', 'id');
    }

    public function create_comment($post_id, $user_id, $text)
    {
        return $this->insert([
            'post_id' => $post_id,
            'user_id' => $user_id,
            'text' => $text
        ]);
    }
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->tablename} WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function get_by_post($post_id)
    {
        $sql = "SELECT * FROM {$this->tablename} WHERE post_id = :pid ORDER BY created_at ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['pid' => $post_id]);
        return $stmt->fetchAll();
    }
    public function find_with_author($id) {
        $sql = "SELECT c.*, u.login AS author_name, u.login AS author_login
            FROM {$this->tablename} c
            LEFT JOIN users u ON u.id = c.user_id
            WHERE c.id = :id
            LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function get_by_post_with_author($post_id) {
        $sql = "SELECT c.*, u.login AS author_name, u.login AS author_login
            FROM {$this->tablename} c
            LEFT JOIN users u ON u.id = c.user_id
            WHERE c.post_id = :post_id
            ORDER BY c.created_at ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['post_id' => $post_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function get_by_user($user_id)
    {
        $sql = "SELECT * FROM {$this->tablename} WHERE user_id = :uid ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['uid' => $user_id]);
        return $stmt->fetchAll();
    }


}

$GLOBALS['comments'] = new comments();

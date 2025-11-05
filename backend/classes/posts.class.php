<?php

class posts extends database
{
    public $primary_key = 'id';
    public $object_type = 'posts';
    public $tablename = 'posts';

    public $object_fields = [
        'id' =>
            ['type' => 'integer'],
        'user_id' =>
            ['type' => 'integer'],
        'title' =>
            ['type' => 'string'],
        'content' =>
            ['type' => 'string'],
        'created_at' =>
            ['type' => 'datetime'],
        'updated_at' =>
            ['type' => 'datetime'],
        'likes' =>
            ['type' => 'integer'],
        'dislikes' =>
            ['type' => 'integer'],
        'views' =>
            ['type' => 'integer'],
    ];

    public function __construct()
    {
        parent::__construct('posts', 'id');
    }
    public function get_all_with_authors() {
        $sql = "SELECT p.*, u.login as author, u.id as user_id
            FROM posts p
            JOIN users u ON u.id = p.user_id
            ORDER BY p.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find_with_author($id) {
        $sql = "SELECT p.*, u.login as author
            FROM posts p
            LEFT JOIN users u ON u.id = p.user_id
            WHERE p.id = :id
            LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function create_post($user_id, $title, $content)
    {
        return $this->insert([
            'user_id' => $user_id,
            'title' => $title,
            'content' => $content,
            'likes' => 0,
            'dislikes' => 0,
            'views' => 0
        ]);
    }

    public function increment_views($post_id)
    {
        $post = $this->find($post_id);
        if (!$post) return false;

        $new_views = $post['views'] + 1;
        return $this->update($post_id, ['views' => $new_views]);
    }

    public function add_like($post_id)
    {
        $post = $this->find($post_id);
        if (!$post) return false;

        $new_likes = $post['likes'] + 1;
        return $this->update($post_id, ['likes' => $new_likes]);
    }
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    public function add_dislike($post_id)
    {
        $post = $this->find($post_id);
        if (!$post) return false;

        $new_dislikes = $post['dislikes'] + 1;
        return $this->update($post_id, ['dislikes' => $new_dislikes]);
    }

    public function get_by_user($user_id)
    {
        $sql = "SELECT * FROM {$this->tablename} WHERE user_id = :uid ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['uid' => $user_id]);
        return $stmt->fetchAll();
    }
}

$GLOBALS['posts'] = new posts();

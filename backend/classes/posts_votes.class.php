<?php
class posts_votes extends database
{
    public $primary_key = 'id';
    public $tablename = 'post_votes';

    public $object_fields = [
        'id' =>
            ['type' => 'integer'],
        'post_id' =>
            ['type' => 'integer'],
        'user_id' =>
            ['type' => 'integer'],
        'vote' =>
            ['type' => 'integer'], // 1 = like, -1 = dislike
        'created_at' =>
            ['type' => 'datetime']
    ];

    public function __construct()
    {
        parent::__construct('post_votes', 'id');
    }

    public function get_user_vote($post_id, $user_id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT vote FROM {$this->tablename} WHERE post_id = :post_id AND user_id = :user_id LIMIT 1"
        );
        $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? intval($row['vote']) : 0;
    }

    public function set_vote($post_id, $user_id, $vote)
    {
        if (!in_array($vote, [1, -1])) return false;

        $existing = $this->get_user_vote($post_id, $user_id);

        if ($existing) {
            if ($existing == $vote) {
                $stmt = $this->pdo->prepare(
                    "DELETE FROM {$this->tablename} WHERE post_id = :post_id AND user_id = :user_id"
                );
                return $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
            } else {
                $stmt = $this->pdo->prepare(
                    "UPDATE {$this->tablename} SET vote = :vote, created_at = NOW() 
                     WHERE post_id = :post_id AND user_id = :user_id"
                );
                return $stmt->execute(['vote' => $vote, 'post_id' => $post_id, 'user_id' => $user_id]);
            }
        } else {
            return $this->insert([
                'post_id' => $post_id,
                'user_id' => $user_id,
                'vote' => $vote
            ]);
        }
    }

    public function get_votes_count($post_id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT 
                SUM(CASE WHEN vote = 1 THEN 1 ELSE 0 END) as likes,
                SUM(CASE WHEN vote = -1 THEN 1 ELSE 0 END) as dislikes
             FROM {$this->tablename} 
             WHERE post_id = :post_id"
        );
        $stmt->execute(['post_id' => $post_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'likes' => intval($row['likes']),
            'dislikes' => intval($row['dislikes'])
        ];
    }

    public function get_user_votes($user_id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT post_id, vote FROM {$this->tablename} WHERE user_id = :user_id"
        );
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$GLOBALS['posts_votes'] = new posts_votes();

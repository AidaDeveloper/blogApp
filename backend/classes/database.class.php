<?php

class database
{
    protected $pdo;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct($table, $primaryKey = 'id')
    {
        $this->table = $table;
        $this->primaryKey = $primaryKey;

        $dsn = 'mysql:host=localhost;dbname=blog_app;charset=utf8mb4';
        $username = 'blog_user';
        $password = 'StrongPassword123';

        $this->pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    public function insert(array $data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);
        $sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ")
                VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        return $this->pdo->lastInsertId();
    }

    public function update($id, array $data)
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }

        $sql = "UPDATE {$this->table} SET " . implode(',', $fields) . " WHERE {$this->primaryKey} = :id";
        $stmt = $this->pdo->prepare($sql);
        $data['id'] = $id;

        return $stmt->execute($data);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}

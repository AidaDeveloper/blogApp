<?php
use PHPUnit\Framework\TestCase;

class CommentsTest extends TestCase
{
    protected $comments;
    protected $posts;
    protected $users;
    protected $pdo;

    protected function setUp(): void
    {
        $this->comments = $GLOBALS['comments'];
        $this->posts = $GLOBALS['posts'];
        $this->users = $GLOBALS['users'];

        $ref = new ReflectionClass($this->users);
        $prop = $ref->getProperty('pdo');
        $prop->setAccessible(true);
        $this->pdo = $prop->getValue($this->users);

        $this->pdo->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    protected function createTestUser()
    {
        $login = 'commentuser_' . rand(1000, 9999);
        $password = 'secret123';
        return $this->users->register($login, $password);
    }

    protected function createTestPost($userId)
    {
        return $this->posts->create_post($userId, 'Title', 'Content');
    }

    public function testCreateComment(): void
    {
        $userId = $this->createTestUser();
        $postId = $this->createTestPost($userId);

        $commentId = $this->comments->create_comment($postId, $userId, 'Hello world!');
        $this->assertNotEmpty($commentId);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$commentId);
    }

    public function testGetByPost(): void
    {
        $userId = $this->createTestUser();
        $postId = $this->createTestPost($userId);
        $this->comments->create_comment($postId, $userId, 'First comment');
        $this->comments->create_comment($postId, $userId, 'Second comment');

        $comments = $this->comments->get_by_post($postId);
        $this->assertCount(2, $comments);
    }

    public function testGetByPostWithAuthor(): void
    {
        $userId = $this->createTestUser();
        $postId = $this->createTestPost($userId);
        $this->comments->create_comment($postId, $userId, 'Hello');

        $comments = $this->comments->get_by_post_with_author($postId);
        $this->assertIsArray($comments);
        $this->assertArrayHasKey('author_login', $comments[0]);
    }

    public function testFindWithAuthor(): void
    {
        $userId = $this->createTestUser();
        $postId = $this->createTestPost($userId);
        $commentId = $this->comments->create_comment($postId, $userId, 'Text');

        $comment = $this->comments->find_with_author($commentId);
        $this->assertEquals($commentId, $comment['id']);
        $this->assertEquals($userId, $comment['user_id']);
    }

    public function testGetByUser(): void
    {
        $userId = $this->createTestUser();
        $postId = $this->createTestPost($userId);
        $this->comments->create_comment($postId, $userId, 'Comment 1');
        $this->comments->create_comment($postId, $userId, 'Comment 2');

        $comments = $this->comments->get_by_user($userId);
        $this->assertCount(2, $comments);
    }
}

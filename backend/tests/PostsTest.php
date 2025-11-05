<?php
use PHPUnit\Framework\TestCase;

class PostsTest extends TestCase
{
    protected $posts;
    protected $users;
    protected $pdo;

    protected function setUp(): void
    {
        $this->posts = $GLOBALS['posts'];
        $this->users = $GLOBALS['users'];

        $ref = new ReflectionClass($this->posts);
        $prop = $ref->getProperty('pdo');
        $prop->setAccessible(true);
        $this->pdo = $prop->getValue($this->posts);

        $this->pdo->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->pdo && $this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    protected function createTestUser()
    {
        $login = 'postuser_' . rand(1000, 9999);
        $password = 'secret123';
        return $this->users->register($login, $password);
    }

    public function testCreatePost(): void
    {
        $userId = $this->createTestUser();
        $title = 'Test Post';
        $content = 'This is a test post content';

        $postId = $this->posts->create_post($userId, $title, $content);

        $this->assertNotEmpty($postId);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$postId);
    }

    public function testIncrementViews(): void
    {
        $userId = $this->createTestUser();
        $postId = $this->posts->create_post($userId, 'Title', 'Content');

        $this->posts->increment_views($postId);
        $post = $this->posts->find($postId);
        $this->assertEquals(1, (int)$post['views']);
    }

    public function testAddLikeAndDislike(): void
    {
        $userId = $this->createTestUser();
        $postId = $this->posts->create_post($userId, 'Title', 'Content');

        $this->posts->add_like($postId);
        $this->posts->add_dislike($postId);
        $post = $this->posts->find($postId);

        $this->assertEquals(1, (int)$post['likes']);
        $this->assertEquals(1, (int)$post['dislikes']);
    }

    public function testGetAllWithAuthors(): void
    {
        $result = $this->posts->get_all_with_authors();
        $this->assertIsArray($result);
    }

    public function testFindWithAuthor(): void
    {
        $userId = $this->createTestUser();
        $postId = $this->posts->create_post($userId, 'Title', 'Content');

        $result = $this->posts->find_with_author($postId);
        $this->assertEquals($userId, $result['user_id']);
    }

    public function testGetByUser(): void
    {
        $userId = $this->createTestUser();
        $this->posts->create_post($userId, 'Title1', 'Content1');
        $this->posts->create_post($userId, 'Title2', 'Content2');

        $posts = $this->posts->get_by_user($userId);
        $this->assertCount(2, $posts);
    }
}

<?php
use PHPUnit\Framework\TestCase;

class UsersTest extends TestCase
{
    protected $users;
    protected $pdo;

    protected function setUp(): void
    {
        $this->users = $GLOBALS['users'];

        $ref = new ReflectionClass($this->users);
        $prop = $ref->getProperty('pdo');
        $prop->setAccessible(true);
        $this->pdo = $prop->getValue($this->users);

        $this->pdo->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->pdo && $this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    public function testRegisterAndExists()
    {
        $login = 'testuser_' . rand(1000, 9999);
        $password = 'password123';

        $userId = $this->users->register($login, $password);
        $this->assertIsNumeric($userId);

        $this->assertTrue($this->users->exists($login));
    }

    public function testLogin()
    {
        $login = 'testuser_' . rand(1000, 9999);
        $password = 'password123';

        $this->users->register($login, $password);

        $user = $this->users->login($login, $password);
        $this->assertIsArray($user);
        $this->assertEquals($login, $user['login']);

        $wrong = $this->users->login($login, 'wrongpass');
        $this->assertFalse($wrong);
    }

    public function testGetAll()
    {
        $allUsers = $this->users->getAll();
        $this->assertIsArray($allUsers);
    }
}

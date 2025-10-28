<?php

require_once __DIR__ . '/../src/Repositories/PostRepository.php';
require_once __DIR__ . '/../src/Models/Post.php';

use PHPUnit\Framework\TestCase;
use src\Repositories\PostRepository;

class PostRepositoryTest extends TestCase
{
    private PostRepository $postRepository;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    /**
     * Runs before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->postRepository = new PostRepository();
    }

    /**
     * Runs after each test
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $hostname = $_ENV['DB_HOST'];
        $username = $_ENV['DB_USER'];
        $databasePassword = $_ENV['DB_PASSWORD'];
		$databaseName = 'posts_web_app_test'; // always use test database for tests?

        $commands = file_get_contents('database/test_schema.sql');
        $dsn = "mysql:host=$hostname;dbname=$databaseName;";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, $username, $databasePassword, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
        $pdo->exec($commands);
    }

    public function testPostCreation()
    {
        $post = $this->postRepository->savePost('test', 'body');
        $this->assertEquals('test', $post->title);
        $this->assertEquals('body', $post->body);
    }

    public function testPostRetrieval()
    {
        // Create a post, then retrieve it
        $post = $this->postRepository->savePost('test', 'body');

        $post = $this->postRepository->getPostById(1);
        $this->assertEquals('test', $post->title);
        $this->assertEquals('body', $post->body);


        $post = $this->postRepository->savePost('another test', 'another body');
        $post = $this->postRepository->getPostById(2);
        $this->assertEquals('another test', $post->title);
        $this->assertEquals('another body', $post->body);

        $post = $this->postRepository->savePost('', 'empty title, body only');
        $post = $this->postRepository->getPostById(3);
        $this->assertEquals('', $post->title);
        $this->assertEquals('empty title, body only', $post->body);

        $post = $this->postRepository->savePost('empty body, title only', '');
        $post = $this->postRepository->getPostById(4);
        $this->assertEquals('empty body, title only', $post->title);
        $this->assertEquals('', $post->body);

        $post = $this->postRepository->savePost('', '');
        $post = $this->postRepository->getPostById(5);
        $this->assertEquals('', $post->title);
        $this->assertEquals('', $post->body);
    }

    public function testPostUpdate()
    {
        // Create a post, then update it
        $this->postRepository->savePost('test', 'body');
        $this->postRepository->savePost('test2', 'test2 body');
        $this->postRepository->savePost('', '');

        sleep(1); // Sleep for 1 second to ensure updated_at timestamp is different
        // Update the post with ID 1
        $post = $this->postRepository->updatePost(1, 'altered title', 'altered body');
        $post = $this->postRepository->getPostById(1);

        // Test if updated values are correct
        $this->assertEquals('altered title', $post->title);
        $this->assertEquals('altered body', $post->body);

        // Test if updated_at timestamp is different from created_at timestamp
        $this->assertNotEquals($post->created_at, $post->updated_at);


        $post = $this->postRepository->updatePost(2, 'test2 updated', 'test2 body updated');
        $post = $this->postRepository->getPostById(2);

        // Test if updated values are correct
        $this->assertEquals('test2 updated', $post->title);
        $this->assertEquals('test2 body updated', $post->body);

        // Test if updated_at timestamp is different from created_at timestamp
        $this->assertNotEquals($post->created_at, $post->updated_at);

        $post = $this->postRepository->updatePost(3, 'none empty', 'none empty');
        $post = $this->postRepository->getPostById(3);

        // Test if updated values are correct
        $this->assertEquals('none empty', $post->title);
        $this->assertEquals('none empty', $post->body);
    }

    public function testPostDeletion()
    {
        //  Create a post
        $post = $this->postRepository->savePost('test', 'body');
        $this->assertEquals(1, $post->id);

        // Delete the post, then try to retrieve it
        $this->postRepository->deletePostById(1);
        $post = $this->postRepository->getPostById(1);
        $this->assertFalse($post);

        $post = $this->postRepository->savePost('test2', 'body2');
        $this->assertEquals(2, $post->id);
        $post = $this->postRepository->savePost('test3', 'body3');
        $this->assertEquals(3, $post->id);

        $this->postRepository->deletePostById(2);
        $this->postRepository->deletePostById(3);
        $post = $this->postRepository->getPostById(2);
        $this->assertFalse($post);
        $post = $this->postRepository->getPostById(3);
        $this->assertFalse($post);
    }
}

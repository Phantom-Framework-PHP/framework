<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Core\Container;
use Phantom\Models\Model;
use Phantom\Database\Database;

// Clases de prueba para simular modelos reales
class Post extends Model {
    public function user() { return $this->belongsTo(User::class); }
}

class User extends Model {
    public function posts() { return $this->hasMany(Post::class); }
}

class ModelRelationshipTest extends TestCase
{
    protected $db;

    protected function setUp(): void
    {
        Container::setInstance(null);
        $app = new Application(dirname(__DIR__, 2));
        
        // Setup SQLite en memoria para tests
        $this->db = new Database([
            'default' => 'sqlite',
            'connections' => [
                'sqlite' => [
                    'driver' => 'sqlite',
                    'database' => ':memory:'
                ]
            ]
        ]);
        
        Container::getInstance()->instance('db', $this->db);

        // Crear tablas necesarias (Sintaxis SQLite)
        $this->db->query("CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)");
        $this->db->query("CREATE TABLE posts (id INTEGER PRIMARY KEY, title TEXT, user_id INTEGER)");
    }

    public function test_has_many_relationship()
    {
        $user = new User(['id' => 1, 'name' => 'John']);
        
        // Simular posts en la DB
        $this->db->query("INSERT INTO posts (id, title, user_id) VALUES (1, 'Post 1', 1)");
        $this->db->query("INSERT INTO posts (id, title, user_id) VALUES (2, 'Post 2', 1)");

        $posts = $user->posts;

        $this->assertCount(2, $posts);
        $this->assertInstanceOf(Post::class, $posts->first());
        $this->assertEquals('Post 1', $posts->first()->title);
    }

    public function test_belongs_to_relationship()
    {
        $post = new Post(['id' => 1, 'title' => 'My Post', 'user_id' => 1]);
        
        // Simular usuario en la DB
        $this->db->query("INSERT INTO users (id, name) VALUES (1, 'John')");

        $user = $post->user;

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John', $user->name);
    }
}

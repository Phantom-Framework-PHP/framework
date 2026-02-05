<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Core\Container;
use Phantom\Models\Model;
use Phantom\Database\Database;

// Modelos para el test
class MorphComment extends Model {
    protected $table = 'comments';
    public function commentable() { return $this->morphTo(); }
}

class MorphPost extends Model {
    protected $table = 'posts';
    public function comments() { return $this->morphMany(MorphComment::class, 'commentable'); }
}

class MorphVideo extends Model {
    protected $table = 'videos';
    public function comments() { return $this->morphMany(MorphComment::class, 'commentable'); }
}

class PolymorphicRelationshipTest extends TestCase
{
    protected $db;

    protected function setUp(): void
    {
        Container::setInstance(null);
        new Application(dirname(__DIR__, 2));
        
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

        // Crear tablas
        $this->db->query("CREATE TABLE posts (id INTEGER PRIMARY KEY, title TEXT)");
        $this->db->query("CREATE TABLE videos (id INTEGER PRIMARY KEY, title TEXT)");
        $this->db->query("CREATE TABLE comments (id INTEGER PRIMARY KEY, body TEXT, commentable_id INTEGER, commentable_type TEXT)");
    }

    public function test_morph_many_relationship()
    {
        $post = new MorphPost(['id' => 10, 'title' => 'Polymorphic Post']);
        
        // Simular comentario para el Post
        $this->db->query("INSERT INTO comments (id, body, commentable_id, commentable_type) 
                          VALUES (1, 'Great post!', 10, 'Tests\Unit\MorphPost')");

        $comments = $post->comments;

        $this->assertCount(1, $comments);
        $this->assertEquals('Great post!', $comments->first()->body);
    }

    public function test_morph_to_relationship()
    {
        // Comentario apuntando a un Video
        $comment = new MorphComment([
            'id' => 1, 
            'body' => 'Cool video!', 
            'commentable_id' => 5, 
            'commentable_type' => MorphVideo::class
        ]);
        
        $this->db->query("INSERT INTO videos (id, title) VALUES (5, 'My Video')");

        $parent = $comment->commentable;

        $this->assertInstanceOf(MorphVideo::class, $parent);
        $this->assertEquals('My Video', $parent->title);
    }
}

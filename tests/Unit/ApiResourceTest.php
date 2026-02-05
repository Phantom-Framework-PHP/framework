<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Http\Resources\JsonResource;
use Phantom\Core\Collection;

class UserResource extends JsonResource
{
    public function toArray()
    {
        return [
            'identifier' => $this->id,
            'full_name' => strtoupper($this->name),
            'email_address' => $this->email
        ];
    }
}

class ApiResourceTest extends TestCase
{
    public function test_resource_transforms_individual_model()
    {
        $user = (object) ['id' => 1, 'name' => 'mario', 'email' => 'mario@example.com', 'password' => 'secret'];
        
        $resource = new UserResource($user);
        $result = $resource->toArray();

        $this->assertEquals(1, $result['identifier']);
        $this->assertEquals('MARIO', $result['full_name']);
        $this->assertArrayNotHasKey('password', $result);
    }

    public function test_resource_collection_transformation()
    {
        $users = [
            (object) ['id' => 1, 'name' => 'mario', 'email' => 'mario@example.com'],
            (object) ['id' => 2, 'name' => 'luigi', 'email' => 'luigi@example.com']
        ];

        $collection = UserResource::collection(new Collection($users));
        $result = $collection->toArray();

        $this->assertCount(2, $result);
        $this->assertEquals('MARIO', $result[0]['full_name']);
        $this->assertEquals('LUIGI', $result[1]['full_name']);
    }
}

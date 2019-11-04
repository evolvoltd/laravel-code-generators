<?php

namespace Evolvo\LaravelCodeGenerators\TestLogic;

use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class HelperTest extends TestCase
{
    private $auth;
    private $status;
    private $uri;
    private $data;
    private $structure;
    private $role;
    private $user_id;
    private $fragment;
    private $json;
    private $testJson;
    private $testFragment;
    private $testStructure;
    private $method;


    public function setAuth($auth = true)
    {
        $this->auth = $auth;
        return $this;
    }

    public function setStatus($status = 200)
    {
        $this->status = $status;
        return $this;
    }

    public function setUri(string $uri, string $method = 'get', int $status = 200, $data = [])
    {
        $this->data = $data;
        $this->status = $status;
        $this->method = $method;
        $this->uri = $uri;
        return $this;
    }

    public function setData(array $data = [])
    {
        $this->data = $data;
        return $this;
    }

    public function setRole($role = 'admin')
    {
        $this->role = $role;
        return $this;
    }

    public function setUserId($user_id = 2)
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function setAssertStructure(array $structure, $testStructure = true)
    {
        $this->testStructure = $testStructure;
        $this->structure = $structure;
        return $this;
    }

    public function setAssertFragment(array $fragment, bool $testFragment = true)
    {
        $this->testFragment = $testFragment;
        $this->fragment = $fragment;
        return $this;
    }

    public function setAssertJson(array $json, bool $testJson = true)
    {
        $this->testJson = $testJson;
        $this->json = $json;
        return $this;
    }

    function getTest()
    {

        $auth = $this->auth;
        $status = $this->status;
        $uri = $this->uri;
        $data = $this->data;
        $structure = $this->structure;
        $role = $this->role;
        $user_id = $this->user_id;
        $fragment = $this->fragment ?? [];
        $json = $this->json ?? [];
        $user = factory(User::class)->make([
            'name' => 'John',
            'email' => 'john@john.com',
            'role' => $role,
            'id' => $user_id]);
        if ($auth) Passport::actingAs($user);

        $this->withHeaders([

            'Content-Type' => 'application/json',
            'Accept' => 'application/json',]);

        $chain = null;
        switch ($this->method) {

            case 'get':
                $chain = $this->json('get', $uri, $data)->assertStatus($status);
                echo $chain->baseResponse;
                break;
            case 'post':
                $chain = $this->json('post', $uri, $data)->assertStatus($status);
                echo $chain->baseResponse;
                break;
            case 'put':
                $chain = $this->json('put', $uri, $data)->assertStatus($status);
                echo $chain->baseResponse;
                break;
            case 'delete':
                $chain = $this->delete($uri, $data)->assertStatus($status);
                break;
            case 'deleteJson':
                $chain = $this->deleteJson($uri, $data)->assertStatus($status);
                //echo $chain->baseResponse;
                break;
            case 'test':
                $chain = $this;
                //echo $chain->baseResponse;
                break;

        }

        $testStructure = $this->testStructure;
        $testJson = $this->testJson;
        $testFragment = $this->testFragment;


        if (@$testStructure == true && @$structure) $chain->assertJsonStructure($structure);
        if (@$testFragment == true && @$fragment) $chain->assertJsonFragment($fragment);
        if (@$testJson == true && @$json) $chain->assertJson($json);
        
        return $chain;
    }
}

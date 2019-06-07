<?php

namespace Evolvo\LaravelCodeGenerators\TestLogic;

use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class MasterTest extends TestCase
{
    
    function master($testData){
        
        $auth = $testData['auth']??true;
        $status = $testData['status']??200;
        $uri = $testData['uri']??'';
        $data = $testData['data']??[];
        $structure = $testData['structure']??[];
        $role = $testData['role']??'admin';
        $user_id = $testData['user_id']??2;
        $fragment = $testData['fragment']??[];
        $json = $testData['json']??[];


        $user = factory(User::class)->make([
            'name' => 'John',
            'email' => 'john@john.com',
            'role' => $role,
            'id'=>$user_id]);
       if($auth) Passport::actingAs($user);

        $this->withHeaders([
            
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',]);

        $chain = null;
        switch ($testData['method']) {

            case 'get':
                $chain =  $this->json('get',$uri,$data)->assertStatus($status);
                echo $chain->baseResponse;
                break;
            case 'post':
                $chain =  $this->json('post', $uri,$data)->assertStatus($status);
                echo $chain->baseResponse;
                break;
            case 'put':
                $chain = $this->json('put', $uri, $data)->assertStatus($status);
                echo $chain->baseResponse;
                break;
            case 'delete':
                $chain =  $this->delete($uri,$data)->assertStatus($status);
                break;
            case 'deleteJson':
                $chain =  $this->deleteJson($uri,$data)->assertStatus($status);
                //echo $chain->baseResponse;
                break;



        }
            if (@$testData['test_structure'] == true) $chain->assertJsonStructure($structure);
            if (@$testData['test_fragment'] == true) $chain->assertJsonFragment($fragment);
            if (@$testData['test_json'] == true) $chain->assertJson($json);
    }


    public function testBasicTest()
    {
        $this->assertTrue(true);
    }
}

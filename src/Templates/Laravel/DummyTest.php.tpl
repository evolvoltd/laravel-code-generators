<?php

namespace Tests\Feature;

use Evolvo\LaravelCodeGenerators\TestLogic\MasterTest;
use Illuminate\Support\Facades\DB;

class testClass extends MasterTest
{
    public function testStore()
    {
    DB::table('/*table*/')->truncate();
        $this->master(
            [
                'method' => 'post',
                'uri' => 'api/store',
                'data' => [

                    post_data



                ],
                'status' => 201,
                'user_id' => 2,
                'test_structure' => false,
                'structure' => [],

                'test_json' => true,
                'json' => [/*'post_json'*/]


            ]
        );
    }

    public function testUpdate()
    {
        $this->master(
            [
                'method' => 'put',
                'uri' => 'api/update',
                'data' => [

                    update_data


                ],
                'status' => 200,
                'user_id' => 2,
                'test_structure' => false,
                'structure' => [],

                'test_json' => true,
                'json' => [/*'put_json'*/]
    ]
        );
    }

    public function testIndex()
    {
        $this->master(
            [
                'method' => 'get',
                'uri' => 'api/index',
                'data' => [

                ],
                'status' => 200,
                'user_id' => 2,
                'test_structure' => false,
                'structure' => [],

                'test_json' => true,
                'json' => [/*'index_json'*/]

            ]
        );
    }

    public function testShow()
    {
        $this->master(
            [
                'method' => 'get',
                'uri' => 'api/show',
                'data' => [],

                'status' => 200,
                'user_id' => 2,
                'test_structure' => false,
                'structure' => [],

                'test_json' => true,
                'json' => [/*'show_json'*/]

]
        );
    }

    public function testDelete()
    {

        $this->master(
            [
                'method' => 'delete',
                'uri' => 'api/delete',

                'data' => [],

                'status' => 200,
                'role' => 'admin',
            ]
        );
    }


    public function testExample()
    {
        $this->assertTrue(true);
    }
}

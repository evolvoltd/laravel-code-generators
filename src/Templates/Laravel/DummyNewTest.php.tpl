<?php

namespace Tests\Feature;

use Evolvo\LaravelCodeGenerators\TestLogic\HelperTest;
use Illuminate\Support\Facades\DB;

class testClass extends HelperTest
{
    public $requestPostData = [
        post_data
        ];

    public $requestUpdateData = [
        update_data
        ];

    public function testStore()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('/*table*/')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->setUri('api/store', 'post', 201, $this->requestPostData)->setAssertJson($this->requestPostData)->getTest();
    }

    public function testUpdate()
    {
        $this->setUri('api/update', 'put', 200, $this->requestUpdateData)->setAssertJson($this->requestUpdateData)->getTest();
    }

    public function testIndex()
    {
        $this->setUri('api/index')->setAssertJson(['data' => [$this->requestUpdateData]])->getTest();
    }

    public function testShow()
    {
        $this->setUri('api/show')->setAssertJson($this->requestUpdateData)->getTest();
    }

    public function testStoreValidate()
    {
        $this->setUri('api/store','post', 422)->getTest();
    }

    public function testUpdateValidate()
    {
        $this->setUri('api/update','put', 422)->getTest();
    }

    public function testDelete()
    {
        $this->setUri('api/delete','delete', 200)->getTest();

    }
}

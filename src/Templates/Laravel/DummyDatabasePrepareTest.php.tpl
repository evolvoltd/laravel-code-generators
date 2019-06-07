<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatabasePrepareTest extends TestCase
{

    public function testDataBase()
    {
        $this->artisan('migrate:refresh');

        $this->assertTrue(true);
    }
}

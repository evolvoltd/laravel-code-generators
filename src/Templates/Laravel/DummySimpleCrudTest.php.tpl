<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;
class testClass extends TestCase
{
    use RefreshDatabase, WithFaker;
    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->states('admin')->create();
        Passport::actingAs($user);
    }
    public function testTypesCRUD()
    {
        // test validation
        $response = $this->json('post', url('api/route'), []);
        $response->assertStatus(422);
        // create
        $data = [
            post_data
        ];
        $response = $this->postJson(url('api/route'), $data);
        $response->assertStatus(200);
        $response->assertJson($data);
        $itemId = $response->decodeResponseJson()["id"];
        //get single
        $response = $this->getJson(url('api/route/' . $itemId));
        $response->assertStatus(200);
        $response->assertJson($data);
        //get all
        $response = $this->getJson(url('api/route'));
        $response->assertStatus(200);
        $response->assertJson(['data' => [$data]]);
        // update
        $data = [
            update_data
        ];
        $response = $this->json('put', url('api/route/' . $itemId), $data);
        $response->assertStatus(200);
        $response->assertJson($data);
        //search
        $response = $this->json('get', url('api/route/find/' . $data['name']));
        $response->assertStatus(200);
        $response->assertJson(['data' => [[
            'name' => $data['name'],
        ]]]);
        $response->assertJsonCount(1, 'data');
        //
        $response = $this->deleteJson(url('api/route/' . $itemId), $data);
        $response->assertStatus(200);
    }
}

<?php
namespace Tests\Feature\Dummies;
use App\Models\User;
use App\Models\Dummy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Tests\TestCase;
class testClass extends TestCase
{
    use RefreshDatabase, WithFaker;
    public function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create(['role'=> User::USER_ROLE_ADMIN]);
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
        /*$response = $this->getJson(url('api/route/' . $itemId));
        $response->assertStatus(200);
        $response->assertJson($data);*/
        //get all
        $response = $this->getJson(url('api/route'));
        $response->assertStatus(200);
        $response->assertJson(['data' => [$data]]);
        // update
        $data = [
            update_data
        ];
        $response = $this->putJson(url('api/route/' . $itemId), $data);
        $response->assertStatus(200);
        $response->assertJson($data);
        //search
        $response = $this->getJson(url('api/route/find/' . $data['name']));
        $response->assertStatus(200);
        $response->assertJson(['data' => [[
            'name' => $data['name'],
        ]]]);
        $response->assertJsonCount(1, 'data');
        //
        $response = $this->deleteJson(url('api/route/' . $itemId), $data);
        $response->assertStatus(200);
    }

    public function testListFiltering()
    {
        $item = Dummy::factory()->create();
        //prepare list filtering tests
        $this->assertTrue(true);

        /*$this->getJson(url('api/route?search=' . $item->name))
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $this->getJson(url('api/route?search=' . Str::random()))
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');

        $this->getJson(url('api/route?status[]=' . $item->status))
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
        $this->getJson(url('api/route?status[]=' . array_values(array_diff(Dummy::AVAILABLE_STATUSES, [$item->status]))[0]))
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');

        $this->getJson(url('api/route?date_from=' . (new Carbon($item->date))->subDay()->format('Y-m-d')))
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
        $this->getJson(url('api/route?date_from=' . (new Carbon($item->date))->addDay()->format('Y-m-d')))
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');

        $this->getJson(url('api/route?date_to=' . (new Carbon($item->date))->addDay()->format('Y-m-d')))
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
        $this->getJson(url('api/route?date_to=' . (new Carbon($item->date))->subDay()->format('Y-m-d')))
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');*/


    }
}

<?php
namespace Tests\Feature\Dummies;

use App\Models\User;
use App\Models\Dummy;
//modelClassUsages
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
        $user = User::factory()->create(['role'=> User::USER_ROLE_ADMIN]);
        Passport::actingAs($user);
    }

    public function testApiResource()
    {
        $apiResourceUrl = url('api/route');

        // create
        $data = Dummy::factory()->make()->toArray();
        $this->postJson($apiResourceUrl, [])->assertStatus(422);
        $itemId = $this->postJson($apiResourceUrl, $data)->assertStatus(200)->assertJson($data)->json("id");

        //get single
        $this->getJson($apiResourceUrl . '/' . $itemId)->assertStatus(200)->assertJson($data);

        //get all
        $this->getJson($apiResourceUrl)->assertStatus(200)->assertJson(['data' => [$data]]);

        // update
        $data = Dummy::factory()->make()->toArray();
        $this->putJson($apiResourceUrl. '/' . $itemId, [])->assertStatus(422);
        $this->putJson($apiResourceUrl. '/' . $itemId, $data)->assertStatus(200)->assertJson($data);

        //search
        foreach (Dummy::SEARCHABLE_ATTRIBUTES as $searchableAttribute)
        $this->getJson($apiResourceUrl.'/find/' . $data[$searchableAttribute])
        ->assertStatus(200)
        ->assertJson(['data' => [[$searchableAttribute => $data[$searchableAttribute]]]])
        ->assertJsonCount(1, 'data');

        //delete
        $this->deleteJson($apiResourceUrl.'/' . $itemId)->assertStatus(200);
    }

    public function testListFiltering()
    {
        /*$data = [
            post_data
        ];*/
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

<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->authenticate();
    }

    public function test_index_returns_all_accounts()
    {
        Account::factory()->count(3)->create();

        $response = $this->getJson('/api/account');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_show_returns_account()
    {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/account/{$account->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $account->id,
                'name' => $account->name,
            ]);
    }

    public function test_store_creates_account()
    {
        $accountData = Account::factory()->make()->toArray();

        $response = $this->postJson('/api/account', $accountData);

        $response->assertStatus(201)
            ->assertJson(['name' => $accountData['name']]);
        $this->assertDatabaseHas('accounts', ['name' => $accountData['name']]);
    }

    public function test_update_modifies_account()
    {
        $account = Account::factory()->create();
        $newName = 'Updated Account Name';

        $response = $this->putJson("/api/account/{$account->id}", ['name' => $newName]);

        $response->assertStatus(200)
            ->assertJson(['name' => $newName]);
        $this->assertEquals($newName, $account->fresh()->name);
    }

    public function test_destroy_deletes_account()
    {
        $account = Account::factory()->create();

        $response = $this->deleteJson("/api/account/{$account->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('accounts', ['id' => $account->id]);
    }
}

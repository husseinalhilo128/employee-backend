<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_access_admin_routes()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Sanctum::actingAs($admin, ['*']);

        $response = $this->getJson('/api/branches');
        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'employee']);

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/branches');
        $response->assertStatus(403);
    }

    /** @test */
    public function employee_can_access_own_profile()
    {
        $employee = User::factory()->create(['role' => 'employee']);

        Sanctum::actingAs($employee, ['*']);

        $response = $this->getJson('/api/profile');
        $response->assertStatus(200);
    }

    /** @test */
    public function guest_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/profile');
        $response->assertStatus(401);
    }
}

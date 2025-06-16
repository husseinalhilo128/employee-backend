<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class RegisterApprovalTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_approve_pending_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pendingUser = User::factory()->create(['approved' => false]);

        Sanctum::actingAs($admin, ['*']);

        $response = $this->postJson("/api/registration-requests/{$pendingUser->id}/approve", [
            'shift_start_morning' => '08:00',
            'shift_end_morning' => '12:30',
            'shift_start_evening' => '16:00',
            'shift_end_evening' => '23:00',
            'hours_per_morning_shift' => 7.5,
            'hours_per_evening_shift' => 7.5,
            'hours_for_both_shifts' => 12,
            'allowed_delay_minutes' => 15,
            'allowed_absence_days' => 3,
            'auto_checkout_enabled' => true,
            'salary' => 5000,
            'role' => 'employee',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'تمت الموافقة على المستخدم وتحديث بياناته']);

        $this->assertDatabaseHas('users', [
            'id' => $pendingUser->id,
            'approved' => true,
            'role' => 'employee',
            'base_salary' => 5000,
        ]);
    }

    /** @test */
    public function admin_can_reject_pending_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pendingUser = User::factory()->create(['approved' => false]);

        Sanctum::actingAs($admin, ['*']);

        $response = $this->postJson("/api/registration-requests/{$pendingUser->id}/reject");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'تم رفض المستخدم وحذفه']);

        $this->assertDatabaseMissing('users', [
            'id' => $pendingUser->id,
        ]);
    }
}

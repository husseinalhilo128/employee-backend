<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Leave;
use App\Models\Notification;
use Laravel\Sanctum\Sanctum;

class LeaveFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function employee_can_submit_leave_request()
    {
        $employee = User::factory()->create(['role' => 'employee', 'approved' => true]);
        Sanctum::actingAs($employee, ['*']);

        $response = $this->postJson('/api/leaves', [
            'type' => 'daily',
            'date' => now()->toDateString(),
            'reason' => 'ظرف عائلي',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'تم إرسال طلب الإجازة']);

        $this->assertDatabaseHas('leaves', [
            'user_id' => $employee->id,
            'status' => 'pending',
            'type' => 'daily',
        ]);
    }

    /** @test */
    public function admin_can_approve_leave()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create(['role' => 'employee']);
        $leave = Leave::factory()->create(['user_id' => $employee->id, 'status' => 'pending']);

        Sanctum::actingAs($admin, ['*']);

        $response = $this->postJson("/api/leaves/{$leave->id}/approve", [
            'status' => 'approved',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'تم تحديث حالة الإجازة']);

        $this->assertDatabaseHas('leaves', [
            'id' => $leave->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $employee->id,
            'title' => 'طلب الإجازة',
            'body' => 'تمت الموافقة على طلب الإجازة الخاص بك',
        ]);
    }

    /** @test */
    public function admin_can_reject_leave()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create(['role' => 'employee']);
        $leave = Leave::factory()->create(['user_id' => $employee->id, 'status' => 'pending']);

        Sanctum::actingAs($admin, ['*']);

        $response = $this->postJson("/api/leaves/{$leave->id}/reject", [
            'status' => 'rejected',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'تم تحديث حالة الإجازة']);

        $this->assertDatabaseHas('leaves', [
            'id' => $leave->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $employee->id,
            'title' => 'طلب الإجازة',
            'body' => 'تم رفض طلب الإجازة الخاص بك',
        ]);
    }
}

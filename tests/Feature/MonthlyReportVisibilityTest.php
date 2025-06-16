<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\MonthlyReport;
use Carbon\Carbon;

class MonthlyReportVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_monthly_report()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $report = MonthlyReport::factory()->create([
            'user_id' => $user->id,
            'month' => '06',
            'year' => '2025',
            'total_present_days' => 20,
            'total_absent_days' => 5,
            'total_leave_days' => 5,
            'final_salary' => 5000,
        ]);

        $this->actingAs($admin)
             ->getJson("/api/reports/{$user->id}?month=06&year=2025")
             ->assertOk()
             ->assertJsonFragment([
                 'id' => $user->id,
                 'final_salary' => 5000,
                 'total_present_days' => 20,
             ]);
    }

    public function test_user_can_view_own_report()
    {
        $user = User::factory()->create();

        $report = MonthlyReport::factory()->create([
            'user_id' => $user->id,
            'month' => now()->format('m'),
            'year' => now()->format('Y'),
            'final_salary' => 4500,
        ]);

        $this->actingAs($user)
             ->getJson("/api/reports/{$user->id}?month=" . now()->format('m') . "&year=" . now()->format('Y'))
             ->assertOk()
             ->assertJsonFragment([
                 'final_salary' => 4500,
             ]);
    }

    public function test_user_cannot_view_other_user_report()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user1)
             ->getJson("/api/reports/{$user2->id}?month=06&year=2025")
             ->assertStatus(403);
    }
}

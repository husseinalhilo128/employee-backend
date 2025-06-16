<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Bonus;
use App\Models\Deduction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ProfileDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_shows_correct_financial_summary()
    {
        $user = User::factory()->create([
            'base_salary' => 3000,
            'double_shift_hours' => 12,
            'approved' => 1,
        ]);

        $this->actingAs($user);

        // Create attendance with 10 hours worked, considered 1 shift
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->subDays(1)->toDateString(),
            'worked_hours' => 12,
            'extra_hours' => 2,
            'missing_hours' => 1,
        ]);

        Bonus::factory()->create([
            'user_id' => $user->id,
            'amount' => 150,
        ]);

        Deduction::factory()->create([
            'user_id' => $user->id,
            'amount' => 50,
        ]);

        $response = $this->getJson('/api/profile');
        $response->assertOk();

        $response->assertJsonFragment([
            'total_bonus' => 150,
            'total_deduction' => 50,
            'extra_hours' => 2,
            'missing_hours' => 1,
        ]);

        $this->assertArrayHasKey('final_salary', $response->json());
    }
}

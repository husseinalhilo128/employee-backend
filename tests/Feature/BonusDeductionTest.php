<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Bonus;
use App\Models\Deduction;
use Laravel\Sanctum\Sanctum;

class BonusDeductionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_add_bonus_for_employee()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();

        Sanctum::actingAs($admin, ['*']);

        $response = $this->postJson('/api/bonus', [
            'user_id' => $employee->id,
            'amount' => 300,
            'reason' => 'أداء ممتاز',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'تمت إضافة المكافأة بنجاح']);

        $this->assertDatabaseHas('bonuses', [
            'user_id' => $employee->id,
            'amount' => 300,
            'reason' => 'أداء ممتاز',
        ]);
    }

    /** @test */
    public function admin_can_add_deduction_for_employee()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();

        Sanctum::actingAs($admin, ['*']);

        $response = $this->postJson('/api/deduction', [
            'user_id' => $employee->id,
            'amount' => 100,
            'reason' => 'تأخير متكرر',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'تمت إضافة الخصم بنجاح']);

        $this->assertDatabaseHas('deductions', [
            'user_id' => $employee->id,
            'amount' => 100,
            'reason' => 'تأخير متكرر',
        ]);
    }

    /** @test */
    public function employee_can_view_own_bonuses_and_deductions()
    {
        $employee = User::factory()->create();
        Bonus::factory()->create(['user_id' => $employee->id, 'amount' => 150]);
        Deduction::factory()->create(['user_id' => $employee->id, 'amount' => 50]);

        Sanctum::actingAs($employee, ['*']);

        $bonusResponse = $this->getJson("/api/bonus/{$employee->id}");
        $deductionResponse = $this->getJson("/api/deduction/{$employee->id}");

        $bonusResponse->assertStatus(200)->assertJsonFragment(['amount' => 150]);
        $deductionResponse->assertStatus(200)->assertJsonFragment(['amount' => 50]);
    }
}

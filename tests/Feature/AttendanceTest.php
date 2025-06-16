<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_check_in_within_valid_location()
    {
        $user = User::factory()->create();

        $branch = Branch::create([
            'name' => 'الفرع الرئيسي',
            'latitude' => 32.615430,
            'longitude' => 44.017204,
            'radius' => 100,
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/attendance/check-in', [
            'latitude' => 32.615450,
            'longitude' => 44.017210,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'branch_name' => $branch->name,
        ]);
    }

    /** @test */
    public function user_can_check_out_within_valid_location()
    {
        $user = User::factory()->create();

        $branch = Branch::create([
            'name' => 'الفرع الرئيسي',
            'latitude' => 32.615430,
            'longitude' => 44.017204,
            'radius' => 100,
        ]);

        $this->actingAs($user, 'sanctum');

        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in' => now()->subHours(2)->format('H:i:s'),
            'latitude' => 32.615450,
            'longitude' => 44.017210,
            'branch_name' => $branch->name,
        ]);

        $response = $this->postJson('/api/attendance/check-out', [
            'latitude' => 32.615460,
            'longitude' => 44.017220,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function user_cannot_check_out_outside_valid_location()
    {
        $user = User::factory()->create();

        $branch = Branch::create([
            'name' => 'الفرع الرئيسي',
            'latitude' => 32.615430,
            'longitude' => 44.017204,
            'radius' => 100,
        ]);

        $this->actingAs($user, 'sanctum');

        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in' => now()->subHours(2)->format('H:i:s'),
            'latitude' => 32.615450,
            'longitude' => 44.017210,
            'branch_name' => $branch->name,
        ]);

        $response = $this->postJson('/api/attendance/check-out', [
            'latitude' => 35.000000, // خارج النطاق الجغرافي
            'longitude' => 45.000000,
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'أنت خارج نطاق مواقع الفروع المسموح بها لتسجيل الانصراف',
        ]);
    }
}

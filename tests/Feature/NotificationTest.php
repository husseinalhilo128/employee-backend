<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Notification;
use Laravel\Sanctum\Sanctum;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_send_notification()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $recipient = User::factory()->create();

        Sanctum::actingAs($admin, ['*']);

        $response = $this->postJson('/api/notifications/send', [
            'user_id' => $recipient->id,
            'title' => 'تنبيه إداري',
            'body' => 'يرجى مراجعة الحضور.',
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['message' => 'تم إرسال الإشعار']);
    }

    /** @test */
    public function non_admin_cannot_send_notification()
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $recipient = User::factory()->create();

        Sanctum::actingAs($employee, ['*']);

        $response = $this->postJson('/api/notifications/send', [
            'user_id' => $recipient->id,
            'title' => 'تنبيه مزيف',
            'body' => 'لا يجب أن يُرسل.',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_see_their_notifications()
    {
        $user = User::factory()->create();

        Notification::factory()->create([
            'user_id' => $user->id,
            'title' => 'مرحبا',
            'body' => 'هذا إشعار تجريبي.',
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/notifications');

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'مرحبا']);
    }
}

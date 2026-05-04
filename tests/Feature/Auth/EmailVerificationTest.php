<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_notice_redirects_to_code_page(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertRedirect(route('verification.code'));
    }

    public function test_email_can_be_verified_with_code(): void
    {
        Event::fake();

        $user = User::factory()->unverified()->create([
            'email_verification_code' => '123456',
            'email_verification_code_expires_at' => now()->addMinutes(30),
        ]);

        $response = $this->actingAs($user)->post(route('verification.verify-code'), [
            'code' => '123456',
        ]);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_email_is_not_verified_with_invalid_code(): void
    {
        $user = User::factory()->unverified()->create([
            'email_verification_code' => '123456',
            'email_verification_code_expires_at' => now()->addMinutes(30),
        ]);

        $this->actingAs($user)->post(route('verification.verify-code'), [
            'code' => '999999',
        ]);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}

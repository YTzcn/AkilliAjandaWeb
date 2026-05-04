<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingAndHelpTest extends TestCase
{
    use RefreshDatabase;

    private function freshUserNeedingOnboarding(): User
    {
        return User::factory()->withoutOnboarding()->create([
            'email_verified_at' => now(),
        ]);
    }

    public function test_dashboard_redirects_to_onboarding_when_incomplete(): void
    {
        $user = $this->freshUserNeedingOnboarding();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('onboarding.show'));
    }

    public function test_help_page_reachable_before_onboarding(): void
    {
        $user = $this->freshUserNeedingOnboarding();

        $this->actingAs($user)
            ->get(route('help.index'))
            ->assertOk()
            ->assertSee('Yardım merkezi', false)
            ->assertSee('İçindekiler', false);
    }

    public function test_onboarding_complete_sets_timestamp_and_goes_to_dashboard(): void
    {
        $user = $this->freshUserNeedingOnboarding();

        $this->actingAs($user)
            ->post(route('onboarding.complete'), [
                'confirm' => '1',
            ])
            ->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertNotNull($user->onboarding_completed_at);
    }

    public function test_onboarding_complete_requires_confirmation(): void
    {
        $user = $this->freshUserNeedingOnboarding();

        $this->actingAs($user)
            ->post(route('onboarding.complete'), [])
            ->assertSessionHasErrors('confirm');
    }

    public function test_completed_user_sees_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_guest_cannot_open_help(): void
    {
        $this->get(route('help.index'))
            ->assertRedirect();
    }
}

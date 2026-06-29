<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulerSettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that user can successfully save daily run time and timezone.
     */
    public function test_user_can_update_scheduler_settings()
    {
        $user = User::factory()->create();

        // Put request to save new values
        $response = $this->actingAs($user)->put(route('settings.update'), [
            'gemini_api_key' => 'test-key',
            'simulation_mode' => '1',
            'search_frequency' => 'daily',
            'daily_run_time' => '07:00',
            'timezone' => 'Asia/Kolkata',
            'max_applications_per_day' => '15',
            'min_match_score' => '80',
            'min_skills_match_score' => '75',
            'max_job_age_days' => '5',
            'preferred_location' => 'Bangalore',
            'work_modes' => ['remote', 'hybrid'],
            'naukri_email' => 'test@example.com',
            'naukri_password' => 'secret',
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        // Set the auth user context for setting retrieval
        auth()->setUser($user);

        // Verify the settings were persisted
        $this->assertEquals('daily', Setting::get('search_frequency'));
        $this->assertEquals('07:00', Setting::get('daily_run_time'));
        $this->assertEquals('Asia/Kolkata', Setting::get('timezone'));
    }

    /**
     * Test that scheduler settings validate time formats and timezone validity.
     */
    public function test_scheduler_validation_rejects_invalid_inputs()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('settings.update'), [
            'gemini_api_key' => 'test-key',
            'simulation_mode' => '1',
            'search_frequency' => 'daily',
            'daily_run_time' => 'invalid-time', // Invalid format (should be H:i)
            'timezone' => 'Invalid/Timezone',  // Invalid timezone
            'max_applications_per_day' => '15',
            'min_match_score' => '80',
            'min_skills_match_score' => '75',
            'max_job_age_days' => '5',
            'preferred_location' => 'Bangalore',
            'work_modes' => ['remote'],
        ]);

        $response->assertSessionHasErrors(['daily_run_time', 'timezone']);
    }
}

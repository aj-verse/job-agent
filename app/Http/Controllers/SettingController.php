<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'gemini_api_key' => Setting::get('gemini_api_key', env('GEMINI_API_KEY', 'your-gemini-api-key-here')),
            'simulation_mode' => Setting::get('simulation_mode', '1'),
            'search_frequency' => Setting::get('search_frequency', 'daily'),
            'daily_run_time' => Setting::get('daily_run_time', '07:00'),
            'timezone' => Setting::get('timezone', 'Asia/Kolkata'),
            'max_applications_per_day' => Setting::get('max_applications_per_day', '10'),
            'min_match_score' => Setting::get('min_match_score', '70'),
            'min_skills_match_score' => Setting::get('min_skills_match_score', '60'),
            'max_job_age_days' => Setting::get('max_job_age_days', '7'),
            'preferred_location' => Setting::get('preferred_location', 'Bangalore'),
            'preferred_companies' => Setting::get('preferred_companies', ''),
            'preferred_work_modes' => explode(',', Setting::get('preferred_work_modes', 'remote,hybrid,onsite')),
            'naukri_email' => Setting::get('naukri_email', ''),
            'naukri_password' => Setting::get('naukri_password', ''),
            'naukri_cookies' => Setting::get('naukri_cookies', ''),
            'naukri_auth_token' => Setting::get('naukri_auth_token', ''),
        ];

        return view('settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'gemini_api_key' => 'nullable|string',
            'simulation_mode' => 'required|in:0,1',
            'search_frequency' => 'required|in:hourly,daily',
            'daily_run_time' => 'required|date_format:H:i',
            'timezone' => 'required|timezone',
            'max_applications_per_day' => 'required|integer|min:1|max:100',
            'min_match_score' => 'required|integer|min:1|max:100',
            'min_skills_match_score' => 'required|integer|min:1|max:100',
            'max_job_age_days' => 'required|integer|min:1|max:90',
            'preferred_location' => 'required|string|max:255',
            'preferred_companies' => 'nullable|string',
            'work_modes' => 'required|array|min:1',
            'work_modes.*' => 'in:remote,hybrid,onsite',
            'naukri_email' => 'nullable|email|max:255',
            'naukri_password' => 'nullable|string|max:255',
            'naukri_cookies' => 'nullable|string',
            'naukri_auth_token' => 'nullable|string',
        ]);

        Setting::set('preferred_work_modes', implode(',', $validated['work_modes']));
        unset($validated['work_modes']);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully!');
    }

    public function authenticate(Request $request, \App\Services\NaukriAutomationService $automationService)
    {
        // First check if credentials are saved
        $email = Setting::get('naukri_email');
        $password = Setting::get('naukri_password');
        
        if (empty($email) || empty($password)) {
            return redirect()->back()->with('error', 'Please fill in and save your Naukri Login Email and Password first.');
        }

        $result = $automationService->loginAndExtractSession();

        if ($result['success']) {
            Setting::set('naukri_cookies', $result['cookies']);
            Setting::set('naukri_auth_token', $result['auth_token']);
            return redirect()->back()->with('success', 'Successfully logged in to Naukri.com! Session cookies and Authorization tokens extracted and saved.');
        } else {
            return redirect()->back()->with('error', 'Authentication failed: ' . ($result['error'] ?? 'Check your credentials or resolve the captcha in the browser window.'));
        }
    }
}

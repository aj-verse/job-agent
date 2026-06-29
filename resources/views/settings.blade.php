@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3">
    <div>
        <h1 class="fw-extrabold mb-1" style="letter-spacing: -1px;">Configuration Settings</h1>
        <p class="text-muted mb-0">Customize your API credentials, search priorities, and automation frequencies.</p>
    </div>
    <div>
        <form action="{{ route('settings.authenticate') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-secondary-grad px-4 py-2.5">
                <i class="fa-solid fa-key me-2"></i> Authenticate & Extract Session
            </button>
        </form>
    </div>
</div>

<div class="glass-card">
    <form action="{{ route('settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <!-- AI Layer Settings -->
        <h5 class="fw-bold mb-4 text-white"><i class="fa-solid fa-brain text-primary me-2"></i> AI Layer Credentials</h5>
        <div class="row g-4 mb-5">
            <div class="col-md-8">
                <label class="form-label text-muted text-xs fw-bold uppercase">Gemini API Key</label>
                <input type="password" name="gemini_api_key" class="form-control" value="{{ $settings['gemini_api_key'] }}" placeholder="AIzaSy...">
                <div class="form-text text-muted" style="font-size: 0.72rem; line-height: 1.4;">Used for parsing resume files and scoring candidate matching. If using dummy credentials, the system runs with realistic fallback data.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted text-xs fw-bold uppercase">Execution Mode</label>
                <select name="simulation_mode" class="form-select">
                    <option value="1" {{ $settings['simulation_mode'] === '1' ? 'selected' : '' }}>Simulation Mode (Safe Sandbox)</option>
                    <option value="0" {{ $settings['simulation_mode'] === '0' ? 'selected' : '' }}>Live Mode (Real Applications)</option>
                </select>
                <div class="form-text text-muted" style="font-size: 0.72rem; line-height: 1.4;">Safe sandbox mode simulates the browser interactions on Naukri.com without submitting real data.</div>
            </div>
        </div>

        <hr class="border-secondary border-opacity-10 my-4">

        <!-- Naukri.com Credentials -->
        <h5 class="fw-bold mb-4 text-white"><i class="fa-solid fa-user-lock text-primary me-2"></i> Naukri.com Credentials</h5>
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <label class="form-label text-muted text-xs fw-bold uppercase">Naukri Login Email</label>
                <input type="email" name="naukri_email" class="form-control" value="{{ $settings['naukri_email'] }}" placeholder="name@domain.com">
                <div class="form-text text-muted" style="font-size: 0.72rem; line-height: 1.4;">Your account username or email.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted text-xs fw-bold uppercase">Naukri Password</label>
                <input type="password" name="naukri_password" class="form-control" value="{{ $settings['naukri_password'] }}" placeholder="••••••••••••">
                <div class="form-text text-muted" style="font-size: 0.72rem; line-height: 1.4;">Your account password.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted text-xs fw-bold uppercase">Session Cookies (Auto-Extracted)</label>
                <textarea name="naukri_cookies" class="form-control text-xs" rows="1" placeholder='[{"name":"foo","value":"bar"}]'>{{ $settings['naukri_cookies'] }}</textarea>
                <div class="form-text text-muted" style="font-size: 0.72rem; line-height: 1.4;">JSON session cookies.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted text-xs fw-bold uppercase">Authorization Token</label>
                <input type="text" name="naukri_auth_token" class="form-control text-xs" value="{{ $settings['naukri_auth_token'] }}" placeholder="AppId/Authorization Token" readonly>
                <div class="form-text text-muted" style="font-size: 0.72rem; line-height: 1.4;">Automatically intercepted API header.</div>
            </div>
        </div>

        <hr class="border-secondary border-opacity-10 my-4">

        <!-- Automation Parameters -->
        <h5 class="fw-bold mb-4 text-white"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Scheduler & Discovery Rules</h5>
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <label class="form-label text-muted text-xs fw-bold uppercase">Search Frequency</label>
                <select name="search_frequency" id="search_frequency" class="form-select text-xs">
                    <option value="hourly" {{ $settings['search_frequency'] === 'hourly' ? 'selected' : '' }}>Hourly Run</option>
                    <option value="daily" {{ $settings['search_frequency'] === 'daily' ? 'selected' : '' }}>Daily Run</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted text-xs fw-bold uppercase">Daily Run Time</label>
                <input type="time" name="daily_run_time" id="daily_run_time" class="form-control text-xs" value="{{ $settings['daily_run_time'] }}">
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted text-xs fw-bold uppercase">Scheduler Timezone</label>
                <select name="timezone" class="form-select text-xs">
                    @foreach(\DateTimeZone::listIdentifiers() as $tz)
                        <option value="{{ $tz }}" {{ $settings['timezone'] === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <label class="form-label text-muted text-xs fw-bold uppercase">Max App. Per Day</label>
                <input type="number" name="max_applications_per_day" class="form-control text-xs" value="{{ $settings['max_applications_per_day'] }}" min="1" max="100">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted text-xs fw-bold uppercase">Min Overall Match %</label>
                <input type="number" name="min_match_score" class="form-control text-xs" value="{{ $settings['min_match_score'] }}" min="10" max="100">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted text-xs fw-bold uppercase">Min Skills Match %</label>
                <input type="number" name="min_skills_match_score" class="form-control text-xs" value="{{ $settings['min_skills_match_score'] }}" min="10" max="100">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted text-xs fw-bold uppercase">Max Job Age (Days)</label>
                <input type="number" name="max_job_age_days" class="form-control text-xs" value="{{ $settings['max_job_age_days'] }}" min="1" max="30">
            </div>
        </div>

        <hr class="border-secondary border-opacity-10 my-4">

        <!-- Search Filters & Preferences -->
        <h5 class="fw-bold mb-4 text-white"><i class="fa-solid fa-sliders text-primary me-2"></i> Application Preferences</h5>
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <label class="form-label text-muted text-xs fw-bold uppercase">Preferred Job Location</label>
                <input type="text" name="preferred_location" class="form-control" value="{{ $settings['preferred_location'] }}">
                <div class="form-text text-muted" style="font-size: 0.72rem;">e.g. Bangalore, Mumbai, Remote</div>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted text-xs fw-bold uppercase">Preferred Companies</label>
                <input type="text" name="preferred_companies" class="form-control" value="{{ $settings['preferred_companies'] }}" placeholder="TCS, Razorpay, IBM">
                <div class="form-text text-muted" style="font-size: 0.72rem;">Comma-separated values. prioritizes matches for these employers.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted text-xs fw-bold uppercase">Preferred Work Modes</label>
                <div class="d-flex gap-3 align-items-center mt-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="work_modes[]" value="remote" id="mode_remote" {{ in_array('remote', $settings['preferred_work_modes']) ? 'checked' : '' }}>
                        <label class="form-check-label text-white text-sm" for="mode_remote">Remote</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="work_modes[]" value="hybrid" id="mode_hybrid" {{ in_array('hybrid', $settings['preferred_work_modes']) ? 'checked' : '' }}>
                        <label class="form-check-label text-white text-sm" for="mode_hybrid">Hybrid</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="work_modes[]" value="onsite" id="mode_onsite" {{ in_array('onsite', $settings['preferred_work_modes']) ? 'checked' : '' }}>
                        <label class="form-check-label text-white text-sm" for="mode_onsite">Onsite</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 pt-3">
            <button type="submit" class="btn btn-primary-grad px-4 py-2.5">
                <i class="fa-solid fa-circle-check me-2"></i> Save Configuration
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const frequencySelect = document.getElementById('search_frequency');
        const dailyTimeInput = document.getElementById('daily_run_time');

        function toggleDailyTime() {
            if (frequencySelect.value === 'daily') {
                dailyTimeInput.removeAttribute('disabled');
            } else {
                dailyTimeInput.setAttribute('disabled', 'disabled');
                // Optional: clear or reset value when disabled
            }
        }

        frequencySelect.addEventListener('change', toggleDailyTime);
        toggleDailyTime(); // run initially
    });
</script>
@endsection

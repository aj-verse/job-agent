<?php

namespace App\Console\Commands;

use App\Http\Controllers\JobController;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunAutomation extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'jobs:automation-run';

    /**
     * The console command description.
     */
    protected $description = 'Trigger automated Naukri job scanning, matching, and application routines';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = \App\Models\User::all();
        if ($users->isEmpty()) {
            $this->warn("No users found. Registration is required first.");
            return;
        }

        $this->info("Initializing Automated Job Search and Application Routine for " . $users->count() . " users...");
        Log::info("CLI Automation started for " . $users->count() . " users.");

        foreach ($users as $user) {
            $this->info("Running automation cycle for user: {$user->name} ({$user->email})...");
            auth()->setUser($user);

            // Re-resolve JobController to get a clean instance per user
            $jobController = app(JobController::class);

            // 1. Discover Jobs
            $this->info("  - Scanning Naukri.com for matching jobs...");
            try {
                $jobController->discover();
                $this->info("  - Discovery scan finished successfully.");
            } catch (\Exception $e) {
                $this->error("  - Discovery scan failed: " . $e->getMessage());
                Log::error("CLI Job Discovery error for user {$user->id}: " . $e->getMessage());
            }

            // 2. Auto-Apply if enabled
            $this->info("  - Applying to qualified matching listings...");
            try {
                $jobController->autoApply();
                $this->info("  - Auto-apply routine finished.");
            } catch (\Exception $e) {
                $this->error("  - Auto-apply routine failed: " . $e->getMessage());
                Log::error("CLI Auto Apply error for user {$user->id}: " . $e->getMessage());
            }
        }

        $this->info("Job automation routine completed for all users.");
        Log::info("CLI Automation finished.");
    }
}

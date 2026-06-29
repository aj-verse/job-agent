<?php

namespace App\Http\Controllers;

use App\Models\NaukriJob;
use App\Models\Resume;
use App\Models\Setting;
use App\Services\AIService;
use App\Services\NaukriAutomationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JobController extends Controller
{
    protected $automationService;
    protected $aiService;

    public function __construct(NaukriAutomationService $automationService, AIService $aiService)
    {
        $this->automationService = $automationService;
        $this->aiService = $aiService;
    }

    /**
     * Display applied job history.
     */
    public function history(Request $request)
    {
        $query = NaukriJob::where('user_id', auth()->id())->whereIn('status', ['applied', 'failed']);

        // Filter by Date Range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('applied_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        // Filter by Company
        if ($request->filled('company')) {
            $query->where('company', 'like', '%' . $request->company . '%');
        }

        // Filter by Job Title
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $jobs = $query->orderBy('applied_at', 'desc')->paginate(10);
        
        // Fetch unique companies for filters
        $companies = NaukriJob::where('user_id', auth()->id())->whereNotNull('company')->distinct()->pluck('company');

        return view('jobs.history', compact('jobs', 'companies'));
    }

    /**
     * Display discovered jobs queue.
     */
    public function queue(Request $request)
    {
        $query = NaukriJob::where('user_id', auth()->id())->whereIn('status', ['discovered', 'low_match', 'duplicate', 'expired']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default show only good matches that can be applied to
            $query->where('status', 'discovered');
        }

        $jobs = $query->orderBy('match_score', 'desc')->paginate(10);

        return view('jobs.queue', compact('jobs'));
    }

    /**
     * Discover jobs from Naukri.com.
     */
    public function discover()
    {
        set_time_limit(180);
        $userId = auth()->id();
        $resume = Resume::where('user_id', $userId)->first();
        if (!$resume) {
            return redirect()->route('resume.index')->with('error', 'Please upload a resume first to extract profile data.');
        }

        // Get search criteria
        $keywords = $resume->keywords ? implode(' ', array_slice($resume->keywords, 0, 3)) : ($resume->job_role ?: 'Laravel Developer');
        $location = Setting::get('preferred_location', $resume->preferred_location ?: 'Bangalore');
        $experience = (int)$resume->experience_years;

        // Fetch jobs from crawler
        $jobsList = $this->automationService->searchJobs($keywords, $location, $experience);

        $addedCount = 0;
        $filteredCount = 0;

        $maxAgeDays = (int)Setting::get('max_job_age_days', 7);
        $minMatchThreshold = (int)Setting::get('min_match_score', 70);
        $minSkillsThreshold = (int)Setting::get('min_skills_match_score', 60);
        $preferredWorkModes = explode(',', Setting::get('preferred_work_modes', 'remote,hybrid,onsite'));

        foreach ($jobsList as $jobData) {
            // 1. Check if already exists in DB (Duplicate Prevention)
            $existingJob = NaukriJob::where('user_id', $userId)
                ->where(function ($query) use ($jobData) {
                    $query->where('job_id', $jobData['job_id'])
                          ->orWhere(function ($query) use ($jobData) {
                              $query->where('company', $jobData['company'])
                                    ->where('title', $jobData['title']);
                          });
                })
                ->first();

            if ($existingJob) {
                // If it is already applied, don't re-add as discovered
                if ($existingJob->status === 'applied') {
                    continue;
                }
                
                // Update stats and continue
                $existingJob->update(['status' => 'duplicate', 'rejection_reason' => 'Already discovered or applied in history']);
                $filteredCount++;
                continue;
            }

            // 1.5 Work Mode Filtering
            $workMode = $this->detectWorkMode($jobData);
            if (!in_array($workMode, $preferredWorkModes)) {
                NaukriJob::create(array_merge($jobData, [
                    'user_id' => $userId,
                    'status' => 'low_match',
                    'rejection_reason' => "Work mode '{$workMode}' is not preferred (" . implode(', ', $preferredWorkModes) . ")"
                ]));
                $filteredCount++;
                continue;
            }

            // 2. Parse job posting age and verify
            $ageValid = $this->isJobAgeValid($jobData['posted_date'], $maxAgeDays);
            if (!$ageValid) {
                NaukriJob::create(array_merge($jobData, [
                    'user_id' => $userId,
                    'status' => 'expired',
                    'rejection_reason' => "Posting age exceeds limit of {$maxAgeDays} days (Posted: {$jobData['posted_date']})"
                ]));
                $filteredCount++;
                continue;
            }

            // 3. AI Match Score Calculation
            $scoreData = $this->aiService->calculateMatchScore($resume->toArray(), $jobData);

            $status = 'discovered';
            $rejectionReason = null;

            if ($scoreData['match_score'] < $minMatchThreshold) {
                $status = 'low_match';
                $rejectionReason = "AI overall match score ({$scoreData['match_score']}%) is below threshold ({$minMatchThreshold}%)";
                $filteredCount++;
            } elseif ($scoreData['skills_match_score'] < $minSkillsThreshold) {
                $status = 'low_match';
                $rejectionReason = "AI skills match score ({$scoreData['skills_match_score']}%) is below threshold ({$minSkillsThreshold}%)";
                $filteredCount++;
            } else {
                $addedCount++;
            }

            NaukriJob::create(array_merge($jobData, [
                'user_id' => $userId,
                'match_score' => $scoreData['match_score'],
                'skills_match_score' => $scoreData['skills_match_score'],
                'experience_match_score' => $scoreData['experience_match_score'],
                'location_match_score' => $scoreData['location_match_score'],
                'status' => $status,
                'rejection_reason' => $rejectionReason
            ]));
        }

        return redirect()->route('jobs.queue')->with('success', "Discovery run completed! Discovered {$addedCount} new matching jobs. Filtered out {$filteredCount} jobs due to age, duplicates, or low matching scores.");
    }

    /**
     * Apply to a specific job.
     */
    public function apply($id)
    {
        set_time_limit(180);
        $userId = auth()->id();
        $job = NaukriJob::where('user_id', $userId)->findOrFail($id);

        if ($job->status === 'applied') {
            return redirect()->back()->with('warning', 'Already applied to this job.');
        }

        // Limit maximum applications per day
        $maxAppPerDay = (int)Setting::get('max_applications_per_day', 10);
        $todayAppliedCount = NaukriJob::where('user_id', $userId)
            ->where('status', 'applied')
            ->whereDate('applied_at', Carbon::today())
            ->count();

        if ($todayAppliedCount >= $maxAppPerDay) {
            return redirect()->back()->with('error', "Daily limit of {$maxAppPerDay} applications reached. Adjust this limit in Settings.");
        }

        // Run Puppeteer script to apply
        $result = $this->automationService->applyJob($job->job_url);

        if ($result['success'] && $result['applied']) {
            $job->update([
                'status' => 'applied',
                'applied_at' => Carbon::now(),
                'application_id' => $result['application_id'] ?: 'NAUKRI-' . rand(100000, 999999),
                'rejection_reason' => null
            ]);
            return redirect()->back()->with('success', "Successfully applied to {$job->title} at {$job->company}!");
        } else {
            $job->update([
                'status' => 'failed',
                'rejection_reason' => $result['error'] ?? 'Application failed due to crawler execution issue.'
            ]);
            return redirect()->back()->with('error', 'Failed to apply: ' . ($result['error'] ?? 'Automation failure'));
        }
    }

    /**
     * Auto apply to all eligible jobs in the discovered list.
     */
    public function autoApply()
    {
        set_time_limit(180);
        $userId = auth()->id();
        $jobs = NaukriJob::where('user_id', $userId)
            ->where('status', 'discovered')
            ->orderBy('match_score', 'desc')
            ->get();

        if ($jobs->isEmpty()) {
            return redirect()->back()->with('info', 'No matching jobs available in the queue to apply.');
        }

        $appliedCount = 0;
        $failedCount = 0;
        $maxAppPerDay = (int)Setting::get('max_applications_per_day', 10);

        foreach ($jobs as $job) {
            $todayAppliedCount = NaukriJob::where('user_id', $userId)
                ->where('status', 'applied')
                ->whereDate('applied_at', Carbon::today())
                ->count();

            if ($todayAppliedCount >= $maxAppPerDay) {
                break; // Stop if daily limit reached
            }

            $result = $this->automationService->applyJob($job->job_url);

            if ($result['success'] && $result['applied']) {
                $job->update([
                    'status' => 'applied',
                    'applied_at' => Carbon::now(),
                    'application_id' => $result['application_id'] ?: 'NAUKRI-' . rand(100000, 999999),
                    'rejection_reason' => null
                ]);
                $appliedCount++;
            } else {
                $job->update([
                    'status' => 'failed',
                    'rejection_reason' => $result['error'] ?? 'Automation script failed.'
                ]);
                $failedCount++;
            }
        }

        return redirect()->route('jobs.history')->with('success', "Auto-apply process finished. Successfully applied to {$appliedCount} jobs. Failed on {$failedCount} jobs.");
    }

    /**
     * Helper to verify if the job posting age is within limit.
     */
    protected function isJobAgeValid(?string $postedText, int $maxAgeDays): bool
    {
        if (empty($postedText)) return true;

        $postedText = strtolower($postedText);
        
        // "30+ days ago", "15 days ago", "7 days ago", "active"
        if (str_contains($postedText, '30+')) {
            return $maxAgeDays >= 30;
        }

        if (preg_match('/(\d+)\s*day/i', $postedText, $matches)) {
            $days = (int)$matches[1];
            return $days <= $maxAgeDays;
        }

        if (str_contains($postedText, 'today') || str_contains($postedText, 'just') || str_contains($postedText, 'hour')) {
            return true;
        }

        return true;
    }

    /**
     * Detect work mode of a job listing from its text fields.
     */
    protected function detectWorkMode(array $jobData): string
    {
        $text = strtolower(
            ($jobData['title'] ?? '') . ' ' . 
            ($jobData['location'] ?? '') . ' ' . 
            ($jobData['description'] ?? '')
        );

        if (str_contains($text, 'remote')) {
            return 'remote';
        }
        if (str_contains($text, 'hybrid')) {
            return 'hybrid';
        }
        return 'onsite';
      }
}

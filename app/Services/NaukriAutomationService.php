<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class NaukriAutomationService
{
    /**
     * Search and discover jobs from Naukri.com.
     */
    public function searchJobs(string $keywords, string $location, int $experience = 0): array
    {
        $simulation = Setting::get('simulation_mode', '1') === '1';
        
        if ($simulation) {
            return $this->getFallbackJobs($keywords, $location);
        }
        
        try {
            $nodePath = $this->getNodePath();
            $scriptPath = base_path('bin/naukri-scraper.cjs');

            $command = [
                $nodePath,
                $scriptPath,
                '--action=search',
                '--keywords=' . $keywords,
                '--location=' . $location,
                '--experience=' . $experience,
                '--simulation=' . ($simulation ? 'true' : 'false')
            ];

            Log::info("Executing Naukri search: " . implode(' ', $command));
            $result = Process::env($this->getSafeEnvironment())->run($command);

            if ($result->failed()) {
                Log::error("Naukri scraper process failed: " . $result->errorOutput());
                return $this->getFallbackJobs($keywords, $location);
            }

            $output = json_decode(trim($result->output()), true);
            if (!$output || !isset($output['success']) || !$output['success']) {
                Log::warning("Naukri scraper failed to return success, using fallback data.");
                return $this->getFallbackJobs($keywords, $location);
            }

            return $output['jobs'] ?? [];
        } catch (\Exception $e) {
            Log::error("Naukri searchJobs exception: " . $e->getMessage());
            return $this->getFallbackJobs($keywords, $location);
        }
    }

    /**
     * Automate application submission to a specific job URL.
     */
    public function applyJob(string $jobUrl): array
    {
        $simulation = Setting::get('simulation_mode', '1') === '1';

        if ($simulation) {
            return [
                'success' => true,
                'applied' => true,
                'application_id' => 'SIM-APP-' . rand(100000, 999999),
                'status' => 'Applied'
            ];
        }

        try {
            $nodePath = $this->getNodePath();
            $scriptPath = base_path('bin/naukri-scraper.cjs');

            $command = [
                $nodePath,
                $scriptPath,
                '--action=apply',
                '--job_url=' . $jobUrl,
                '--simulation=' . ($simulation ? 'true' : 'false')
            ];

            $env = $this->getSafeEnvironment([
                'NAUKRI_EMAIL' => Setting::get('naukri_email', ''),
                'NAUKRI_PASSWORD' => Setting::get('naukri_password', ''),
                'NAUKRI_COOKIES' => Setting::get('naukri_cookies', ''),
            ]);

            $result = Process::env($env)->run($command);

            if ($result->failed()) {
                Log::error("Naukri apply process failed: " . $result->errorOutput());
                $errorOutput = $result->errorOutput();
                if (str_contains($errorOutput, 'CSPRNG') || str_contains($errorOutput, 'ncrypto') || str_contains($errorOutput, 'InitializeOncePerProcess')) {
                    return [
                        'success' => false,
                        'error' => "NodeJS Cryptographic Crash: Your local Node.js binary crashed because of a known Windows/OpenSSL security block (ncrypto::CSPRNG assertion failed). To resolve this, update Node.js on your computer to the latest stable LTS version, or switch 'Execution Mode' to 'Simulation Mode' in Settings to run the sandbox."
                    ];
                }
                return ['success' => false, 'error' => $errorOutput];
            }

            $output = json_decode(trim($result->output()), true);
            if (!$output || !isset($output['success']) || !$output['success']) {
                return ['success' => false, 'error' => $output['error'] ?? 'Unknown scraping error'];
            }

            return [
                'success' => true,
                'applied' => $output['applied'] ?? false,
                'application_id' => $output['applicationId'] ?? null,
                'status' => $output['status'] ?? 'Applied'
            ];
        } catch (\Exception $e) {
            Log::error("Naukri applyJob exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Run login extraction to fetch auth token and session cookies.
     */
    public function loginAndExtractSession(): array
    {
        try {
            $nodePath = $this->getNodePath();
            $scriptPath = base_path('bin/naukri-scraper.cjs');

            $command = [
                $nodePath,
                $scriptPath,
                '--action=login',
                '--simulation=false'
            ];

            $env = $this->getSafeEnvironment([
                'NAUKRI_EMAIL' => Setting::get('naukri_email', ''),
                'NAUKRI_PASSWORD' => Setting::get('naukri_password', ''),
            ]);

            Log::info("Executing Naukri login session extraction...");
            $result = Process::env($env)->run($command);

            if ($result->failed()) {
                return ['success' => false, 'error' => $result->errorOutput() ?: 'Process execution failed.'];
            }

            $output = json_decode(trim($result->output()), true);
            if (!$output || !isset($output['success']) || !$output['success']) {
                return ['success' => false, 'error' => $output['error'] ?? 'Authentication failed or timed out.'];
            }

            return [
                'success' => true,
                'cookies' => $output['cookies'] ?? null,
                'auth_token' => $output['authToken'] ?? null
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Dynamic PHP fallback for search results if NodeJS process execution fails.
     */
    protected function getFallbackJobs(string $keywords, string $location): array
    {
        $companies = ['Tech Solutions', 'Cloud Services Inc', 'InnoSoft Labs', 'Global Digital Co', 'Sprint Systems'];
        $expRanges = ['2-4 years', '1-3 years', '3-5 years', '0-2 years'];
        $salaries = ['5,00,000 - 8,00,000 INR', 'Not disclosed', '7,50,000 - 12,00,000 INR'];
        $jobs = [];

        for ($i = 1; $i <= 5; $i++) {
            $jobId = 'fb-' . rand(100000, 999999);
            $jobs[] = [
                'job_id' => $jobId,
                'title' => $keywords . ' - Support Developer',
                'company' => $companies[$i - 1],
                'location' => $location . ', India',
                'salary' => $salaries[rand(0, 2)],
                'experience_required' => $expRanges[rand(0, 3)],
                'description' => "We are looking for a qualified {$keywords} to join our tech team. Responsibilities include building backend logic, writing clean code, and working with APIs.",
                'posted_date' => '3 days ago',
                'job_url' => "https://www.naukri.com/job-listings-{$jobId}"
            ];
        }

        return $jobs;
    }

    /**
     * Get safe environment variables for running Node.js / Puppeteer on Windows.
     */
    private function getSafeEnvironment(array $extra = []): array
    {
        $criticalEnv = [
            'SystemRoot' => getenv('SystemRoot') ?: 'C:\Windows',
            'SystemDrive' => getenv('SystemDrive') ?: 'C:',
            'PATH' => getenv('PATH'),
            'USERPROFILE' => getenv('USERPROFILE'),
            'LOCALAPPDATA' => getenv('LOCALAPPDATA'),
            'APPDATA' => getenv('APPDATA'),
            'TEMP' => sys_get_temp_dir(),
            'TMP' => sys_get_temp_dir(),
        ];

        return array_merge(
            $_ENV,
            getenv() ?: [],
            array_filter($criticalEnv),
            $extra
        );
    }

    /**
     * Resolve the path to the Node.js executable based on the operating system.
     */
    protected function getNodePath(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return base_path('bin/node-portable/node-v20.11.0-win-x64/node.exe');
        }
        
        return 'node';
    }
}

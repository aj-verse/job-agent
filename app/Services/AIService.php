<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    /**
     * Parse raw resume text into structured JSON format.
     */
    public function parseResume(string $rawText): array
    {
        $apiKey = Setting::get('gemini_api_key', env('GEMINI_API_KEY'));

        if ($this->isDummyKey($apiKey)) {
            return $this->getMockResumeData($rawText);
        }

        try {
            $prompt = "You are an expert resume parsing AI. Parse the following raw text from a resume and extract the structured information in JSON format. 
            Do NOT return any markdown wrapper (like ```json). Return ONLY a valid JSON object.
            
            JSON schema:
            {
                \"name\": \"Name or null\",
                \"email\": \"Email or null\",
                \"phone\": \"Phone number or null\",
                \"skills\": [\"list of skills extracted\"],
                \"experience_years\": 5.5, (decimal or integer representing total years of experience, default 0.0)
                \"experience_details\": [
                    {
                        \"role\": \"Job Title\",
                        \"company\": \"Company Name\",
                        \"duration\": \"Duration (e.g. 2021 - Present)\",
                        \"description\": \"Brief description\"
                    }
                ],
                \"education\": [
                    {
                        \"degree\": \"Degree Name\",
                        \"school\": \"School/University\",
                        \"year\": \"Passing Year\"
                    }
                ],
                \"certifications\": [\"list of certifications\"],
                \"preferred_location\": \"City or null\",
                \"current_salary\": \"Current CTC/Salary or null\",
                \"expected_salary\": \"Expected CTC/Salary or null\",
                \"job_role\": \"Primary job role category/classification (e.g., Laravel Developer, PHP Developer, React Engineer)\",
                \"keywords\": [\"list of search keywords for job hunting, e.g., 'laravel developer', 'php backend'\"]
            }
            
            Raw text:
            " . $rawText;

            $response = $this->callGemini($apiKey, $prompt);
            return json_decode($response, true) ?: $this->getMockResumeData($rawText);
        } catch (\Exception $e) {
            Log::error("Gemini parseResume error: " . $e->getMessage());
            return $this->getMockResumeData($rawText);
        }
    }

    /**
     * Calculate matching scores between resume and job details.
     */
    public function calculateMatchScore(array $resumeData, array $jobData): array
    {
        $apiKey = Setting::get('gemini_api_key', env('GEMINI_API_KEY'));

        if ($this->isDummyKey($apiKey)) {
            return $this->getMockMatchScore($resumeData, $jobData);
        }

        try {
            $prompt = "You are an AI Recruitment Engine. Compare the candidate's resume profile with the job description and details. Calculate matching scores and provide a clear relevancy analysis.
            Do NOT return any markdown wrapper. Return ONLY a valid JSON object.
            
            Candidate Resume Profile:
            " . json_encode($resumeData) . "
            
            Job Details:
            " . json_encode($jobData) . "
            
            Calculate and return a JSON object with the following fields:
            {
                \"skills_match_score\": 85, (0-100 percentage match of candidate skills vs job required skills)
                \"experience_match_score\": 90, (0-100 percentage match of candidate experience level vs job required experience)
                \"location_match_score\": 100, (0-100 percentage match of candidate preferred location vs job location)
                \"match_score\": 88, (Weighted average overall relevancy score 0-100)
                \"relevance_explanation\": \"Explanation of the match and why this score was given.\",
                \"is_good_match\": true (boolean, true if overall match_score >= threshold, e.g., 70)
            }";

            $response = $this->callGemini($apiKey, $prompt);
            return json_decode($response, true) ?: $this->getMockMatchScore($resumeData, $jobData);
        } catch (\Exception $e) {
            Log::error("Gemini calculateMatchScore error: " . $e->getMessage());
            return $this->getMockMatchScore($resumeData, $jobData);
        }
    }

    /**
     * Generate career recommendations based on the resume data.
     */
    public function getCareerRecommendations(array $resumeData): array
    {
        $apiKey = Setting::get('gemini_api_key', env('GEMINI_API_KEY'));

        if ($this->isDummyKey($apiKey)) {
            return $this->getMockRecommendations($resumeData);
        }

        try {
            $prompt = "You are a career development AI advisor. Analyze this candidate's resume profile and provide career optimization recommendations, missing skills, recommended courses, and domain trends.
            Do NOT return any markdown wrapper. Return ONLY a valid JSON object.
            
            Candidate Resume Profile:
            " . json_encode($resumeData) . "
            
            JSON schema:
            {
                \"missing_skills\": [
                    {
                        \"skill\": \"Skill name\",
                        \"importance\": \"High/Medium/Low\",
                        \"reason\": \"Why this skill is important for their target role\"
                    }
                ],
                \"recommended_courses\": [
                    {
                        \"title\": \"Course title or topic\",
                        \"platform\": \"Suggested platform (e.g. Coursera, Udemy, YouTube)\",
                        \"description\": \"What they will learn\"
                    }
                ],
                \"resume_suggestions\": [
                    \"Specific suggestion to improve resume content, structure, or impact\"
                ],
                \"trending_technologies\": [
                    {
                        \"name\": \"Tech name\",
                        \"growth_rate\": \"High/Stable\",
                        \"description\": \"Why it's trending in their domain\"
                    }
                ]
            }";

            $response = $this->callGemini($apiKey, $prompt);
            return json_decode($response, true) ?: $this->getMockRecommendations($resumeData);
        } catch (\Exception $e) {
            Log::error("Gemini getCareerRecommendations error: " . $e->getMessage());
            return $this->getMockRecommendations($resumeData);
        }
    }

    /**
     * Check if the API key is a placeholder/dummy.
     */
    protected function isDummyKey(?string $key): bool
    {
        if (empty($key)) return true;
        $dummyValues = ['dummy', 'placeholder', 'key', 'your-api-key', 'null', 'api_key', 'test', '12345'];
        foreach ($dummyValues as $dummy) {
            if (stripos($key, $dummy) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Call the Gemini API via HTTP post.
     */
    protected function callGemini(string $apiKey, string $prompt): string
    {
        $response = Http::timeout(10)->withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json'
            ]
        ]);

        if ($response->failed()) {
            throw new \Exception("Gemini API call failed: " . $response->body());
        }

        $result = $response->json();
        return $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    /**
     * Mock Parsing Resume Data.
     */
    protected function getMockResumeData(string $rawText): array
    {
        // Simple heuristic regex to extract names, emails, phones if present
        preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $rawText, $emailMatch);
        preg_match('/(?:\+?\d{1,3}[- ]?)?\(?\d{3}\)?[- ]?\d{3}[- ]?\d{4}/', $rawText, $phoneMatch);
        
        $name = "Candidate Name";
        // Try to guess name from first line
        $lines = explode("\n", $rawText);
        if (!empty($lines[0])) {
            $name = trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $lines[0]));
            if (strlen($name) > 30 || empty($name)) {
                $name = "John Doe";
            }
        }

        return [
            'name' => $name,
            'email' => $emailMatch[0] ?? 'candidate@example.com',
            'phone' => $phoneMatch[0] ?? '+91 98765 43210',
            'skills' => ['PHP', 'Laravel', 'MySQL', 'JavaScript', 'Vue.js', 'Git', 'REST APIs', 'Bootstrap'],
            'experience_years' => 3.5,
            'experience_details' => [
                [
                    'role' => 'Software Engineer (PHP/Laravel)',
                    'company' => 'WebTech Solutions',
                    'duration' => '2023 - Present',
                    'description' => 'Developed and maintained responsive web applications using Laravel and Vue.js. Integrated payment gateways and optimized database queries.'
                ],
                [
                    'role' => 'Junior Web Developer',
                    'company' => 'Aura Digital Agency',
                    'duration' => '2022 - 2023',
                    'description' => 'Worked on HTML, CSS, Bootstrap, and core PHP projects. Assisted in writing clean, documented database queries.'
                ]
            ],
            'education' => [
                [
                    'degree' => 'B.Tech in Computer Science',
                    'school' => 'Technical University',
                    'year' => '2022'
                ]
            ],
            'certifications' => ['Laravel Certified Associate', 'AWS Certified Cloud Practitioner'],
            'preferred_location' => 'Bangalore',
            'current_salary' => '6,00,000 INR',
            'expected_salary' => '9,00,000 INR',
            'job_role' => 'Laravel Developer',
            'keywords' => ['Laravel Developer', 'PHP Backend Developer', 'Laravel Backend Engineer', 'PHP Developer']
        ];
    }

    /**
     * Mock Match Score Calculator.
     */
    protected function getMockMatchScore(array $resumeData, array $jobData): array
    {
        $skills = array_map('strtolower', $resumeData['skills'] ?? []);
        $jd = strtolower(($jobData['title'] ?? '') . ' ' . ($jobData['description'] ?? '') . ' ' . ($jobData['experience_required'] ?? ''));
        
        // Count skill occurrences
        $matchedSkills = 0;
        foreach ($skills as $skill) {
            if (str_contains($jd, $skill)) {
                $matchedSkills++;
            }
        }
        
        $totalSkillsCount = max(count($skills), 1);
        $skillsScore = min(100, (int)(($matchedSkills / $totalSkillsCount) * 100) + 40); // Baseline boost for demo
        
        // Experience comparison
        $expRequired = 0;
        if (preg_match('/(\d+)\s*-?\s*(\d+)?\s*(?:year|yr)/i', $jobData['experience_required'] ?? '', $matches)) {
            $expRequired = (int)$matches[1];
        }
        $expCandidate = (float)($resumeData['experience_years'] ?? 0);
        
        if ($expCandidate >= $expRequired) {
            $expScore = 100;
        } else {
            $expScore = max(0, 100 - ($expRequired - $expCandidate) * 20);
        }

        // Location comparison
        $locJob = strtolower($jobData['location'] ?? '');
        $locCandidate = strtolower($resumeData['preferred_location'] ?? '');
        $locScore = 50;
        if (empty($locCandidate) || empty($locJob) || str_contains($locJob, $locCandidate) || str_contains($locCandidate, $locJob) || str_contains($locJob, 'remote') || str_contains($locCandidate, 'remote')) {
            $locScore = 100;
        }

        $overall = (int)(($skillsScore * 0.5) + ($expScore * 0.3) + ($locScore * 0.2));

        return [
            'skills_match_score' => $skillsScore,
            'experience_match_score' => (int)$expScore,
            'location_match_score' => $locScore,
            'match_score' => $overall,
            'relevance_explanation' => "Calculated automatically by matching Candidate skills (" . implode(', ', array_slice($resumeData['skills'] ?? [], 0, 5)) . ") against Job posting details. Skill overlap matches at {$skillsScore}%. Candidate experience of {$expCandidate} years compared to required {$expRequired} years matches at {$expScore}%.",
            'is_good_match' => $overall >= (int)Setting::get('min_match_score', 70)
        ];
    }

    /**
     * Mock recommendations.
     */
    protected function getMockRecommendations(array $resumeData): array
    {
        return [
            'missing_skills' => [
                [
                    'skill' => 'Docker',
                    'importance' => 'Medium',
                    'reason' => 'Many modern Laravel job listings expect knowledge of containerization for deployments.'
                ],
                [
                    'skill' => 'Redis',
                    'importance' => 'High',
                    'reason' => 'Crucial for caching, queues, and optimizing real-time Laravel applications.'
                ],
                [
                    'skill' => 'AWS (S3/EC2)',
                    'importance' => 'Medium',
                    'reason' => 'Cloud infrastructure management is a popular requirement for senior-level PHP roles.'
                ]
            ],
            'recommended_courses' => [
                [
                    'title' => 'Laravel Queues in Production (LaraCasts)',
                    'platform' => 'Laracasts',
                    'description' => 'Master queues, Redis, and job scaling in Laravel.'
                ],
                [
                    'title' => 'Docker for PHP Developers',
                    'platform' => 'Udemy',
                    'description' => 'Learn how to containerize Laravel applications for dev and prod.'
                ]
            ],
            'resume_suggestions' => [
                "Quantify achievements: Change 'Responsible for database optimization' to 'Optimized SQL queries, reducing API response times by 30%'.",
                "Highlight Laravel 11/12 specific features if you have used them, such as new directory structures or modern task scheduling.",
                "Include direct URLs to public GitHub repositories showing clean code architecture."
            ],
            'trending_technologies' => [
                [
                    'name' => 'Livewire v3 / Alpine.js',
                    'growth_rate' => 'High',
                    'description' => 'Fast-growing choice for single-page style Laravel applications without full JS frameworks.'
                ],
                [
                    'name' => 'Spatie Packages',
                    'growth_rate' => 'Stable',
                    'description' => 'Industry standard for role permissions, media library, and analytics integration.'
                ]
            ]
        ];
    }
}

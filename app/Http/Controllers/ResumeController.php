<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Services\AIService;
use App\Services\ResumeParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResumeController extends Controller
{
    protected $parserService;
    protected $aiService;

    public function __construct(ResumeParserService $parserService, AIService $aiService)
    {
        $this->parserService = $parserService;
        $this->aiService = $aiService;
    }

    public function index()
    {
        $resume = Resume::where('user_id', auth()->id())->first();
        return view('resume', compact('resume'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'resume' => 'required|file|mimes:pdf,docx|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('resume');
            $extension = $file->getClientOriginalExtension();
            $path = $file->storeAs('resumes', 'resume_' . time() . '.' . $extension, 'public');

            $absolutePath = storage_path('app/public/' . $path);
            
            // Extract raw text
            $rawText = $this->parserService->extractText($absolutePath, $extension);
            
            // Parse with AI
            $parsedData = $this->aiService->parseResume($rawText);

            // Generate recommendations and cache them
            $recommendations = $this->aiService->getCareerRecommendations($parsedData);
            $parsedData['ai_analysis'] = $recommendations;
            $parsedData['resume_path'] = $path;

            // Delete old resume file if exists
            $oldResume = Resume::where('user_id', auth()->id())->first();
            if ($oldResume && $oldResume->resume_path) {
                Storage::disk('public')->delete($oldResume->resume_path);
                $oldResume->delete();
            }

            // Save to DB
            Resume::create(array_merge($parsedData, ['user_id' => auth()->id()]));

            return redirect()->route('resume.index')->with('success', 'Resume uploaded, parsed, and AI analyzed successfully!');
        } catch (\Exception $e) {
            return redirect()->route('resume.index')->with('error', 'Failed to process resume: ' . $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        $resume = Resume::where('user_id', auth()->id())->firstOrFail();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'skills' => 'required|string', // Comma separated
            'experience_years' => 'required|numeric|min:0',
            'preferred_location' => 'nullable|string|max:255',
            'current_salary' => 'nullable|string|max:255',
            'expected_salary' => 'nullable|string|max:255',
            'job_role' => 'nullable|string|max:255',
        ]);

        // Convert skills string back to array
        $skillsArray = array_map('trim', explode(',', $validated['skills']));
        
        // Regenerate keywords list automatically
        $keywords = array_merge([$validated['job_role']], array_slice($skillsArray, 0, 3));
        $keywords = array_values(array_unique(array_filter($keywords)));

        $resume->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'skills' => $skillsArray,
            'experience_years' => $validated['experience_years'],
            'preferred_location' => $validated['preferred_location'],
            'current_salary' => $validated['current_salary'],
            'expected_salary' => $validated['expected_salary'],
            'job_role' => $validated['job_role'],
            'keywords' => $keywords,
        ]);

        return redirect()->route('resume.index')->with('success', 'Profile updated successfully!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function index()
    {
        $resume = Resume::where('user_id', auth()->id())->first();
        
        $recommendations = null;
        if ($resume && $resume->ai_analysis) {
            $recommendations = $resume->ai_analysis;
        }

        return view('recommendations', compact('resume', 'recommendations'));
    }
}

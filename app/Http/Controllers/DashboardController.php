<?php

namespace App\Http\Controllers;

use App\Models\NaukriJob;
use App\Models\Resume;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $resume = Resume::where('user_id', $userId)->first();
        
        // Basic Overview Metrics
        $totalFound = NaukriJob::where('user_id', $userId)->count();
        $totalApplied = NaukriJob::where('user_id', $userId)->where('status', 'applied')->count();
        
        $today = Carbon::today();
        $appliedToday = NaukriJob::where('user_id', $userId)
            ->where('status', 'applied')
            ->whereDate('applied_at', $today)
            ->count();
            
        $startOfWeek = Carbon::now()->startOfWeek();
        $appliedThisWeek = NaukriJob::where('user_id', $userId)
            ->where('status', 'applied')
            ->where('applied_at', '>=', $startOfWeek)
            ->count();
            
        $startOfMonth = Carbon::now()->startOfMonth();
        $appliedThisMonth = NaukriJob::where('user_id', $userId)
            ->where('status', 'applied')
            ->where('applied_at', '>=', $startOfMonth)
            ->count();
            
        // Success Rate: Applied / (Applied + Failed + Low Match) -> jobs processed
        $processedCount = NaukriJob::where('user_id', $userId)
            ->whereIn('status', ['applied', 'low_match', 'failed'])
            ->count();
        $successRate = $processedCount > 0 ? round(($totalApplied / $processedCount) * 100) : 0;
        
        $avgMatchScore = NaukriJob::where('user_id', $userId)->where('status', 'applied')->avg('match_score') ?: 0;
        
        // --- Visualizations Charts Data ---
        
        // 1. Daily Application Trend (Last 7 Days)
        $dailyTrend = NaukriJob::select(DB::raw("DATE(applied_at) as date"), DB::raw("COUNT(*) as count"))
            ->where('user_id', $userId)
            ->where('status', 'applied')
            ->where('applied_at', '>=', Carbon::now()->subDays(7))
            ->groupBy(DB::raw("DATE(applied_at)"))
            ->orderBy('date', 'ASC')
            ->get()
            ->pluck('count', 'date');
            
        // Fill dates for last 7 days
        $dailyTrendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateStr = Carbon::now()->subDays($i)->format('Y-m-d');
            $dailyTrendData[$dateStr] = $dailyTrend[$dateStr] ?? 0;
        }

        // 2. Company-wise Applications (Top 5)
        $companyApplications = NaukriJob::select('company', DB::raw('COUNT(*) as count'))
            ->where('user_id', $userId)
            ->where('status', 'applied')
            ->groupBy('company')
            ->orderBy('count', 'DESC')
            ->limit(5)
            ->get();

        // 3. Location-wise Applications
        $locationApplications = NaukriJob::select('location', DB::raw('COUNT(*) as count'))
            ->where('user_id', $userId)
            ->where('status', 'applied')
            ->groupBy('location')
            ->orderBy('count', 'DESC')
            ->limit(5)
            ->get();

        // 4. Rejection/Filter Reason Breakdown
        $rejectionReasons = NaukriJob::select('status', DB::raw('COUNT(*) as count'))
            ->where('user_id', $userId)
            ->whereIn('status', ['duplicate', 'low_match', 'expired', 'failed'])
            ->groupBy('status')
            ->get();

        // 5. Match Score Distribution
        $scoreDistribution = [
            '90-100%' => NaukriJob::where('user_id', $userId)->whereBetween('match_score', [90, 100])->count(),
            '80-89%' => NaukriJob::where('user_id', $userId)->whereBetween('match_score', [80, 89])->count(),
            '70-79%' => NaukriJob::where('user_id', $userId)->whereBetween('match_score', [70, 79])->count(),
            '<70%' => NaukriJob::where('user_id', $userId)->where('match_score', '<', 70)->count(),
        ];

        return view('dashboard', compact(
            'resume',
            'totalFound',
            'totalApplied',
            'appliedToday',
            'appliedThisWeek',
            'appliedThisMonth',
            'successRate',
            'avgMatchScore',
            'dailyTrendData',
            'companyApplications',
            'locationApplications',
            'rejectionReasons',
            'scoreDistribution'
        ));
    }
}

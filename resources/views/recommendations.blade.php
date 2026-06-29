@extends('layouts.app')

@section('content')
<div class="mb-5">
    <h1 class="fw-extrabold mb-1" style="letter-spacing: -1px;">AI Career recommendations</h1>
    <p class="text-muted mb-0">Missing skills, course tracks, and resume adjustments formulated by AI based on your profile.</p>
</div>

@if(!$resume)
    <div class="glass-card text-center py-5">
        <i class="fa-solid fa-lightbulb fs-1 text-muted mb-4 opacity-50"></i>
        <h4 class="fw-bold mb-2">No Recommendations Available</h4>
        <p class="text-muted mx-auto mb-4" style="max-width: 450px;">Upload a resume to analyze your profile gaps and generate automatic training suggestions.</p>
        <a href="{{ route('resume.index') }}" class="btn btn-primary-grad px-4 py-2.5">
            <i class="fa-solid fa-cloud-arrow-up me-2"></i> Upload Resume
        </a>
    </div>
@else
    <div class="row g-4">
        <!-- Left Side: Gaps & Suggestions -->
        <div class="col-12 col-lg-7">
            <!-- Missing Skills Card -->
            <div class="glass-card mb-4">
                <h5 class="fw-bold mb-4 text-white"><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i> Priority Skill Gaps</h5>
                @if(!empty($recommendations['missing_skills']))
                    <div class="d-flex flex-column gap-3">
                        @foreach($recommendations['missing_skills'] as $skillItem)
                            @php
                                $isHigh = $skillItem['importance'] === 'High';
                                $borderClr = $isHigh ? 'rgba(239, 68, 68, 0.4)' : 'rgba(245, 158, 11, 0.4)';
                                $bgClr = $isHigh ? 'rgba(239, 68, 68, 0.02)' : 'rgba(245, 158, 11, 0.02)';
                                $badgeClass = $isHigh ? 'bg-danger' : 'bg-warning text-dark';
                            @endphp
                            <div class="p-3.5 rounded-4 border-start border-4 border-top border-bottom border-end border-secondary border-opacity-10" style="border-left-color: {{ $borderClr }} !important; background: {{ $bgClr }};">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold text-white mb-0">{{ $skillItem['skill'] }}</h6>
                                    <span class="badge {{ $badgeClass }} text-xxs px-2.5 py-1 rounded">
                                        {{ $skillItem['importance'] }} Priority
                                    </span>
                                </div>
                                <p class="text-muted text-xs mb-0 lh-base">{{ $skillItem['reason'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">No gaps parsed. Your profile perfectly matches the targeted role requirements!</p>
                @endif
            </div>

            <!-- Resume Suggestions -->
            <div class="glass-card">
                <h5 class="fw-bold mb-4 text-white"><i class="fa-solid fa-wand-magic-sparkles text-primary me-2"></i> Resume Enhancement Suggestions</h5>
                @if(!empty($recommendations['resume_suggestions']))
                    <div class="d-flex flex-column gap-2">
                        @foreach($recommendations['resume_suggestions'] as $suggestion)
                            <div class="p-3 rounded-4 border border-secondary border-opacity-10 d-flex align-items-start gap-3" style="background: rgba(255, 255, 255, 0.005);">
                                <i class="fa-solid fa-circle-check text-success fs-5 mt-0.5"></i>
                                <span class="text-xs text-muted lh-base">{{ $suggestion }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">Your resume structure satisfies AI matching protocols.</p>
                @endif
            </div>
        </div>

        <!-- Right Side: Courses & Market Trends -->
        <div class="col-12 col-lg-5">
            <!-- Market Trends -->
            <div class="glass-card mb-4">
                <h5 class="fw-bold mb-4 text-white"><i class="fa-solid fa-chart-line text-info me-2"></i> Tech Trends in Your Domain</h5>
                @if(!empty($recommendations['trending_technologies']))
                    <div class="d-flex flex-column gap-3">
                        @foreach($recommendations['trending_technologies'] as $tech)
                            <div class="p-3.5 rounded-4 border border-secondary border-opacity-10" style="background: rgba(6, 182, 212, 0.02); border-left: 3px solid rgba(6, 182, 212, 0.3) !important;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="fw-bold text-white mb-0">{{ $tech['name'] }}</h6>
                                    <span class="badge bg-info text-white text-xxs px-2 py-1 rounded">
                                        {{ $tech['growth_rate'] }} Demand
                                    </span>
                                </div>
                                <p class="text-muted text-xs mb-0 lh-base">{{ $tech['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">No market trends loaded.</p>
                @endif
            </div>

            <!-- Recommended Learning -->
            <div class="glass-card">
                <h5 class="fw-bold mb-4 text-white"><i class="fa-solid fa-graduation-cap text-success me-2"></i> Recommended Course Tracks</h5>
                @if(!empty($recommendations['recommended_courses']))
                    <div class="d-flex flex-column gap-3">
                        @foreach($recommendations['recommended_courses'] as $course)
                            <div class="d-flex align-items-start gap-3 p-3 rounded-4 border border-secondary border-opacity-10" style="background: rgba(16, 185, 129, 0.02); border-left: 3px solid rgba(16, 185, 129, 0.3) !important;">
                                <div class="p-2 bg-success bg-opacity-10 text-success rounded-3 border border-success border-opacity-20 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                    <i class="fa-solid fa-book-open fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold text-white mb-1 fs-7">{{ $course['title'] }}</h6>
                                    <span class="badge bg-dark border border-secondary border-opacity-20 text-muted text-xxs px-2 py-1 rounded mb-2">{{ $course['platform'] }}</span>
                                    <p class="text-muted text-xxs mb-0 lh-base">{{ $course['description'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">No course recommendations available.</p>
                @endif
            </div>
        </div>
    </div>
@endif
@endsection

@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3">
    <div>
        <h1 class="fw-extrabold mb-1" style="letter-spacing: -1px;">Discovery Queue</h1>
        <p class="text-muted mb-0">Manage crawled job openings on Naukri matching your profile requirements.</p>
    </div>
    <div class="d-flex gap-3">
        <form action="{{ route('jobs.discover') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-secondary-grad px-4 py-2.5">
                <i class="fa-solid fa-arrows-rotate me-2"></i> Crawl Jobs
            </button>
        </form>
        <form action="{{ route('jobs.autoApply') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary-grad px-4 py-2.5">
                <i class="fa-solid fa-paper-plane me-2"></i> Auto-Apply All
            </button>
        </form>
    </div>
</div>

<!-- Modern Filter Pills Bar -->
<div class="glass-card mb-4 py-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <span class="text-xs text-muted fw-bold uppercase"><i class="fa-solid fa-filter text-primary me-2"></i> Filter Status</span>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('jobs.queue') }}" class="btn btn-sm px-3 rounded-pill border {{ request('status') === null ? 'btn-primary border-primary' : 'btn-outline-secondary border-secondary border-opacity-20 text-white' }} text-xs fw-semibold">
                Qualified Matching
            </a>
            <a href="{{ route('jobs.queue', ['status' => 'low_match']) }}" class="btn btn-sm px-3 rounded-pill border {{ request('status') === 'low_match' ? 'btn-primary border-primary' : 'btn-outline-secondary border-secondary border-opacity-20 text-white' }} text-xs fw-semibold">
                Low Match Skip
            </a>
            <a href="{{ route('jobs.queue', ['status' => 'expired']) }}" class="btn btn-sm px-3 rounded-pill border {{ request('status') === 'expired' ? 'btn-primary border-primary' : 'btn-outline-secondary border-secondary border-opacity-20 text-white' }} text-xs fw-semibold">
                Outdated Skip
            </a>
            <a href="{{ route('jobs.queue', ['status' => 'duplicate']) }}" class="btn btn-sm px-3 rounded-pill border {{ request('status') === 'duplicate' ? 'btn-primary border-primary' : 'btn-outline-secondary border-secondary border-opacity-20 text-white' }} text-xs fw-semibold">
                Duplicate Skip
            </a>
        </div>
    </div>
</div>

<!-- Listings Cards -->
@if($jobs->isEmpty())
    <div class="glass-card text-center py-5">
        <i class="fa-solid fa-folder-open fs-1 text-muted mb-3 opacity-40"></i>
        <h4 class="fw-bold mb-2">No Listings Found</h4>
        <p class="text-muted mx-auto mb-0" style="max-width: 450px;">
            @if(request('status') === 'low_match')
                No jobs skipped for low relevancy score.
            @elseif(request('status') === 'expired')
                No jobs skipped for outdated postings.
            @elseif(request('status') === 'duplicate')
                No duplicate skips tracked.
            @else
                Queue is empty. Click "Crawl Jobs" to search for fresh matching postings.
            @endif
        </p>
    </div>
@else
    <div class="d-flex flex-column gap-3.5">
        @foreach($jobs as $job)
            <div class="glass-card p-3.5" style="background: rgba(16, 22, 47, 0.35);">
                <div class="row align-items-center g-3">
                    <!-- Title and Company -->
                    <div class="col-12 col-md-5">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4 d-none d-sm-flex align-items-center justify-content-center" style="width: 54px; height: 54px; border: 1px solid rgba(139, 92, 246, 0.15);">
                                <i class="fa-solid fa-code fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1 fs-6">
                                    <a href="{{ $job->job_url }}" target="_blank" class="text-white text-decoration-none hover-underline d-inline-flex align-items-center gap-1.5">
                                        {{ $job->title }} <i class="fa-solid fa-arrow-up-right-from-square text-muted" style="font-size: 0.75rem;"></i>
                                    </a>
                                </h5>
                                <p class="fw-bold mb-2 text-xs" style="color: #d946ef;">{{ $job->company }}</p>
                                <div class="d-flex flex-wrap gap-2 text-xxs text-muted fw-semibold">
                                    <span><i class="fa-solid fa-location-dot me-1"></i>{{ $job->location }}</span>
                                    <span>•</span>
                                    <span><i class="fa-solid fa-briefcase me-1"></i>{{ $job->experience_required }}</span>
                                    <span>•</span>
                                    <span><i class="fa-solid fa-money-bill-wave me-1"></i>{{ $job->salary ?: 'Not disclosed' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scores & Matching Breakdown -->
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <!-- Overall Score -->
                            <div class="text-center px-2 py-1 bg-dark bg-opacity-30 rounded-3 border border-secondary border-opacity-10" style="min-width: 76px;">
                                <div class="fw-extrabold fs-4 mb-0 {{ $job->match_score >= 70 ? 'text-success' : 'text-danger' }}" style="letter-spacing: -0.5px;">
                                    {{ $job->match_score }}%
                                </div>
                                <span class="text-muted" style="font-size: 0.65rem;">Match Score</span>
                            </div>
                            
                            <!-- Skill & Experience progress indicators -->
                            <div class="flex-grow-1">
                                <div class="mb-1 text-xxs text-muted d-flex justify-content-between">
                                    <span>Skills Match:</span>
                                    <span class="fw-bold text-white">{{ $job->skills_match_score }}%</span>
                                </div>
                                <div class="progress mb-2" style="height: 3px; background-color: rgba(255, 255, 255, 0.05);">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $job->skills_match_score }}%" aria-valuenow="{{ $job->skills_match_score }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                
                                <div class="mb-1 text-xxs text-muted d-flex justify-content-between">
                                    <span>Experience Fit:</span>
                                    <span class="fw-bold text-white">{{ $job->experience_match_score }}%</span>
                                </div>
                                <div class="progress" style="height: 3px; background-color: rgba(255, 255, 255, 0.05);">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $job->experience_match_score }}%" aria-valuenow="{{ $job->experience_match_score }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="col-12 col-md-3 text-md-end">
                        <span class="d-block text-muted text-xxs mb-2"><i class="fa-solid fa-clock-rotate-left me-1"></i>{{ $job->posted_date }}</span>
                        
                        @if($job->status === 'discovered' || $job->status === 'duplicate')
                            <form action="{{ route('jobs.apply', $job->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary-grad px-3.5 py-1.5 text-xs">
                                    <i class="fa-solid fa-paper-plane me-1"></i> Apply
                                </button>
                            </form>
                        @else
                            <button class="btn btn-xs btn-outline-danger px-3 py-1 rounded text-xxs" disabled>
                                <i class="fa-solid fa-ban me-1"></i> Filtered Out
                            </button>
                            @if($job->rejection_reason)
                                <div class="text-danger mt-1.5 text-xxs" style="font-size: 0.7rem;">{{ $job->rejection_reason }}</div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $jobs->appends(request()->input())->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection

@extends('layouts.app')

@section('content')
<div class="mb-5">
    <h1 class="fw-extrabold mb-1" style="letter-spacing: -1px;">Application History</h1>
    <p class="text-muted mb-0">Track and review the automated browser submission status for each matching job.</p>
</div>

<!-- Glass Filter Panel -->
<div class="glass-card mb-4 p-3.5">
    <form action="{{ route('jobs.history') }}" method="GET">
        <div class="row g-3">
            <div class="col-12 col-md-3">
                <label class="form-label text-muted text-xxs fw-bold uppercase">Job Title</label>
                <input type="text" name="title" class="form-control text-xs" placeholder="e.g. Laravel" value="{{ request('title') }}">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label text-muted text-xxs fw-bold uppercase">Company</label>
                <select name="company" class="form-select text-xs">
                    <option value="">All Companies</option>
                    @foreach($companies as $company)
                        <option value="{{ $company }}" {{ request('company') === $company ? 'selected' : '' }}>{{ $company }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label text-muted text-xxs fw-bold uppercase">Submission Date Range</label>
                <div class="input-group">
                    <input type="date" name="start_date" class="form-control text-xs" value="{{ request('start_date') }}">
                    <span class="input-group-text bg-dark border-secondary border-opacity-15 text-muted text-xs">to</span>
                    <input type="date" name="end_date" class="form-control text-xs" value="{{ request('end_date') }}">
                </div>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label text-muted text-xxs fw-bold uppercase">Status</label>
                <select name="status" class="form-select text-xs">
                    <option value="">All</option>
                    <option value="applied" {{ request('status') === 'applied' ? 'selected' : '' }}>Applied</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="col-12 text-end mt-4">
                <a href="{{ route('jobs.history') }}" class="btn btn-sm btn-outline-secondary me-2 text-xs fw-semibold px-3 py-2 rounded-3 border-secondary border-opacity-20 text-white">
                    <i class="fa-solid fa-eraser me-1"></i> Clear Filters
                </a>
                <button type="submit" class="btn btn-sm btn-primary-grad text-xs px-3.5 py-2 rounded-3">
                    <i class="fa-solid fa-filter me-1"></i> Search
                </button>
            </div>
        </div>
    </form>
</div>

<!-- History Datatable Card -->
@if($jobs->isEmpty())
    <div class="glass-card text-center py-5">
        <i class="fa-solid fa-folder-minus fs-1 text-muted mb-3 opacity-40"></i>
        <h4 class="fw-bold mb-2">No Submissions Logged</h4>
        <p class="text-muted mx-auto mb-0" style="max-width: 450px;">You haven't run any applications yet or no records match the filter query parameters.</p>
    </div>
@else
    <div class="glass-card p-0 overflow-hidden border border-secondary border-opacity-10">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0 text-xs text-muted" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255, 255, 255, 0.025);">
                <thead>
                    <tr class="border-bottom border-secondary border-opacity-20 text-white fw-bold">
                        <th class="py-3.5 ps-4">Submission Date</th>
                        <th class="py-3.5">Company</th>
                        <th class="py-3.5">Position Title</th>
                        <th class="py-3.5">AI Relevancy</th>
                        <th class="py-3.5">Application ID</th>
                        <th class="py-3.5">Status</th>
                        <th class="py-3.5 pe-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jobs as $job)
                        <tr class="border-bottom border-secondary border-opacity-10">
                            <!-- Date & Time -->
                            <td class="py-3.5 ps-4">
                                <div class="text-white fw-bold">{{ $job->applied_at ? $job->applied_at->format('Y-m-d') : 'N/A' }}</div>
                                <span class="text-xxs text-muted">{{ $job->applied_at ? $job->applied_at->format('H:i') : '' }}</span>
                            </td>
                            
                            <!-- Company -->
                            <td class="fw-extrabold text-white py-3.5">{{ $job->company }}</td>
                            
                            <!-- Position Title -->
                            <td class="py-3.5">
                                <div class="text-white fw-semibold">{{ $job->title }}</div>
                                <a href="{{ $job->job_url }}" target="_blank" class="text-muted text-xxs hover-underline d-inline-flex align-items-center gap-1 mt-1">
                                    Link <i class="fa-solid fa-up-right-from-square" style="font-size: 0.65rem;"></i>
                                </a>
                            </td>
                            
                            <!-- Match Score -->
                            <td class="py-3.5">
                                <span class="badge-match {{ $job->match_score < 70 ? 'badge-match-low' : '' }}" style="font-size: 0.75rem; padding: 0.35rem 0.75rem;">
                                    {{ $job->match_score }}%
                                </span>
                            </td>
                            
                            <!-- Application ID -->
                            <td class="py-3.5"><code>{{ $job->application_id ?: 'N/A' }}</code></td>
                            
                            <!-- Status Badge -->
                            <td class="py-3.5">
                                @if($job->status === 'applied')
                                    <span class="badge bg-success-subtle text-success border border-success border-opacity-20 px-2.5 py-1.5 rounded-3 text-xxs">
                                        <i class="fa-solid fa-circle-check me-1"></i> Success
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-20 px-2.5 py-1.5 rounded-3 text-xxs">
                                        <i class="fa-solid fa-circle-xmark me-1"></i> Failed
                                    </span>
                                @endif
                            </td>
                            
                            <!-- Actions/Errors -->
                            <td class="py-3.5 pe-4 text-center">
                                @if($job->rejection_reason)
                                    <button class="btn btn-xs btn-outline-danger p-2 border-0 bg-transparent text-danger" disabled data-bs-toggle="tooltip" title="{{ $job->rejection_reason }}">
                                        <i class="fa-solid fa-circle-exclamation fs-5"></i>
                                    </button>
                                @else
                                    <button class="btn btn-xs btn-outline-success p-2 border-0 bg-transparent text-success" disabled>
                                        <i class="fa-solid fa-check-double fs-5"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="mt-4">
        {{ $jobs->appends(request()->input())->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection

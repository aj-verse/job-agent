@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3">
    <div>
        <h1 class="fw-extrabold mb-1" style="letter-spacing: -1px;">Analytics Dashboard</h1>
        <p class="text-muted mb-0">Overview of your AI-powered job application actions and Naukri matches.</p>
    </div>
    <div class="d-flex gap-3">
        @if($resume)
            <form action="{{ route('jobs.discover') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-secondary-grad px-4 py-2.5">
                    <i class="fa-solid fa-arrows-rotate me-2"></i> Scan Jobs
                </button>
            </form>
            <form action="{{ route('jobs.autoApply') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary-grad px-4 py-2.5">
                    <i class="fa-solid fa-paper-plane me-2"></i> Run Auto-Apply
                </button>
            </form>
        @else
            <a href="{{ route('resume.index') }}" class="btn btn-primary-grad px-4 py-2.5">
                <i class="fa-solid fa-cloud-arrow-up me-2"></i> Setup Resume to Begin
            </a>
        @endif
    </div>
</div>

@if(!$resume)
    <div class="glass-card mb-5 border-warning border-opacity-25" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(15, 23, 42, 0.45) 100%);">
        <div class="d-flex align-items-center gap-4">
            <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-4 border border-warning border-opacity-20">
                <i class="fa-solid fa-triangle-exclamation fs-3"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-1 text-white">Resume Profile Missing</h5>
                <p class="mb-0 text-muted">To allow the AI engine to discover and match jobs, you need to upload a resume (PDF/DOCX). The model will automatically parse your credentials and build your automation profile.</p>
            </div>
        </div>
    </div>
@endif

<!-- Stats Cards Grid -->
<div class="row g-4 mb-5">
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="border-bottom: 3px solid #8b5cf6;">
            <span class="text-muted d-block mb-2 text-xs uppercase font-semibold">Total Discovered</span>
            <h2 class="fw-extrabold text-white mb-1">{{ $totalFound }}</h2>
            <span class="text-muted text-xs"><i class="fa-solid fa-layer-group text-primary me-1"></i> Jobs in queue</span>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="border-bottom: 3px solid #06b6d4;">
            <span class="text-muted d-block mb-2 text-xs uppercase font-semibold">Total Applied</span>
            <h2 class="fw-extrabold text-white mb-1">{{ $totalApplied }}</h2>
            <span class="text-muted text-xs"><i class="fa-solid fa-circle-check text-info me-1"></i> Submissions made</span>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="border-bottom: 3px solid #10b981;">
            <span class="text-muted d-block mb-2 text-xs uppercase font-semibold">Applied Today</span>
            <h2 class="fw-extrabold text-white mb-1">{{ $appliedToday }}</h2>
            <span class="text-muted text-xs"><i class="fa-solid fa-bolt text-success me-1"></i> Last 24 hours</span>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="border-bottom: 3px solid #f59e0b;">
            <span class="text-muted d-block mb-2 text-xs uppercase font-semibold">Success Rate</span>
            <h2 class="fw-extrabold text-white mb-1">{{ $successRate }}%</h2>
            <span class="text-muted text-xs"><i class="fa-solid fa-fire text-warning me-1"></i> Relevancy ratio</span>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Daily Trend -->
    <div class="col-12 col-xl-8">
        <div class="glass-card h-100">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-chart-line text-primary me-2"></i> Application Trends</h5>
            <div style="height: 320px;">
                <canvas id="dailyTrendChart"></canvas>
            </div>
        </div>
    </div>
    <!-- Match Score Distribution -->
    <div class="col-12 col-xl-4">
        <div class="glass-card h-100">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-chart-pie text-success me-2"></i> Match Score Distribution</h5>
            <div style="height: 320px; display: flex; justify-content: center; align-items: center;">
                <canvas id="scoreDistChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Top Companies -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="glass-card">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-building text-info me-2"></i> Targeted Employers</h5>
            <div style="height: 250px;">
                <canvas id="companyChart"></canvas>
            </div>
        </div>
    </div>
    <!-- Top Locations -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="glass-card">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-location-dot text-danger me-2"></i> Location Density</h5>
            <div style="height: 250px;">
                <canvas id="locationChart"></canvas>
            </div>
        </div>
    </div>
    <!-- Rejection Reasons -->
    <div class="col-12 col-lg-4">
        <div class="glass-card">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-filter text-warning me-2"></i> Filtering & Skips</h5>
            <div style="height: 250px;">
                <canvas id="rejectionChart"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Set global chart options for dark mode
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.04)';
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";

    // 1. Daily Trend Chart
    const dailyCtx = document.getElementById('dailyTrendChart').getContext('2d');
    const purpleGrad = dailyCtx.createLinearGradient(0, 0, 0, 300);
    purpleGrad.addColorStop(0, 'rgba(139, 92, 246, 0.35)');
    purpleGrad.addColorStop(1, 'rgba(139, 92, 246, 0.0)');

    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_keys($dailyTrendData)) !!},
            datasets: [{
                label: 'Applications Run',
                data: {!! json_encode(array_values($dailyTrendData)) !!},
                borderColor: '#8b5cf6',
                backgroundColor: purpleGrad,
                borderWidth: 3,
                fill: true,
                pointBackgroundColor: '#8b5cf6',
                pointBorderColor: '#ffffff',
                pointHoverRadius: 6,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // 2. Match Score Distribution Chart
    const scoreCtx = document.getElementById('scoreDistChart').getContext('2d');
    new Chart(scoreCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($scoreDistribution)) !!},
            datasets: [{
                data: {!! json_encode(array_values($scoreDistribution)) !!},
                backgroundColor: ['#10b981', '#8b5cf6', '#f59e0b', '#ef4444'],
                borderWidth: 2,
                borderColor: '#0b0f19'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, padding: 15 }
                }
            }
        }
    });

    // 3. Company Chart
    const companyCtx = document.getElementById('companyChart').getContext('2d');
    new Chart(companyCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($companyApplications->pluck('company')) !!},
            datasets: [{
                data: {!! json_encode($companyApplications->pluck('count')) !!},
                backgroundColor: '#f57c00',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
                x: { grid: { display: false } }
            }
        }
    });

    // 4. Location Chart
    const locationCtx = document.getElementById('locationChart').getContext('2d');
    new Chart(locationCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($locationApplications->pluck('location')) !!},
            datasets: [{
                data: {!! json_encode($locationApplications->pluck('count')) !!},
                backgroundColor: '#ef4444',
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0 } },
                y: { grid: { display: false } }
            }
        }
    });

    // 5. Rejection Reason Chart
    const rejectionCtx = document.getElementById('rejectionChart').getContext('2d');
    const statusLabels = {!! json_encode($rejectionReasons->pluck('status')) !!};
    const mappedLabels = statusLabels.map(lbl => {
        if (lbl === 'low_match') return 'Low Score';
        if (lbl === 'duplicate') return 'Duplicate';
        if (lbl === 'expired') return 'Outdated';
        if (lbl === 'failed') return 'Failed';
        return lbl;
    });

    new Chart(rejectionCtx, {
        type: 'polarArea',
        data: {
            labels: mappedLabels,
            datasets: [{
                data: {!! json_encode($rejectionReasons->pluck('count')) !!},
                backgroundColor: ['rgba(245, 158, 11, 0.65)', 'rgba(59, 130, 246, 0.65)', 'rgba(239, 68, 68, 0.65)', 'rgba(16, 185, 129, 0.65)'],
                borderColor: '#0b0f19',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    grid: { color: 'rgba(255, 255, 255, 0.04)' },
                    angleLines: { color: 'rgba(255, 255, 255, 0.04)' },
                    ticks: { display: false }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, padding: 10 }
                }
            }
        }
    });
</script>
@endsection

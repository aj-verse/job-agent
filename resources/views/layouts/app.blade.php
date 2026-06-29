<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Job Agent') }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom Styling (Futuristic Glassmorphism) -->
    <style>
        :root {
            --bg-color: #060813;
            --sidebar-bg: rgba(10, 14, 35, 0.6);
            --card-bg: rgba(16, 22, 47, 0.45);
            --border-color: rgba(255, 255, 255, 0.05);
            --border-glow: rgba(99, 102, 241, 0.15);
            --primary-accent: #8b5cf6;
            --primary-gradient: linear-gradient(135deg, #8b5cf6 0%, #d946ef 100%);
            --secondary-gradient: linear-gradient(135deg, #f57c00 0%, #ffb74d 100%);
            --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Outfit', sans-serif;
            background: radial-gradient(circle at 50% 50%, #0f132a 0%, #050714 100%);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Navigation */
        .sidebar {
            background-color: var(--sidebar-bg);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--border-color);
            min-height: 100vh;
            position: fixed;
            width: 280px;
            z-index: 100;
            padding: 2rem 1.5rem;
            box-shadow: 10px 0 30px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            font-weight: 800;
            font-size: 1.6rem;
            letter-spacing: -0.5px;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 3rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sidebar-brand i {
            font-size: 1.8rem;
            filter: drop-shadow(0 2px 10px rgba(139, 92, 246, 0.4));
        }

        .nav-link {
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.9rem 1.2rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 0.6rem;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
        }

        .nav-link i {
            font-size: 1.1rem;
            transition: transform 0.25s ease;
        }

        .nav-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transform: translateX(4px);
        }
        
        .nav-link:hover i {
            transform: scale(1.1);
        }

        .nav-link.active {
            color: #ffffff;
            background: var(--primary-gradient);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.35);
        }
        
        .nav-link.active:hover {
            transform: none;
        }

        .nav-link.text-danger-hover:hover {
            color: #ef4444 !important;
            background-color: rgba(239, 68, 68, 0.05) !important;
            border-color: rgba(239, 68, 68, 0.1) !important;
        }

        /* Main Content Wrapper */
        .main-content {
            margin-left: 280px;
            padding: 3rem;
            min-height: 100vh;
        }

        /* Modern Glass Cards */
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            border-color: rgba(139, 92, 246, 0.25);
            box-shadow: 0 15px 45px rgba(139, 92, 246, 0.08);
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(22, 29, 60, 0.6) 0%, rgba(14, 18, 43, 0.6) 100%);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 1.8rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.15);
        }

        .stat-card::after {
            content: '';
            position: absolute;
            bottom: -20px;
            right: -20px;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.2) 0%, transparent 70%);
            border-radius: 50%;
        }

        /* Gradient & Neon Buttons */
        .btn-primary-grad {
            background: var(--primary-gradient);
            border: none;
            color: white;
            font-weight: 700;
            letter-spacing: 0.2px;
            border-radius: 12px;
            padding: 0.8rem 1.8rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.3);
        }

        .btn-primary-grad:hover {
            background: linear-gradient(135deg, #d946ef 0%, #8b5cf6 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 25px rgba(217, 70, 239, 0.5);
        }

        .btn-secondary-grad {
            background: var(--secondary-gradient);
            border: none;
            color: white;
            font-weight: 700;
            border-radius: 12px;
            padding: 0.8rem 1.8rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(6, 182, 212, 0.25);
        }

        .btn-secondary-grad:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 25px rgba(6, 182, 212, 0.45);
        }

        /* Form Inputs Redesign */
        .form-control, .form-select {
            background-color: rgba(11, 15, 33, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--text-main);
            border-radius: 12px;
            padding: 0.75rem 1.1rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            background-color: rgba(11, 15, 33, 0.85);
            color: #ffffff;
            border-color: var(--primary-accent);
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.2);
        }

        .form-label, label.form-label {
            color: #cbd5e1 !important;
            font-weight: 700;
            font-size: 0.75rem !important;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: inline-block;
            margin-bottom: 0.5rem;
        }

        .form-text, .text-muted {
            color: #94a3b8 !important;
        }

        .text-gradient {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Badges & Scores */
        .badge-match {
            background: rgba(16, 185, 129, 0.12);
            color: #10b981;
            font-weight: 700;
            font-size: 0.85rem;
            border: 1px solid rgba(16, 185, 129, 0.25);
            padding: 0.5rem 1rem;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.05);
        }

        .badge-match-low {
            background: rgba(239, 68, 68, 0.12);
            color: #ef4444;
            font-weight: 700;
            font-size: 0.85rem;
            border: 1px solid rgba(239, 68, 68, 0.25);
            padding: 0.5rem 1rem;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Beautiful Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg-color);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 5px;
            border: 2px solid var(--bg-color);
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        /* Mobile Viewports */
        @media (max-width: 991px) {
            .sidebar {
                width: 80px;
                padding: 1.5rem 0.5rem;
            }
            .sidebar-brand span, .nav-link span {
                display: none;
            }
            .sidebar-brand {
                justify-content: center;
                margin-bottom: 2.5rem;
            }
            .nav-link {
                justify-content: center;
                padding: 1rem;
            }
            .main-content {
                margin-left: 80px;
                padding: 2rem 1.5rem;
            }
            .user-details {
                display: none !important;
            }
            .user-card-divider {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    @auth
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-robot"></i>
            <span>Job Agent</span>
        </div>
        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link {{ Request::routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
            <a class="nav-link {{ Request::routeIs('resume.*') ? 'active' : '' }}" href="{{ route('resume.index') }}">
                <i class="fa-solid fa-file-invoice"></i>
                <span>Resume Profile</span>
            </a>
            <a class="nav-link {{ Request::routeIs('jobs.queue') ? 'active' : '' }}" href="{{ route('jobs.queue') }}">
                <i class="fa-solid fa-layer-group"></i>
                <span>Discovery Queue</span>
            </a>
            <a class="nav-link {{ Request::routeIs('jobs.history') ? 'active' : '' }}" href="{{ route('jobs.history') }}">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Applied History</span>
            </a>
            <a class="nav-link {{ Request::routeIs('recommendations.*') ? 'active' : '' }}" href="{{ route('recommendations.index') }}">
                <i class="fa-solid fa-lightbulb"></i>
                <span>AI Recommendations</span>
            </a>
            <a class="nav-link {{ Request::routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                <i class="fa-solid fa-sliders"></i>
                <span>Settings</span>
            </a>

            <div class="mt-auto pt-3 border-top user-card-divider" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                <div class="d-flex align-items-center gap-2 px-2 py-1 mb-2 user-details">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 32px; height: 32px; background: var(--primary-gradient); font-size: 0.85rem; flex-shrink: 0;">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-white fw-bold text-truncate" style="font-size: 0.85rem;" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</div>
                        <div class="text-muted text-truncate" style="font-size: 0.7rem;" title="{{ auth()->user()->email }}">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <a class="nav-link text-danger-hover" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="padding: 0.6rem 1rem; font-size: 0.85rem; margin-bottom: 0;">
                    <i class="fa-solid fa-right-from-bracket" style="color: #ef4444;"></i>
                    <span>Logout</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </nav>
    </div>
    @endauth

    <!-- Main Content -->
    <div class="main-content" style="@guest margin-left: 0; padding: 2rem; @endguest">
        <!-- Toast Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-lg text-white mb-4" style="background: var(--success-gradient); border-radius: 14px;" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-circle-check fs-5 me-3"></i> 
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-lg text-white mb-4" style="background: var(--danger-gradient); border-radius: 14px;" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-circle-exclamation fs-5 me-3"></i> 
                    <div>{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-lg text-dark mb-4" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 14px;" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-triangle-exclamation fs-5 me-3"></i> 
                    <div class="fw-bold">{{ session('warning') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>

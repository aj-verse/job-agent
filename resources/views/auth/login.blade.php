@extends('layouts.app')

@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height: 80vh;">
    <div class="glass-card shadow-lg p-5" style="width: 100%; max-width: 480px; border-radius: 24px; position: relative;">
        <!-- Glowing background effect -->
        <div style="position: absolute; top: -50px; left: -50px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(139, 92, 246, 0.2) 0%, transparent 70%); border-radius: 50%; z-index: -1;"></div>
        <div style="position: absolute; bottom: -50px; right: -50px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(217, 70, 239, 0.15) 0%, transparent 70%); border-radius: 50%; z-index: -1;"></div>

        <div class="text-center mb-5">
            <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-3" style="width: 60px; height: 60px; background: var(--primary-gradient); box-shadow: 0 8px 25px rgba(139, 92, 246, 0.35);">
                <i class="fa-solid fa-robot text-white fs-3" style="filter: drop-shadow(0 2px 5px rgba(0,0,0,0.25));"></i>
            </div>
            <h2 class="fw-bold mb-1" style="letter-spacing: -0.5px;">Welcome Back</h2>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Sign in to manage your automated job applications</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div class="mb-4">
                <label for="email" class="form-label text-white fw-bold">Email Address</label>
                <div class="position-relative">
                    <i class="fa-regular fa-envelope position-absolute text-muted" style="left: 16px; top: 50%; transform: translateY(-50%); font-size: 1rem;"></i>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="name@example.com" style="padding-left: 45px;">
                </div>
                @error('email')
                    <span class="invalid-feedback d-block mt-2" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="form-label text-white fw-bold">Password</label>
                <div class="position-relative">
                    <i class="fa-solid fa-lock position-absolute text-muted" style="left: 16px; top: 50%; transform: translateY(-50%); font-size: 1rem;"></i>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••" style="padding-left: 45px;">
                </div>
                @error('password')
                    <span class="invalid-feedback d-block mt-2" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="form-check mb-4 d-flex align-items-center gap-2">
                <input class="form-check-input mt-0" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} style="background-color: rgba(11, 15, 33, 0.65); border-color: rgba(255, 255, 255, 0.1);">
                <label class="form-check-label text-muted" for="remember" style="font-size: 0.85rem; user-select: none;">
                    Remember me on this device
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary-grad w-100 py-3 mb-4">
                Sign In <i class="fa-solid fa-arrow-right-to-bracket ms-2"></i>
            </button>

            <!-- Register Redirect -->
            <div class="text-center">
                <p class="text-muted mb-0" style="font-size: 0.85rem;">
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="text-gradient fw-bold text-decoration-none ms-1">Sign Up</a>
                </p>
            </div>
        </form>
    </div>
</div>
@endsection

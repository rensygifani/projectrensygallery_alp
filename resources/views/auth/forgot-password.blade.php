@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-center align-items-center min-vh-100">
        <div class="card pastel-form-card shadow-sm p-4" style="max-width:420px; width:100%;">

            <div class="text-center mb-3">
                <div class="fs-1">🔐</div>
                <h4 class="fw-bold pastel-section-title mt-2">
                    Lupa Password
                </h4>
                <p class="text-muted small mt-2">
                    Masukkan email yang terdaftar, kami akan mengirimkan link
                    untuk mengatur ulang password Anda.
                </p>
            </div>

            {{-- SESSION STATUS --}}
            <x-auth-session-status class="mb-3" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                {{-- EMAIL --}}
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">
                        Email
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="form-control pastel-input"
                        placeholder="example@email.com"
                    >
                    <x-input-error :messages="$errors->get('email')" class="mt-1 text-danger small" />
                </div>

                {{-- BUTTON --}}
                <div class="d-grid">
                    <button class="btn pastel-btn">
                        Kirim Link Reset Password
                    </button>
                </div>
            </form>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="pastel-link small">
                    ← Kembali ke Login
                </a>
            </div>

        </div>
    </div>

@endsection



{{-- <x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout> --}}

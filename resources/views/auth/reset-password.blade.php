@extends('layouts.app')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-5">

        <div class="card pastel-form-card shadow-sm p-4 mt-4">

            <div class="text-center mb-3">
                <div class="fs-2 mb-1">🔐</div>
                <h4 class="fw-bold">Reset Password</h4>
                <p class="text-muted small">
                    Silakan masukkan password baru untuk akun Anda
                </p>
            </div>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                {{-- TOKEN --}}
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                {{-- EMAIL --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email"
                           name="email"
                           class="form-control pastel-input @error('email') is-invalid @enderror"
                           value="{{ old('email', $request->email) }}"
                           required autofocus>

                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- PASSWORD --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password Baru</label>
                    <input type="password"
                           name="password"
                           class="form-control pastel-input @error('password') is-invalid @enderror"
                           required>

                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- CONFIRM PASSWORD --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Konfirmasi Password</label>
                    <input type="password"
                           name="password_confirmation"
                           class="form-control pastel-input"
                           required>
                </div>

                {{-- SUBMIT --}}
                <button class="btn pastel-btn w-100">
                    Reset Password
                </button>
            </form>

        </div>

    </div>
</div>

@endsection



{{-- <x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Reset Password') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout> --}}

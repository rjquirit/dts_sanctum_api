@extends('layouts.app')

@section('title', 'Login')

@section('styles')
    <!-- App css -->
    <link href="assets/css/style.min.css" rel="stylesheet" type="text/css" />
    <!-- Theme Config js -->
    <script src="assets/js/config.js"></script>
@endsection

@section('content')
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card border-0 shadow-lg mt-2">
                        <div class="card-body p-4 mt-3">
                            @if(session('error'))
                                <div class="alert alert-danger" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif
                            <div class="text-center">
                                <div class="mx-auto mb-4 text-center auth-logo">
                                    <a href="{{ route('login') }}" class="logo-dark">
                                        <img src="images/logo.png" height="100" alt="deped logo">
                                    </a>
                                </div>
                                <h4 class="fw-bold text-dark mb-2">Welcome Back!</h3>
                                    <p class="text-muted">Sign in to your account to continue</p>
                            </div>
                            <form id="loginForm" action="" class="mt-4">
                                @csrf
                                <div class="mb-3">
                                    <label for="email" class="form-label">{{ __('Email Address') }}</label>
                                    <input type="email" class="form-control" id="email" autocomplete="email" autofocus placeholder="Enter your email">
                                    <div class="invalid-feedback" id="emailError"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">{{ __('Password') }}</label>
                                    <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" placeholder="Enter your password">
                                     <div class="invalid-feedback" id="passwordError"></div>
                                </div>
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="remember">
                                    <label class="form-check-label" for="remember">{{ __('Remember Me') }}</label>
                                </div>

                                <div class="alert alert-danger d-none" id="loginError"></div>

                                <div class="d-grid">
                                    <button class="btn btn-dark btn-lg fw-medium" type="submit" id="loginButton">{{ __('Login') }}</button>
                                </div>

                                <!-- Divider -->
                                <div class="text-center mb-2">
                                    <hr>
                                    <span class="text-muted">or sign in with email</span>
                                    
                                </div>

                                 <!-- Google Sign-In Button -->
                                <div class="d-grid gap-2">
                                    <button id="GoogleLoginButton"  class="btn btn-primary btn-lg">
                                        <i class="fab fa-google"></i>
                                        <!-- <svg width="18" height="18" viewBox="0 0 18 18" class="me-2">
                                            <path fill="#4285F4" d="M16.51 8H8.98v3h4.3c-.18 1-.74 1.48-1.6 2.04v2.01h2.6a7.8 7.8 0 0 0 2.38-5.88c0-.57-.05-.66-.15-1.18z"/>
                                            <path fill="#34A853" d="M8.98 17c2.16 0 3.97-.72 5.3-1.94l-2.6-2a4.8 4.8 0 0 1-7.18-2.54H1.83v2.07A8 8 0 0 0 8.98 17z"/>
                                            <path fill="#FBBC05" d="M4.5 10.52a4.8 4.8 0 0 1 0-3.04V5.41H1.83a8 8 0 0 0 0 7.18l2.67-2.07z"/>
                                            <path fill="#EA4335" d="M8.98 4.18c1.17 0 2.23.4 3.06 1.2l2.3-2.3A8 8 0 0 0 1.83 5.41L4.5 7.49a4.77 4.77 0 0 1 4.48-3.31z"/>
                                        </svg> -->
                                        {{ __('Continue with Google') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <p class="text-center mt-3 text-white text-opacity-50">Don't have an account?
                        <a href="{{ route('register') }}" class="text-decoration-none text-white fw-bold">Sign Up</a>
                    </p>
                </div>
            </div>
        </div>
@endsection


@push('scripts')
    <script type="module">
    import { bindLogin } from '/js/modules/auth.js';
    document.addEventListener('DOMContentLoaded', () => {
        bindLogin('#loginForm');
    });
    </script>
@endpush
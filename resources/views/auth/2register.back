@extends('layouts.app')

@section('title', 'Register')

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
                    <div class="card border-0 shadow-lg">
                        <div class="card-body p-4">
                            <div class="text-center">
                                <div class="mx-auto mb-4 text-center auth-logo">
                                    <a href="{{ route('register') }}" class="logo-dark">
                                        <img src="images/logo.png" height="100" alt="deped logo">
                                    </a>
                                </div>
                                <h4 class="fw-bold text-dark mb-2">Sign Up</h3>
                                        <p class="text-muted">New to our platform? Sign up now! It only takes a
                                            minute.
                                        </p>
                            </div>

                            <form id="registerForm" class="mt-4">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label" for="name">{{ __('Name') }}</label>
                                    <input id="name" type="text" name="name" class="form-control" required autocomplete="name" autofocus placeholder="Enter your name">
                                    <div class="invalid-feedback" id="nameError"></div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="email">{{ __('Email Address') }}</label>
                                    <input type="email" id="email" name="email" class="form-control" required autocomplete="email" placeholder="Enter your email">
                                    <div class="invalid-feedback" id="emailError"></div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="password">{{ __('Password') }}</label>
                                    <input type="password" id="password" class="form-control" name="password" required minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" placeholder="Enter your password">
                                    <div class="invalid-feedback" id="passwordError"></div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="password_confirmation">{{ __('Confirm Password') }}</label>
                                    <input type="password" id="password_confirmation" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Enter confirm password">
                                    <div class="invalid-feedback" id="password_confirmationError"></div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="checkbox-signin">
                                        <label class="form-check-label" for="checkbox-signin">I accept Terms and Condition</label>
                                    </div>
                                </div>

                                <div class="alert alert-danger d-none" id="registerError"></div>

                                <div class="mb-1 text-center d-grid">
                                    <button class="btn btn-dark btn-lg fw-medium" type="submit" id="registerButton">{{ __('Register') }}</button>
                                </div>

                            </form>
                        </div>
                    </div>
                    <p class="text-center mt-4 text-white text-opacity-50">I already have an account
                        <a href="{{ route('login') }}" class="text-decoration-none text-white fw-bold">Sign In</a>
                    </p>
            </div>
        </div>
    </div>
@endsection

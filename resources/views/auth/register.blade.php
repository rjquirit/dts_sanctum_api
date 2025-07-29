@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">

                        <form id="registerForm" class="row">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">{{ __('Name') }}</label>
                                <input id="name" type="text" class="form-control" name="name" required autocomplete="name" autofocus>
                                <div class="invalid-feedback" id="nameError"></div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">{{ __('Email Address') }}</label>
                                <input id="email" type="email" class="form-control" name="email" required autocomplete="email">
                                <div class="invalid-feedback" id="emailError"></div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">{{ __('Password') }}</label>
                                <input id="password" type="password" class="form-control" name="password" 
                                required minlength="8" 
                                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}">
                                <div class="invalid-feedback" id="passwordError"></div>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                                <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                <div class="invalid-feedback" id="password_confirmationError"></div>
                            </div>


                            <div class="alert alert-danger d-none" id="registerError"></div>

                        <div class="mb-0">
                            <button type="submit" class="btn btn-primary" id="registerButton">
                                {{ __('Register') }}
                            </button>
                            <div class="card-action center-align">
                                <a href="{{ route('login') }}" class="blue-text">Already have an account? Login</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

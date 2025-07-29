@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                        <form id="loginForm" class="row">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">{{ __('Email Address') }}</label>
                                <input id="email" type="email" class="form-control" name="email" required autocomplete="email" autofocus>
                                <div class="invalid-feedback" id="emailError"></div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">{{ __('Password') }}</label>
                                <input id="password" type="password" class="form-control" name="password" required autocomplete="current-password">
                                <div class="invalid-feedback" id="passwordError"></div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>

                            <div class="alert alert-danger d-none" id="loginError"></div>

                            <div class="mb-0">
                                <button type="submit" class="btn btn-primary w-100" id="loginButton">
                                    {{ __('Login') }}
                                </button>
                                <div class="text-center mt-3">
                                    <a href="{{ route('register') }}" class="link-primary">Don't have an account? Sign up</a>
                                </div>
                            </div>
                        </form>
                    </div>
                 </div>
            </div>
        </div>
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
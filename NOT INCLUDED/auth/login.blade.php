@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                    <form id="loginForm" onsubmit="handleLogin(event)">
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
                            <button type="submit" class="btn btn-primary" id="loginButton">
                                {{ __('Login') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
async function handleLogin(event) {
    event.preventDefault();
    
    const form = event.target;
    const button = form.querySelector('#loginButton');
    const errorDiv = document.getElementById('loginError');
    
    // Reset errors
    errorDiv.classList.add('d-none');
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    // Get form data
    const formData = {
        email: form.email.value,
        password: form.password.value,
    };
    
    try {
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
        
        const response = await api.request('/login', {
            method: 'POST',
            body: JSON.stringify(formData)
        });
        
        api.setToken(response.token);
        window.location.href = '/dashboard';
    } catch (error) {
        if (error.response?.status === 422) {
            // Validation errors
            const errors = error.response.data.errors;
            Object.keys(errors).forEach(field => {
                const input = form.querySelector(`#${field}`);
                const feedback = form.querySelector(`#${field}Error`);
                if (input && feedback) {
                    input.classList.add('is-invalid');
                    feedback.textContent = errors[field][0];
                }
            });
        } else {
            // General error
            errorDiv.textContent = error.message;
            errorDiv.classList.remove('d-none');
        }
    } finally {
        button.disabled = false;
        button.textContent = 'Login';
    }
}
</script>
@endsection

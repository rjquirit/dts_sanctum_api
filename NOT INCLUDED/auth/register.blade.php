@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form id="registerForm" onsubmit="handleRegister(event)">
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
async function handleRegister(event) {
    event.preventDefault();
    
    const form = event.target;
    const button = form.querySelector('#registerButton');
    const errorDiv = document.getElementById('registerError');
    
    // Reset errors
    errorDiv.classList.add('d-none');
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    // Get form data
    const formData = {
        name: form.name.value,
        email: form.email.value,
        password: form.password.value,
        password_confirmation: form.password_confirmation.value
    };
    
    try {
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
        
        const response = await api.request('/register', {
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
        button.textContent = 'Register';
    }
}
</script>
@endsection

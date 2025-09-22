@extends('layouts.user')

@section('title', 'Profile')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <h1 class="h3 fw-bold mb-4" style="color: var(--text-dark);">My Profile</h1>
            
            <!-- Alert Messages -->
            <div id="alert-container" class="mb-4"></div>
            
            <div class="row g-4">
                <!-- Profile Information Card -->
                <div class="col-12 col-lg-4">
                    <div class="card h-100">
                    <div class="text-center">
                        <!-- Avatar -->
                        <div class="relative inline-block mb-4">
                            @if(Auth::user()->avatar)
                                <img src="{{ Auth::user()->avatar ?? asset('images/default-avatar.png') }}" 
                                     alt="Profile Avatar" 
                                     class="w-24 h-24 rounded-full object-cover border-4 border-gray-200 shadow-sm">
                            @else
                                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center border-4 border-gray-200 shadow-sm">
                                    <span class="text-2xl font-bold text-white">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                </div>
                            @endif
                            <!-- Online status indicator -->
                            <div class="absolute bottom-0 right-0 w-6 h-6 bg-green-500 border-2 border-white rounded-full"></div>
                        </div>
                        
                        <!-- User Name -->
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ Auth::user()->name }}</h2>
                        
                        <!-- Position -->
                        <p class="text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full inline-block mb-4">
                            {{ Auth::user()->position ?? 'No Position' }}
                        </p>
                    </div>
                        
                        <!-- User Details -->
                        <div class="border-top pt-4 mt-4">
                            <div class="d-flex align-items-start mb-3">
                                <i class="bi bi-envelope text-muted me-3 mt-1" style="font-size: 1.1rem;"></i>
                                <div class="text-start">
                                    <p class="small text-muted mb-1">Email</p>
                                    <p class="fw-medium mb-0" style="color: var(--text-dark); font-size: 0.9rem;">{{ Auth::user()->email }}</p>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-start mb-3">
                                <i class="bi bi-building text-muted me-3 mt-1" style="font-size: 1.1rem;"></i>
                                <div class="text-start">
                                    <p class="small text-muted mb-1">Department</p>
                                    <p class="fw-medium mb-0" style="color: var(--text-dark); font-size: 0.9rem;">
                                        @php
                                            $section = \App\Models\Sections::select('section_description')
                                                       ->where('section_id', Auth::user()->section_id)
                                                       ->first();
                                        @endphp
                                        {{ $section->section_description ?? 'No Section Assigned' }}
                                        {{ Auth::user()->section_name ?? '' }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-start mb-3">
                                <i class="bi bi-person-badge text-muted me-3 mt-1" style="font-size: 1.1rem;"></i>
                                <!-- <div class="text-start">
                                    <p class="small text-muted mb-1">User ID</p>
                                    <p class="fw-medium mb-0" style="color: var(--text-dark); font-size: 0.9rem;">#{{ Auth::user()->id }}</p>
                                </div> -->
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <!-- <div class="border-top pt-4 mt-4">
                            <button class="btn btn-outline-secondary w-100 mb-3" style="border-radius: 8px;">
                                <i class="bi bi-pencil me-2"></i>Edit Profile
                            </button>
                            <button class="btn btn-link text-muted p-0" style="font-size: 0.85rem;">
                                <i class="bi bi-clock-history me-1"></i>View Activity Log
                            </button>
                        </div> -->
                    </div>
                </div>
                
                <!-- Change Password Section -->
                <div class="col-12 col-lg-8">
                    <div class="card h-100">
                        <div class="card-header d-flex align-items-center" 
                             style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: none;">
                            <i class="bi bi-shield-lock-fill me-2 text-white" style="font-size: 1.2rem;"></i>
                            <h5 class="h5 mb-0 text-white">Change Password</h5>
                        </div>
                        
                        <div class="card-body">
                            <!-- Password Security Info -->
                            <div class="alert d-flex align-items-start mb-4" 
                                 style="background: var(--background-secondary); border: 2px solid var(--border-color); border-radius: 10px;">
                                <i class="bi bi-info-circle-fill me-3 mt-1" style="color: var(--primary-color); font-size: 1.1rem;"></i>
                                <div>
                                    <strong style="color: var(--text-dark);">Password Requirements:</strong>
                                    <span style="color: var(--text-dark);">Use at least 8 characters with a mix of uppercase and lowercaseletters, numbers, and symbols for better security.</span>
                                </div>
                            </div>
                            
                            <form id="password-form">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-12">
                                        <label for="current_password" class="form-label fw-medium" style="color: var(--text-dark);">
                                            Current Password
                                        </label>
                                        <div class="position-relative">
                                            <input type="password" 
                                                   id="current_password" 
                                                   name="current_password" 
                                                   class="form-control"
                                                   placeholder="Enter your current password" 
                                                   required>
                                            <button type="button" 
                                                    class="btn position-absolute end-0 top-50 translate-middle-y border-0 text-muted" 
                                                    onclick="togglePassword('current_password')"
                                                    style="z-index: 5;">
                                                <i class="bi bi-eye" id="current_password-toggle"></i>
                                            </button>
                                        </div>
                                        <div class="invalid-feedback" id="current_password-error"></div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="password" class="form-label fw-medium" style="color: var(--text-dark);">
                                            New Password
                                        </label>
                                        <div class="position-relative">
                                            <input type="password" 
                                                   id="password" 
                                                   name="password" 
                                                   class="form-control"
                                                   placeholder="Enter new password" 
                                                   required>
                                            <button type="button" 
                                                    class="btn position-absolute end-0 top-50 translate-middle-y border-0 text-muted" 
                                                    onclick="togglePassword('password')"
                                                    style="z-index: 5;">
                                                <i class="bi bi-eye" id="password-toggle"></i>
                                            </button>
                                        </div>
                                        <!-- Password Strength Indicator -->
                                        <div class="mt-2">
                                            <div class="progress" style="height: 6px;">
                                                <div id="password-strength" 
                                                     class="progress-bar" 
                                                     role="progressbar" 
                                                     style="width: 0%; transition: all 0.3s ease;">
                                                </div>
                                            </div>
                                            <small id="password-strength-text" class="text-muted">Password strength will appear here</small>
                                        </div>
                                        <div class="invalid-feedback" id="password-error"></div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="password_confirmation" class="form-label fw-medium" style="color: var(--text-dark);">
                                            Confirm New Password
                                        </label>
                                        <div class="position-relative">
                                            <input type="password" 
                                                   id="password_confirmation" 
                                                   name="password_confirmation" 
                                                   class="form-control"
                                                   placeholder="Confirm new password" 
                                                   required>
                                            <button type="button" 
                                                    class="btn position-absolute end-0 top-50 translate-middle-y border-0 text-muted" 
                                                    onclick="togglePassword('password_confirmation')"
                                                    style="z-index: 5;">
                                                <i class="bi bi-eye" id="password_confirmation-toggle"></i>
                                            </button>
                                        </div>
                                        <div class="invalid-feedback" id="password_confirmation-error"></div>
                                    </div>
                                    
                                    <div class="col-12 pt-3">
                                        <div class="d-flex flex-column flex-sm-row gap-3">
                                            <button type="button" class="btn btn-outline-secondary flex-fill">
                                                <i class="bi bi-x-circle me-2"></i>Cancel
                                            </button>
                                            <button type="submit" id="change-password-btn" class="btn btn-primary flex-fill" disabled>
                                                <span class="btn-text">
                                                    <i class="bi bi-shield-check me-2"></i>Change Password
                                                </span>
                                                <span class="loading-spinner d-none">
                                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                                    Processing...
                                                </span>
                                            </button>
                                        </div>
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


<script>
// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
    field.setAttribute('type', type);
}

// Password strength checker
function checkPasswordStrength(password) {
    let strength = 0;
    let feedback = '';
    
    // Length check
    if (password.length >= 8) strength += 25;
    else return { strength: 0, feedback: 'Too short (minimum 8 characters)' };
    
    // Character variety checks
    if (/[a-z]/.test(password)) strength += 25;
    if (/[A-Z]/.test(password)) strength += 25;
    if (/[0-9]/.test(password)) strength += 15;
    if (/[^A-Za-z0-9]/.test(password)) strength += 10;
    
    // Determine feedback
    if (strength < 25) feedback = 'Very weak';
    else if (strength < 50) feedback = 'Weak';
    else if (strength < 75) feedback = 'Good';
    else feedback = 'Strong';
    
    return { strength, feedback };
}

class PasswordManager {
    constructor() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                        document.querySelector('input[name="_token"]')?.value;
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // Password form submission
        document.getElementById('password-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.changePassword();
        });

        // Real-time password confirmation validation
        document.getElementById('password_confirmation').addEventListener('input', () => {
            this.validatePasswordMatch();
        });

        // Password strength indicator
        document.getElementById('password').addEventListener('input', (e) => {
            const result = checkPasswordStrength(e.target.value);
            const strengthBar = document.getElementById('password-strength');
            const strengthText = document.getElementById('password-strength-text');
            
            strengthBar.style.width = result.strength + '%';
            strengthText.textContent = result.feedback;
            
            // Color based on strength
            if (result.strength < 25) {
                strengthBar.className = 'bg-red-500 h-2 rounded-full transition-all duration-300';
            } else if (result.strength < 50) {
                strengthBar.className = 'bg-yellow-500 h-2 rounded-full transition-all duration-300';
            } else if (result.strength < 75) {
                strengthBar.className = 'bg-blue-500 h-2 rounded-full transition-all duration-300';
            } else {
                strengthBar.className = 'bg-green-500 h-2 rounded-full transition-all duration-300';
            }
        });
    }

    async changePassword() {
        const btn = document.getElementById('change-password-btn');
        const form = document.getElementById('password-form');
        
        this.toggleLoading(btn, true);
        this.clearErrors();

        try {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            const response = await this.makeRequest('/api/profile/password', {
                method: 'PUT',
                body: JSON.stringify(data)
            });

            if (response.message) {
                this.showAlert(response.message, 'success');
                form.reset();
                // Reset password strength indicator
                document.getElementById('password-strength').style.width = '0%';
                document.getElementById('password-strength-text').textContent = 'Password strength will appear here';
            }
        } catch (error) {
            if (error.errors) {
                this.showValidationErrors(error.errors);
            } else {
                this.showAlert(error.message || 'An error occurred while changing password', 'error');
            }
        } finally {
            this.toggleLoading(btn, false);
        }
    }

    validatePasswordMatch() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('password_confirmation').value;
        const errorElement = document.getElementById('password_confirmation-error');

        if (confirmPassword && password !== confirmPassword) {
            errorElement.textContent = 'Passwords do not match';
            errorElement.classList.remove('hidden');
        } else {
            errorElement.classList.add('hidden');
        }
    }

    async makeRequest(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json'
            }
        };

        const response = await fetch(url, { ...defaultOptions, ...options });
        const data = await response.json();

        if (!response.ok) {
            throw data;
        }

        return data;
    }

    toggleLoading(button, isLoading) {
        const text = button.querySelector('.btn-text');
        const spinner = button.querySelector('.loading-spinner');
        
        if (isLoading) {
            text.classList.add('hidden');
            spinner.classList.remove('hidden');
            button.disabled = true;
        } else {
            text.classList.remove('hidden');
            spinner.classList.add('hidden');
            button.disabled = false;
        }
    }

    showAlert(message, type = 'info') {
        const container = document.getElementById('alert-container');
        const alertClass = type === 'success' ? 'bg-green-50 text-green-800 border-green-200' :
                          type === 'error' ? 'bg-red-50 text-red-800 border-red-200' :
                          'bg-blue-50 text-blue-800 border-blue-200';

        const alert = document.createElement('div');
        alert.className = `${alertClass} border px-4 py-3 rounded-lg mb-4`;
        alert.innerHTML = `
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        ${type === 'success' ? 
                            '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>' :
                            '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>'
                        }
                    </svg>
                    <span>${message}</span>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-lg font-bold hover:opacity-70">&times;</button>
            </div>
        `;
        
        container.appendChild(alert);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alert.parentElement) {
                alert.remove();
            }
        }, 5000);
    }

    showValidationErrors(errors) {
        Object.keys(errors).forEach(field => {
            const errorElement = document.getElementById(`${field}-error`);
            if (errorElement) {
                errorElement.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                errorElement.classList.remove('hidden');
            }
        });
    }

    clearErrors() {
        const errorElements = document.querySelectorAll('[id$="-error"]');
        errorElements.forEach(element => {
            element.classList.add('hidden');
            element.textContent = '';
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new PasswordManager();
});
</script>

@endsection
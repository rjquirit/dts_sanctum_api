@extends('layouts.user')

@section('title', 'Profile')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Change Password</h1>
        
        <!-- Alert Messages -->
        <div id="alert-container" class="mb-6"></div>
        
        <!-- Change Password Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form id="password-form">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                        <input type="password" id="current_password" name="current_password" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Enter your current password" required>
                        <span class="text-red-500 text-sm hidden" id="current_password-error"></span>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" id="password" name="password" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Enter new password" required>
                        <span class="text-red-500 text-sm hidden" id="password-error"></span>
                    </div>
                    
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Confirm new password" required>
                        <span class="text-red-500 text-sm hidden" id="password_confirmation-error"></span>
                    </div>
                    
                    <button type="submit" id="change-password-btn" 
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                        <span class="btn-text">Change Password</span>
                        <span class="loading-spinner hidden">
                            <svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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
        const alertClass = type === 'success' ? 'bg-green-100 text-green-800 border-green-200' :
                          type === 'error' ? 'bg-red-100 text-red-800 border-red-200' :
                          'bg-blue-100 text-blue-800 border-blue-200';

        const alert = document.createElement('div');
        alert.className = `${alertClass} border px-4 py-3 rounded mb-4`;
        alert.innerHTML = `
            <div class="flex justify-between items-center">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="text-lg font-bold">&times;</button>
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
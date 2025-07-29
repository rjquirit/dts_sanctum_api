@extends('layouts.app')

@section('content')
<div class="offline-page">
    <h1>You're Offline</h1>
    <p>Sorry, but you're currently offline and this page isn't available. Please check your internet connection and try again.</p>
    
    <div class="offline-actions">
        <button onclick="window.location.reload()">Retry</button>
        <a href="/" class="btn">Go to Homepage</a>
    </div>
</div>
@endsection

@push('styles')
<style>
    .offline-page {
        text-align: center;
        padding: 2rem;
        max-width: 600px;
        margin: 0 auto;
    }
    
    .offline-actions {
        margin-top: 2rem;
        display: flex;
        gap: 1rem;
        justify-content: center;
    }
    
    .offline-actions button,
    .offline-actions .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        background: #4a5568;
        color: white;
        text-decoration: none;
    }
    
    .offline-actions button:hover,
    .offline-actions .btn:hover {
        background: #2d3748;
    }
</style>
@endpush

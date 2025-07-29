@extends('layouts.app')

@section('title', 'Offline')

@section('styles')
<style>
.offline-container {
    text-align: center;
    padding: 40px 20px;
}

.offline-icon {
    font-size: 64px;
    color: #6c757d;
    margin-bottom: 20px;
}
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="offline-container">
                <div class="offline-icon">
                    <i class="fas fa-wifi"></i>
                </div>
                <h2>You're Offline</h2>
                <p class="lead">Please check your internet connection and try again.</p>
                <button class="btn btn-primary" onclick="window.location.reload()">
                    <i class="fas fa-sync-alt"></i> Retry
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

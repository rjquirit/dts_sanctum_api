@extends('layouts.user')

@section('content')

<div class="document-tracker">
    <!-- Search Section -->
    <div class="search-section">
        <div class="search-container">
            <input type="text" id="trackingNumber" class="search-input" placeholder="Enter Tracking Number" value="">
            <button id="searchBtn" class="btn-search">
                <i class="fas fa-search"></i> Search
            </button>
            <button id="scanBtn" class="btn-scan">
        <i class="fas fa-camera"></i> Scan
    </button>
        </div>
        <div style="display: flex; justify-content: flex-end; margin-top: 15px;">
            <button id="printBtn" class="btn-print">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loading" class="loading">
        <div class="spinner"></div>
        <p>Searching document...</p>
    </div>

    <!-- Error Message -->
    <div id="errorMessage" class="error"></div>

    <!-- Document Content (Hidden by default) -->
    <div id="documentContent" style="display: none;">
        <!-- Codes Section -->
        <div class="codes-section">
            <div class="code-container">
                <h6 style="margin-bottom: 15px; color: #555;">Barcode</h6>
                <div id="barcodeContainer">
                    <!-- Barcode will be generated here -->
                </div>
            </div>
            <div class="code-container">
                <h6 style="margin-bottom: 15px; color: #555;">QR Code</h6>
                <div id="qrCodeContainer">
                    <!-- QR Code will be generated here -->
                </div>
            </div>
        </div>

        <!-- Document Details -->
    <div class="document-section">
        <div class="document-details">
            <div class="card-header">
                <i class="fas fa-file-alt"></i> Document Details
            </div>
            <div id="documentDetailsContent">
                <!-- Document details will be populated here -->
            </div>
        </div>

        <!-- Document Route Timeline -->
        <div class="timeline-container">
            <div class="card-header">
                <i class="fas fa-route"></i> Document Route
            </div>
            <div class="timeline" id="documentTimeline">
                <!-- Timeline items will be populated here -->
            </div>
        </div>
    </div>

    </div>

    <!-- Print Container -->
    <div id="printContainer">
        <!-- Content will be cloned here for printing -->
        <div id="printHeader"></div>
        <div id="printContent"></div>
    </div>
</div>
<!-- Scanner Modal -->
<div id="scannerModal" class="scanner-modal">
    <div class="scanner-content">
        <div class="scanner-header">
            <h3 class="scanner-title">
                <i class="fas fa-qrcode"></i> Scan QR Code or Barcode
            </h3>
            <button class="close-scanner" id="closeScannerBtn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="scanner-container">
            <video id="scanner-video" autoplay muted playsinline></video>
            <div class="scanner-controls">
                <button id="toggleScannerBtn" class="btn-scanner-control">
                    <i class="fas fa-play"></i> Start Scanner
                </button>
                <button id="switchCameraBtn" class="btn-scanner-control">
                    <i class="fas fa-sync-alt"></i> Switch Camera
                </button>
            </div>
            <div id="scannerResult" class="scanner-result">
                Position the QR code or barcode within the camera view
            </div>
            <div class="scanner-instructions">
                • Make sure the code is well lit and clearly visible<br>
                • Hold the camera steady and at an appropriate distance<br>
                • The scan will happen automatically when detected
            </div>
        </div>
    </div>
</div>

<!-- Scanner Libraries -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Include the JavaScript file -->
<script src="{{ asset('js/doc_find.js') }}"></script>
@endsection
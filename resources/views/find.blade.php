@extends('layouts.user')

@section('content')
<style>
.document-section {
    display: flex;
    flex-direction: column; /* default (mobile-first) → stacked */
    gap: 20px;
}
.document-tracker {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* 🔸 Orange Gradient Header Section */
.search-section {
    background: linear-gradient(135deg, #ff7e5f 0%, #feb47b 100%);
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(255,126,95,0.3);
}

.search-container {
    display: flex;
    gap: 15px;
    align-items: center;
    max-width: 600px;
    margin: 0 auto;
}

.search-input {
    flex: 1;
    padding: 15px 20px;
    border: none;
    border-radius: 50px;
    font-size: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    outline: none;
    transition: all 0.3s ease;
}

.search-input:focus {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255,126,95,0.3);
}

/* 🔸 Orange Search Button */
.btn-search {
    background: linear-gradient(45deg, #ff6a00, #ff8c00);
    border: none;
    padding: 15px 30px;
    border-radius: 50px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(255,140,0,0.3);
}

.btn-search:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255,140,0,0.4);
}

/* 🔸 Print Button - Softer Orange */
.btn-print {
    background: linear-gradient(45deg, #ff9966, #ff5e62);
    border: none;
    padding: 12px 25px;
    border-radius: 25px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-left: auto;
}

.btn-print:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255,94,98,0.4);
}

.codes-section {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    gap: 20px;
}

.code-container {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    text-align: center;
    flex: 1;
    transition: all 0.3s ease;
}

.code-container:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(255,126,95,0.2);
}

.document-details {
    background: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.document-details:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255,126,95,0.15);
}

/* 🔸 Orange Header */
.card-header {
    background: linear-gradient(135deg, #ff6a00 0%, #ff8c00 100%);
    color: white;
    padding: 15px 25px;
    border-radius: 10px 10px 0 0;
    font-weight: 600;
    font-size: 18px;
    margin: -30px -30px 20px -30px;
}

.detail-row {
    display: flex;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
    align-items: center;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: #555;
    min-width: 180px;
}

.detail-value {
    color: #333;
    flex: 1;
}

.tags {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.tag {
    background: linear-gradient(45deg, #ffb347, #ffcc33);
    color: #333;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 14px;
    font-weight: 500;
}

.timeline-container {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(to bottom, #ff7e5f, #feb47b);
    border-radius: 2px;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
    padding: 20px;
    background: #fdf7f2;
    border-radius: 12px;
    margin-left: 20px;
    transition: all 0.3s ease;
}

.timeline-item:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 15px rgba(255,126,95,0.2);
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -32px;
    top: 25px;
    width: 12px;
    height: 12px;
    background: #ff7e5f;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 0 3px #ff7e5f;
}

.timeline-item.forwarded::before {
    background: #ff8c00;
    box-shadow: 0 0 0 3px #ff8c00;
}

.timeline-item.received::before {
    background: #ffb347;
    box-shadow: 0 0 0 3px #ffb347;
}

.timeline-item.completed::before {
    background: #ff5e62;
    box-shadow: 0 0 0 3px #ff5e62;
}

.timeline-item.pending::before {
    background: #ffc107;
    box-shadow: 0 0 0 3px #ffc107;
}

.badge {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    color: white;
}

.badge-success { background-color: #28a745; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-danger { background-color: #dc3545; }
.badge-primary { background-color: #ff8c00; }
.badge-info { background-color: #ff5e62; }
.badge-secondary { background-color: #6c757d; }

.timeline-date {
    color: #ff6a00;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 8px;
}

.timeline-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
    font-size: 16px;
}

.timeline-details {
    color: #666;
    font-size: 14px;
    line-height: 1.5;
}

.loading {
    display: none;
    text-align: center;
    padding: 40px;
}

.loading .spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #ff7e5f;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.error {
    background: #fff4f0;
    border: 1px solid #ffccbc;
    color: #d35400;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
    display: none;
}
@media (min-width: 992px) {
    .document-section {
        flex-direction: row;
        align-items: flex-start;
    }

    .document-details, 
    .timeline-container {
        flex: 1;              /* take equal width */
        max-width: 50%;       /* optional limit */
    }
}
@media (max-width: 768px) {
    .search-container {
        flex-direction: column;
    }
    
    .codes-section {
        flex-direction: column;
    }
    
    .detail-row {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .detail-label {
        min-width: auto;
        margin-bottom: 5px;
    }
    
}
</style>

<div class="document-tracker">
    <!-- Search Section -->
    <div class="search-section">
        <div class="search-container">
            <input type="text" id="trackingNumber" class="search-input" placeholder="Enter Tracking Number" value="">
            <button id="searchBtn" class="btn-search">
                <i class="fas fa-search"></i> Search
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

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Include the JavaScript file -->
<script src="{{ asset('js/doc_find.js') }}"></script>
@endsection
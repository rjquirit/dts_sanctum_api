@extends('layouts.user')

@section('content')
<div class="container">
    <!-- Top Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="input-group w-50">
            <input type="text" class="form-control" placeholder="Enter Tracking Number" value="23-10-7798">
            <button class="btn btn-primary">Search</button>
        </div>
        <button class="btn btn-secondary">Print</button>
    </div>

    <div class="d-flex justify-content-between mb-4">
        <div>
            <img src="{{ asset('images/barcode.png') }}" alt="Barcode">
        </div>
        <div>
            <img src="{{ asset('images/qrcode.png') }}" alt="QR Code">
        </div>
    </div>

    <!-- Middle Section -->
    <div class="card mb-4">
        <div class="card-header">Document Details</div>
        <div class="card-body">
            <p><strong>From:</strong> RJ</p>
            <p><strong>Office:</strong> ICT</p>
            <p><strong>Datetime Submission:</strong> Jun 7, 2023, 5 PM</p>
            <p><strong>Doc Type:</strong> Leave Description</p>
            <p><strong>Tags:</strong> #C1007 & #88</p>
        </div>
    </div>

    <!-- Bottom Section -->
    <div class="card">
        <div class="card-header">Document Route</div>
        <div class="card-body">
            <h5>Sender (Jun 7, 2023)</h5>
            <p><strong>Name:</strong> [Sender Name]</p>
            <p><strong>Division:</strong> [Division]</p>
            <p><strong>Purpose:</strong> [Purpose]</p>

            <hr>

            <h5>In Transit</h5>
            <p><strong>From:</strong> [From]</p>
            <p><strong>To:</strong> [To]</p>
            <p><strong>Purpose:</strong> [Purpose]</p>
        </div>
    </div>
</div>
@endsection
@extends('layouts.user')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <h5>New Document</h5>
            <hr>
            
            
            <form id="documentForm" method="POST" action="" enctype="multipart/form-data">
            
            @csrf
                
                <!-- From Field -->
                <div class="row mb-3">
                    <label for="from" class="col-sm-3 col-form-label">From:</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="from" name="from" 
                               placeholder="Full Name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Office Field -->
                <div class="row mb-3">
                    <label for="office" class="col-sm-3 col-form-label">Office:</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="office" name="office" 
                               placeholder="Division / School / Office" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Document Type Field -->
                <div class="row mb-3">
                    <label for="document_type" class="col-sm-3 col-form-label">Document Type:</label>
                    <div class="col-sm-9">
                        <select class="form-select" id="document_type" name="document_type" required>
                            <option value="">--Select--</option>
                            <option value="memo">Memorandum</option>
                            <option value="letter">Official Letter</option>
                            <option value="report">Report</option>
                            <option value="request">Request</option>
                            <option value="circular">Circular</option>
                            <option value="notice">Notice</option>
                            <option value="other">Other</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Details Field -->
                <div class="row mb-3">
                    <label for="details" class="col-sm-3 col-form-label">Details:</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="details" name="details" rows="4" 
                                  placeholder="Description, Date, Destination" required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Purpose of Submission Field -->
                <div class="row mb-3">
                    <label for="purpose" class="col-sm-3 col-form-label">Purpose of Submission:</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="purpose" name="purpose" rows="4" 
                                  placeholder="Purposes or Actions to be taken..." required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Receiving Units Field -->
                <div class="row mb-3">
                    <label for="receiving_units" class="col-sm-3 col-form-label">Receiving Units:</label>
                    <div class="col-sm-9">
                        <select class="form-select" id="receiving_units" name="receiving_units" required>
                            <option value="">--Select--</option>
                            <option value="admin">Administrative Office</option>
                            <option value="finance">Finance Department</option>
                            <option value="hr">Human Resources</option>
                            <option value="academic">Academic Affairs</option>
                            <option value="registrar">Registrar's Office</option>
                            <option value="library">Library</option>
                            <option value="ict">ICT Department</option>
                            <option value="maintenance">Maintenance & Security</option>
                            <option value="other">Other</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- File Upload Field -->
                <div class="row mb-4">
                    <label for="document_file" class="col-sm-3 col-form-label">Document File:</label>
                    <div class="col-sm-9">
                        <input type="file" class="form-control" id="document_file" name="document_file" 
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        <div class="form-text">Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max: 10MB)</div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="button" class="btn btn-secondary me-2" id="cancelBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Submit
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="loadingModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 mb-0">Submitting document...</p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/doc_add.js') }}"></script>
@endpush
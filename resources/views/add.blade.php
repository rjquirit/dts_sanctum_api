@extends('layouts.user')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <h5>New Document</h5>
            <hr>
            
            <form id="documentForm" method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
                @csrf
                <!-- Make sure CSRF token meta tag is present -->
                <meta name="csrf-token" content="{{ csrf_token() }}">

                <!-- From Field -->
                <div class="row mb-3">
                    <label for="origin_fname" class="col-sm-3 col-form-label">From:</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="origin_fname" name="origin_fname" 
                               placeholder="Loading..." value="{{ Auth::user()->name }}" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Office/School Field -->
                <div class="row mb-3">
                    <label for="origin_school" class="col-sm-3 col-form-label">Office/School:</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="origin_school" name="origin_school" 
                               placeholder="Loading..." value="" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Document Type Field -->
                <div class="row mb-3">
                    <label for="doc_type_id" class="col-sm-3 col-form-label">Document Type: <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <select id="doc_type_id" name="doc_type_id" class="form-select" required>
                            <option value="">-- Select Document Type --</option>
                        </select>
                        <div class="invalid-feedback">Please select a document type</div>
                    </div>
                </div>

                <!-- Details Field -->
                <div class="row mb-3">
                    <label for="docs_description" class="col-sm-3 col-form-label">Details: <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="docs_description" name="docs_description" rows="4" 
                                  placeholder="Description, Date, Destination" required></textarea>
                        <div class="invalid-feedback">Please enter document details</div>
                    </div>
                </div>

                <!-- Purpose of Submission Field -->
                <div class="row mb-3">
                    <label for="actions_needed" class="col-sm-3 col-form-label">Purpose of Submission: <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="actions_needed" name="actions_needed" rows="4" 
                                  placeholder="Purposes or Actions to be taken..." required></textarea>
                        <div class="invalid-feedback">Please enter the purpose of submission</div>
                    </div>
                </div>

                <!-- Receiving Units Field -->
                <div class="row mb-3">
                    <label for="receiving_section" class="col-sm-3 col-form-label">Receiving Units: <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <select id="receiving_section" name="receiving_section" class="form-select" required>
                            <option value="">-- Select Receiving Unit --</option>
                        </select>
                        <div class="invalid-feedback">Please select a receiving unit</div>
                    </div>
                </div>

                <!-- File Upload Field -->
                <!-- <div class="row mb-4">
                    <label for="document_file" class="col-sm-3 col-form-label">Document File:</label>
                    <div class="col-sm-9">
                        <input type="file" class="form-control" id="document_file" name="document_file" 
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <div class="form-text">Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max: 10MB)</div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div> -->

                <!-- Hidden fields for current user info -->
                <input type="hidden" id="origin_userid" name="origin_userid" value="{{ Auth::id() }}">
                <input type="hidden" id="origin_section" name="origin_section" value="{{ Auth::user()->section_id }}">
                <input type="hidden" id="origin_school_id" name="origin_school_id" value="{{ Auth::user()->school_id ?? 1 }}">

                <!-- Action Buttons -->
                <div class="row">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="button" class="btn btn-secondary me-2" id="cancelBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Submit Document
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
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h6 class="mb-0">Submitting document...</h6>
                <p class="small text-muted mb-0">Please wait while we process your request.</p>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel">Success!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">Document Submitted Successfully</h4>
                    <p class="mb-0">Your document has been submitted and is now being processed.</p>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="{{ route('incoming') }}" class="btn btn-success">Return to Incoming Documents</a>
                <a href="{{ route('add') }}" class="btn btn-outline-secondary">Create Another</a>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script type="module" src="{{ asset('js/doc_add.js') }}"></script>
@endpush
@endsection
@extends('layouts.user')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h5>Document Forwarding</h5>
            <hr>
            
            <!-- Toggle Switch for Document Type -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="toggleSwitch">
                        <label class="form-check-label" for="toggleSwitch">Show Personal Documents</label>
                    </div>

                </div>
                <!-- <div class="col-md-3">
                    <select class="form-select" id="docTypeFilter">
                        <option value="">All Document Types</option>
                        @foreach($documentTypes ?? [] as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div> -->
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search documents..." id="searchInput">
                        <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="text-center py-5" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading documents...</p>
            </div>

            <!-- Documents Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Tracking #</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Origin</th>
                            <th>Status</th>
                            <th>Date Posted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="documentsTableBody">
                        <!-- Documents will be loaded here by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- No Data Message -->
            <div id="noDataMessage" class="text-center py-5" style="display: none;">
                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No documents found</h5>
                <p class="text-muted">Try adjusting your search or filters</p>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <span id="paginationInfo" class="text-muted">Showing 0 to 0 of 0 entries</span>
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="paginationLinks">
                        <!-- Pagination links will be added here by JavaScript -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- View Document Modal -->
<div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDocumentModalLabel">Document Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="documentDetails">
                <!-- Document details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script type="module" src="{{ asset('js/doc_forward.js') }}"></script>
@endpush

@endsection
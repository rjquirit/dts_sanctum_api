@extends('layouts.user')
@section('content')
<style>

    
    .detail-row {
        display: flex;
        margin-bottom: 0.5rem;
        border-bottom: 1px solid #eee;
        padding: 0.5rem 0;
    }
    
    .detail-label {
        width: 150px;
        font-weight: bold;
        color: #666;
    }
    
    .detail-value {
        flex: 1;
    }
    
    .timeline-item {
        border-left: 3px solid #ccc;
        padding: 1rem;
        margin-bottom: 1rem;
        position: relative;
    }
    
    .timeline-item:before {
        content: '';
        position: absolute;
        left: -8px;
        top: 20px;
        width: 13px;
        height: 13px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #ccc;
    }
    
    .timeline-date {
        color: #666;
        font-size: 0.9rem;
    }
    
    .timeline-title {
        font-weight: bold;
        margin: 0.5rem 0;
    }
    
    .timeline-details {
        background: #f8f9fa;
        padding: 0.75rem;
        border-radius: 4px;
        font-size: 0.9rem;
    }
    
    .timeline-item.completed {
        border-left-color: #28a745;
    }
    
    .timeline-item.completed:before {
        border-color: #28a745;
    }
    
    .timeline-item.received {
        border-left-color: #17a2b8;
    }
    
    .timeline-item.received:before {
        border-color: #17a2b8;
    }
    
    .timeline-item.pending {
        border-left-color: #ffc107;
    }
    
    .timeline-item.pending:before {
        border-color: #ffc107;
    }

</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h5>Archived Documents</h5>
            <hr>
            
            <!-- Toggle Switch for Document Type -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="toggleSwitch">
                        <label class="form-check-label" for="toggleSwitch">Show Personal Archived Documents</label>
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
                            <th class="sortable" data-sort-by="doc_tracking">
                                TRACKING #
                                <i class="fas fa-sort ms-1"></i>
                            </th>
                            <th class="sortable" data-sort-by="docs_description">
                                TYPE & DESCRIPTION
                                <i class="fas fa-sort ms-1"></i>
                            </th>
                            <th class="sortable" data-sort-by="route_fromsection">
                                FROM
                                <i class="fas fa-sort ms-1"></i>
                            </th>
                            <th class="sortable" data-sort-by="route_purpose">
                                ACTIONS NEEDED
                                <i class="fas fa-sort ms-1"></i>
                            </th>
                            <th class="sortable" data-sort-by="datetime_forwarded">
                                DATE POSTED
                                <i class="fas fa-sort ms-1"></i>
                            </th>
                            <th>ACTION</th>
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
<!-- Add this after your existing modals -->
<div class="modal fade" id="documentDetailsModal" tabindex="-1" aria-labelledby="documentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentDetailsModalLabel">Document Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Document Details Section -->
                <div class="document-section">
                    <div class="document-details">
                        <div class="card-header">
                            <i class="fas fa-file-alt"></i> Document Details
                        </div>
                        <div id="modalDocumentDetailsContent">
                            <!-- Document details will be populated here -->
                        </div>
                    </div>

                    <!-- Document Route Timeline -->
                    <div class="timeline-container mt-4">
                        <div class="card-header">
                            <i class="fas fa-route"></i> Document Route
                        </div>
                        <div class="timeline" id="modalDocumentTimeline">
                            <!-- Timeline items will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script type="module" src="{{ asset('js/doc_deferred.js') }}"></script>
@endpush

@endsection
@extends('layouts.user')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h5>Send (Forward)</h5>
            <hr>
            
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h2 class="text-primary mb-0">140</h2>
                            <p class="card-text text-muted mb-0">Total Office Docs</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-secondary">
                        <div class="card-body text-center">
                            <h2 class="text-secondary mb-0">79</h2>
                            <p class="card-text text-muted mb-0">Total Personal Docs</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Toggle Section -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="toggleSwitch">
                        <label class="form-check-label" for="toggleSwitch">
                            Toggle Section
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search..." id="searchBox">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Code</th>
                            <th scope="col">Description</th>
                            <th scope="col">Date</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>DOC001</td>
                            <td>Sample archived document</td>
                            <td>2024-01-15</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>DOC002</td>
                            <td>Another archived document</td>
                            <td>2024-01-14</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>DOC003</td>
                            <td>Third archived document</td>
                            <td>2024-01-13</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>DOC004</td>
                            <td>Fourth archived document</td>
                            <td>2024-01-12</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <small class="text-muted">Showing 1 to 4 of 219 entries</small>
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                        </li>
                        <li class="page-item active">
                            <a class="page-link" href="#">1</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">2</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">3</a>
                        </li>
                        <li class="page-item">
                            <span class="page-link">...</span>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">55</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Add some custom styles -->
<style>
    .card {
        transition: transform 0.2s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .table th {
        border-top: none;
    }
    
    .btn-group .btn {
        margin: 0 1px;
    }
    
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
</style>

<!-- Add JavaScript for interactivity -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchBox = document.getElementById('searchBox');
        searchBox.addEventListener('input', function() {
            // Add search logic here
            console.log('Searching for:', this.value);
        });
        
        // Toggle switch functionality
        const toggleSwitch = document.getElementById('toggleSwitch');
        toggleSwitch.addEventListener('change', function() {
            // Add toggle logic here
            console.log('Toggle state:', this.checked);
        });
        
        // Table row hover effects
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8f9fa';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    });
</script>
@endsection
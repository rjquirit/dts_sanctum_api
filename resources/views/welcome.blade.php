@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
<div class="container d-flex flex-column align-items-center justify-content-center min-vh-100">
    <div class="card shadow p-4" style="max-width: 480px; width: 100%;">
        <div class="text-center mb-4">
            <h1 class="mb-3" style="font-family: 'Google Sans', Arial, sans-serif; font-weight: 700; font-size: 2.5rem; color: #4285f4;">DTS Search</h1>
            <div class="mb-3">
                <a href="/login" class="btn btn-outline-primary me-2">Login</a>
                <a href="/register" class="btn btn-outline-secondary">Register</a>
            </div>
        </div>
        <form id="doc-search-form" class="mb-3">
            <div class="input-group input-group-lg mb-2">
                <input type="text" class="form-control" id="doc_id" name="doc_id" placeholder="Search Doc ID..." autocomplete="off" required>
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
            </div>
        </form>
        <div id="search-result" class="mb-3"></div>
        <div class="border-top pt-3">
            <h6 class="text-muted mb-2">Recent Routes</h6>
            <div id="routes-result">Loading...</div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search Docmain by Doc_id
    document.getElementById('doc-search-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const docId = document.getElementById('doc_id').value;
        const resultDiv = document.getElementById('search-result');
        resultDiv.innerHTML = 'Searching...';
        fetch(`/api/docmain/track/${encodeURIComponent(docId)}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.doc) {
                    resultDiv.innerHTML = `<div class='alert alert-success'><b>Doc ID:</b> ${data.doc.doc_tracking}<br><b>Description:</b> ${data.doc.docs_description}</div>`;
                } else {
                    resultDiv.innerHTML = `<div class='alert alert-warning'>No document found for Doc ID: <b>${docId}</b></div>`;
                }
            })
            .catch(() => {
                resultDiv.innerHTML = `<div class='alert alert-danger'>Error searching document.</div>`;
            });
    });

    // Load DocroutesController recent routes
    fetch('/api/docroutes/recent')
        .then(res => res.json())
        .then(data => {
            const routesDiv = document.getElementById('routes-result');
            if (data && data.routes && data.routes.length) {
                routesDiv.innerHTML = '<ul class="list-group">' +
                    data.routes.map(route => `<li class="list-group-item"><b>${route.doc_tracking}</b> - ${route.status} (${route.datetime_forwarded})</li>`).join('') +
                    '</ul>';
            } else {
                routesDiv.innerHTML = '<div class="text-muted">No recent routes found.</div>';
            }
        })
        .catch(() => {
            document.getElementById('routes-result').innerHTML = '<div class="text-danger">Error loading routes.</div>';
        });
});
</script>
@endsection

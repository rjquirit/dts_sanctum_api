
@extends('layouts.app')

@section('content')
<div class="container mx-auto py-10">
    <div class="flex flex-col items-center justify-center min-h-screen">
        <h1 class="text-4xl font-bold mb-8">Document Tracking Search</h1>
        <form id="search-form" class="w-full max-w-xl flex flex-col items-center" onsubmit="return false;">
            <div class="w-full flex items-center border rounded-lg shadow px-4 py-2 bg-white">
                <input id="doc_id_input" type="text" class="flex-1 outline-none text-lg" placeholder="Enter Document Tracking Number..." autofocus />
                <button id="search-btn" type="button" class="ml-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Search</button>
            </div>
        </form>
        <div id="search-error" class="text-red-600 mt-2 hidden"></div>
        <div id="timeline-container" class="w-full max-w-2xl mt-10"></div>
    </div>
</div>
@endsection


@push('scripts')
<script src="/js/search.js"></script>
@endpush


<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ErrorLogController extends Controller
{
    /**
     * Display a listing of the error logs.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
     public function index(Request $request)
    {
        try {
            $errorLogs = ErrorLog::query()
                ->with('user:id,name')
                ->when($request->input('type') && $request->input('type') != 'all', function ($query, $type) {
                    return $query->where('type', $type);
                })
                ->when($request->input('environment') && $request->input('environment') != 'all', function ($query, $environment) {
                    return $query->where('environment', $environment);
                })
                ->when($request->input('status_code') && $request->input('status_code') != 'all', function ($query, $statusCode) {
                    return $query->where('status_code', $statusCode);
                })
                ->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 10))
                ->withQueryString();

            return Inertia::render('Auth/Error', [
                'logs' => $errorLogs,
                'filters' => $request->only(['search', 'type', 'environment', 'status_code']),
                'auth' => [
                    'user' => $request->user()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch error logs', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Failed to fetch error logs');
        }
    }
    /**
     * Store a new error log.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        Log::info("Storing error log", ['request_data' => $request->all()]);

        try {
            $validated = $request->validate([
                'message' => 'required|string|max:65535',
                'stack_trace' => 'nullable|string|max:65535',
                'url' => 'nullable|string|max:255',
                'user_agent' => 'nullable|string|max:255',
                'method' => 'nullable|string|max:255',
                'status_code' => 'nullable|integer',
                'type' => 'nullable|string|max:255',
                'environment' => 'nullable|string|max:255',
                'additional_data' => 'nullable|array', // Changed from 'json' to 'array'
            ]);

            // Convert additional_data array to JSON
            $additionalData = isset($validated['additional_data'])
                ? json_encode($validated['additional_data'])
                : json_encode(['ip' => $request->ip(), 'timestamp' => now()->toISOString()]);

            $errorLog = ErrorLog::create([
                'message' => $validated['message'],
                'stack_trace' => $validated['stack_trace'] ?? null,
                'url' => $validated['url'] ?? $request->fullUrl(),
                'user_agent' => $validated['user_agent'] ?? $request->userAgent(),
                'method' => $validated['method'] ?? $request->method(),
                'status_code' => $validated['status_code'] ?? null,
                'type' => $validated['type'] ?? 'frontend',
                'user_id' => $request->user()?->id,
                'environment' => $validated['environment'] ?? config('app.env'),
                'additional_data' => $additionalData,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'id' => $errorLog->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to store error log', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error logged internally',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}

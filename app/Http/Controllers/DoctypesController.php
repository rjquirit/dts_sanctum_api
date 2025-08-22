<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Doctypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DoctypesController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            
            $query = Doctypes::when($search, function($q) use ($search) {
                return $q->where('doctype_description', 'like', "%{$search}%");
            });

            $doctypes = $query->paginate($perPage);

            return response()->json([
                'doctypes' => $doctypes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch document types',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'doctype_description' => 'required|string|max:255',
                'active' => 'boolean'
            ]);

            $doctype = Doctypes::create([
                'doctype_description' => $validated['doctype_description'],
                'active' => $validated['active'] ?? true
            ]);

            return response()->json([
                'message' => 'Document type created successfully',
                'doctype' => $doctype
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating document type:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to create document type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $doctype = Doctypes::findOrFail($id);
            
            return response()->json([
                'doctype' => $doctype
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Document type not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $doctype = Doctypes::findOrFail($id);

            $validated = $request->validate([
                'doctype_description' => 'required|string|max:255',
                'active' => 'boolean'
            ]);

            $doctype->update($validated);

            return response()->json([
                'message' => 'Document type updated successfully',
                'doctype' => $doctype
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating document type:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to update document type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $doctype = Doctypes::findOrFail($id);
            $doctype->delete();

            return response()->json([
                'message' => 'Document type deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete document type',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\Sections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class SectionController extends Controller
{
    public function index(): JsonResponse
    {
        $sections = Sections::paginate(10);
        return response()->json([
            'data' => $sections,
            'message' => 'Sections retrieved successfully'
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'section_description' => 'required|string|max:255',
            'office_id' => 'required|integer',
            'initial_receipt' => 'required|boolean',
            'public_view' => 'required|boolean',
            'hidden' => 'required|boolean',
            'active' => 'required|boolean'
        ]);

        try {
            $section = Sections::create($request->all());
            return response()->json([
                'data' => $section,
                'message' => 'Section created successfully'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating section: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error creating section',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $section = Sections::findOrFail($id);
            return response()->json([
                'data' => $section,
                'message' => 'Section retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Section not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'section_description' => 'required|string|max:255',
            'office_id' => 'required|integer',
            'initial_receipt' => 'required|boolean',
            'public_view' => 'required|boolean',
            'hidden' => 'required|boolean',
            'active' => 'required|boolean'
        ]);

        try {
            $section = Sections::findOrFail($id);
            $section->update($request->all());
            return response()->json([
                'data' => $section,
                'message' => 'Section updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating section: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error updating section',
                'error' => $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $section = Sections::findOrFail($id);
            $section->delete();
            return response()->json([
                'message' => 'Section deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting section: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error deleting section',
                'error' => $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }
}
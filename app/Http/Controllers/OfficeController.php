<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offices;
use App\Models\Sections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OfficeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Offices::query();

            if ($search = $request->input('search')) {
                $query->where('office_name', 'like', "%{$search}%");
            }

            if ($active = $request->boolean('active')) {
                $query->where('active', true);
            }

            $offices = $query->paginate($request->input('per_page', 10));

            return response()->json($offices);
        } catch (\Exception $e) {
            Log::error('Error fetching offices:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to fetch offices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'office_name' => 'required|string|max:255',
                'sch_id' => 'required',
                'district' => 'required',
                'active' => 'boolean'
            ]);

            $office = Offices::create($validated);

            return response()->json([
                'message' => 'Office created successfully',
                'office' => $office
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating office:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to create office',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $office = Offices::findOrFail($id);
            return response()->json($office);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Office not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $office = Offices::findOrFail($id);

            $validated = $request->validate([
                'office_name' => 'required|string|max:255',
                'sch_id' => 'required',
                'district' => 'required',
                'active' => 'boolean'
            ]);

            $office->update($validated);

            return response()->json([
                'message' => 'Office updated successfully',
                'office' => $office
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating office:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to update office',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $office = Offices::findOrFail($id);
            $office->delete();

            return response()->json([
                'message' => 'Office deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete office',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getSections($id)
    {
        try {
            $office = Offices::findOrFail($id);
            $sections = Sections::where('office_id', $id)
                ->where('active', true)
                ->get();

            return response()->json([
                'office' => $office,
                'sections' => $sections
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch sections',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

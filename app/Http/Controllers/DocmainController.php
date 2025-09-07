<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Docmain;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class DocmainController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            // Handle both auth:sanctum and VerifySanctumToken middleware
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please log in to access documents'
                ], 401);
            }

            $query = Docmain::with(['doctype', 'origin_section', 'origin_office', 'routes']);

            // Toggle-based filtering: false = office, true = personal
            if ($request->has('toggle')) {
                if ($request->toggle === 'true' || $request->toggle === true) {
                    // Personal documents: filter by acceptedby_userid = current user's id
                    $query->where('acceptedby_userid', $user->id);
                } else {
                    // Office documents: filter by receiving_section = current user's section_id
                    $query->where('receiving_section', $user->section_id);
                }
            } else {
                // Default behavior: show office documents
                $query->where('receiving_section', $user->section_id);
            }
            Log::info("Forwarded Docs Query", [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
            ]);
            
            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('doc_tracking', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('docs_description', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('origin_fname', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('origin_school', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('actions_needed', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('acct_payee', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('acct_particulars', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('final_actions_made', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('deactivate_reason', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Filter by active status
            if ($request->has('active')) {
                $query->where('active', $request->active);
            }

            // Filter by document type
            if ($request->has('doc_type_id')) {
                $query->where('doc_type_id', $request->doc_type_id);
            }

            // Filter by origin section
            if ($request->has('origin_section')) {
                $query->where('origin_section', $request->origin_section);
            }

            // Filter by done status
            if ($request->has('done')) {
                $query->where('done', $request->done);
            }

            // Date range filter for datetime_posted
            if ($request->has('date_from') && $request->has('date_to')) {
                $query->whereBetween('datetime_posted', [
                    $request->date_from . ' 00:00:00',
                    $request->date_to . ' 23:59:59'
                ]);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'doc_id');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $documents = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Documents retrieved successfully',
                'data' => $documents
            ]);

        } catch (\Exception $e) {
            Log::error('Error in DocmainController@index', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving documents',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created document.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'track_issuedby_userid' => 'required|integer',
                'doc_type_id' => 'required|integer',
                'tempdocs_id' => 'nullable|integer',
                'docs_description' => 'required|string',
                'origin_fname' => 'nullable|string|max:255',
                'origin_userid' => 'nullable|integer',
                'origin_school_id' => 'nullable|integer',
                'origin_school' => 'nullable|string|max:255',
                'origin_section' => 'nullable|integer',
                'receiving_section' => 'nullable|integer',
                'actions_needed' => 'nullable|string|max:255',
                'datetime_posted' => 'required|date',
                'datetime_accepted' => 'nullable|date',
                'acceptedby_userid' => 'nullable|integer',
                'acct_dvnum' => 'nullable|string|max:255',
                'acct_payee' => 'nullable|string|max:255',
                'acct_particulars' => 'nullable|string|max:255',
                'acct_amount' => 'nullable|numeric',
                'final_actions_made' => 'nullable|string',
                'done' => 'nullable|integer|in:0,1',
                'updatedby_id' => 'nullable|integer',
                'archive_id' => 'nullable|integer',
                'active' => 'nullable|integer|in:0,1',
                'deactivate_reason' => 'nullable|string|max:255',
                'tags' => 'nullable|json',
                'additional_receivers' => 'nullable|json'
            ]);

            $document = Docmain::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Document created successfully',
                'data' => $document->load(['doctype', 'origin_section', 'origin_office'])
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified document.
     */
    public function show($id): JsonResponse
    {
        try {
            $document = Docmain::with(['doctype', 'origin_section', 'origin_office', 'routes'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Document retrieved successfully',
                'data' => $document
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified document.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $document = Docmain::findOrFail($id);

            $validatedData = $request->validate([
                'track_issuedby_userid' => 'sometimes|integer',
                'doc_type_id' => 'sometimes|integer',
                'tempdocs_id' => 'nullable|integer',
                'docs_description' => 'sometimes|string',
                'origin_fname' => 'nullable|string|max:255',
                'origin_userid' => 'nullable|integer',
                'origin_school_id' => 'nullable|integer',
                'origin_school' => 'nullable|string|max:255',
                'origin_section' => 'nullable|integer',
                'receiving_section' => 'nullable|integer',
                'actions_needed' => 'nullable|string|max:255',
                'datetime_posted' => 'sometimes|date',
                'datetime_accepted' => 'nullable|date',
                'acceptedby_userid' => 'nullable|integer',
                'acct_dvnum' => 'nullable|string|max:255',
                'acct_payee' => 'nullable|string|max:255',
                'acct_particulars' => 'nullable|string|max:255',
                'acct_amount' => 'nullable|numeric',
                'final_actions_made' => 'nullable|string',
                'done' => 'nullable|integer|in:0,1',
                'updatedby_id' => 'nullable|integer',
                'archive_id' => 'nullable|integer',
                'active' => 'nullable|integer|in:0,1',
                'deactivate_reason' => 'nullable|string|max:255',
                'tags' => 'nullable|json',
                'additional_receivers' => 'nullable|json'
            ]);

            // Add updated timestamp and user
            $validatedData['datetime_updated'] = now();
            $validatedData['updatedby_id'] = auth()->id();

            $document->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Document updated successfully',
                'data' => $document->load(['doctype', 'origin_section', 'origin_office'])
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $document = Docmain::findOrFail($id);
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete document by setting active to 0.
     */
    public function deactivate(Request $request, $id): JsonResponse
    {
        try {
            $document = Docmain::findOrFail($id);

            $validatedData = $request->validate([
                'deactivate_reason' => 'required|string|max:255'
            ]);

            $document->update([
                'active' => 0,
                'deactivate_reason' => $validatedData['deactivate_reason'],
                'datetime_updated' => now(),
                'updatedby_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document deactivated successfully',
                'data' => $document
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deactivating document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore deactivated document.
     */
    public function activate($id): JsonResponse
    {
        try {
            $document = Docmain::findOrFail($id);

            $document->update([
                'active' => 1,
                'deactivate_reason' => '',
                'datetime_updated' => now(),
                'updatedby_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document activated successfully',
                'data' => $document
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error activating document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark document as done.
     */
    public function markDone(Request $request, $id): JsonResponse
    {
        try {
            $document = Docmain::findOrFail($id);

            $validatedData = $request->validate([
                'final_actions_made' => 'required|string'
            ]);

            $document->update([
                'done' => 1,
                'final_actions_made' => $validatedData['final_actions_made'],
                'datetime_updated' => now(),
                'updatedby_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document marked as done successfully',
                'data' => $document
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking document as done',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accept document.
     */
    public function accept($id): JsonResponse
    {
        try {
            $document = Docmain::findOrFail($id);

            $document->update([
                'datetime_accepted' => now(),
                'acceptedby_userid' => auth()->id(),
                'datetime_updated' => now(),
                'updatedby_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document accepted successfully',
                'data' => $document
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error accepting document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get document statistics.
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'total_documents' => Docmain::count(),
                'active_documents' => Docmain::where('active', 1)->count(),
                'inactive_documents' => Docmain::where('active', 0)->count(),
                'done_documents' => Docmain::where('done', 1)->count(),
                'pending_documents' => Docmain::where('done', 0)->count(),
                'accepted_documents' => Docmain::whereNotNull('datetime_accepted')->count(),
                'unaccepted_documents' => Docmain::whereNull('datetime_accepted')->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Document statistics retrieved successfully',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
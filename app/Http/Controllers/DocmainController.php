<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Docmain;
Use App\Models\Docroutes;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

            $sortBy = $request->get('sort_by', 'datetime_route_accepted');
            $sortOrder = strtolower($request->get('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

            $sortMap = [
                // route columns (dts_docroutes)
                'action_id' => 'dts_docroutes.action_id',
                'datetime_forwarded' => 'dts_docroutes.datetime_forwarded',
                'datetime_route_accepted' => 'dts_docroutes.datetime_route_accepted',
                'route_purpose' => 'dts_docroutes.route_purpose',
                // doc (dts_docs) columns
                'datetime_posted' => 'dts_docs.datetime_posted',
                'doc_tracking' => 'dts_docs.doc_tracking',
                'docs_description' => 'dts_docs.docs_description',
                'origin_fname' => 'dts_docs.origin_fname',
            ];

            // if unknown sort request, default to a safe column
            $sortColumn = $sortMap[$sortBy] ?? 'dts_docroutes.datetime_route_accepted';

            // --- base query: select routes and join docs so we can sort by doc fields safely ---
            $query = Docroutes::select('dts_docroutes.*')
                ->leftJoin('dts_docs', 'dts_docs.doc_id', '=', 'dts_docroutes.document_id')
                ->with(['document.doctype', 'document.origin_section', 'document.origin_office']);

            // Keep the route filters (qualified column names)
            $query->where('dts_docroutes.datetime_route_accepted', 0)
                ->where('dts_docroutes.active', 1);

            // Toggle: personal vs office
            if ($request->has('toggle')) {
                // use boolean() to allow 'true'/'false' strings
                if ($request->boolean('toggle')) {
                    $query->where('dts_docroutes.route_touser_id', $user->id);
                } else {
                    $query->where('dts_docroutes.route_tosection_id', $user->section_id);
                }
            } else {
                $query->where('dts_docroutes.route_tosection_id', $user->section_id);
            }

            // Search: since we've joined dts_docs, search on qualified columns
            if ($request->filled('search')) {
                $searchTerm = '%' . $request->search . '%';
                $query->where(function($q) use ($searchTerm) {
                    $q->where('dts_docs.doc_tracking', 'like', $searchTerm)
                    ->orWhere('dts_docs.docs_description', 'like', $searchTerm)
                    ->orWhere('dts_docs.origin_fname', 'like', $searchTerm);
                });
            }

            // Apply ordering using qualified column
            $query->orderByRaw("$sortColumn $sortOrder");

            // Pagination (select dts_docroutes.* earlier avoids ambiguous column errors)
            $perPage = (int) $request->get('per_page', 15);
            $documents = $query->paginate($perPage);

            // --- transform the paginator items into flat objects your frontend expects ---
            $documents->getCollection()->transform(function($route) {
                $doc = $route->document ?? null;

                return [
                    'doc_id'             => $doc->doc_id ?? null,
                    'doc_tracking'       => $doc->doc_tracking ?? '',
                    'doctype_description'=> $doc->doctype->doctype_description ?? '',
                    'docs_description'   => $doc->docs_description ?? '',
                    'origin_section'     => $doc->origin_section->section_name ?? ($doc->origin_section ?? ''),
                    'origin_fname'       => $doc->origin_fname ?? ($doc->origin_name ?? ''),
                    'route_fromsection'  => $route->route_fromsection ?? '',
                    'route_from'         => $route->route_from ?? '',
                    'route_purpose'      => $route->route_purpose ?? '',
                    'fwd_remarks'        => $route->fwd_remarks ?? '',
                    'datetime_forwarded' => optional($route->datetime_forwarded)->format('Y-m-d H:i:s') ?? ($doc->datetime_posted ?? null),
                    // include route action id for reference
                    'action_id'          => $route->action_id ?? null,
                ];
            });

            // return the paginator with transformed collection (frontend expects pagination)
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
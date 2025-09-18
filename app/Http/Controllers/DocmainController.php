<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;

use App\Models\Docmain;
Use App\Models\Docroutes;
Use App\Models\Sections;
Use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpParser\Comment\Doc;

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
            Log::info("DocmainController@index DEBUG", [
                'user_id' => $user->id,
                'request_url' => $request->fullUrl(),
                'request_path' => $request->path(),
                'route_name' => $request->route()->getName(),
                'route_parameters' => $request->route()->parameters(),
                'query_parameters' => $request->query(),
                'all_parameters' => $request->all(),
            ]);
            // Get the current route name
            $routeName = $request->route()->getName();
            $typeInput = strtolower(
                $request->route('type') ?? $request->get('type', 'incoming')
            );

            $typeMap = [
                'incoming' => 1, '1' => 1,
                'pending'  => 2, '2' => 2,
                'forward'  => 3, '3' => 3,
                'deferred' => 4, '4' => 4,
                'mydocs'   => 5, '5' => 5,
                'keep'     => 6, '6' => 6,
                'release'  => 7, '7' => 7
            ];
            $case = $typeMap[$typeInput] ?? 1; // default to incoming
            Log::info("DocmainController@index called", [
                'user_id' => $user->id,
                'type' => $typeInput,
                'case' => $case,
                'route_name' => $routeName,
            ]);   

            $sortBy = $request->get('sort_by', 'dts_docroutes.action_id');
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
            $sortColumn = $sortMap[$sortBy] ?? 'dts_docroutes.action_id';

            // --- base query: select routes and join docs so we can sort by doc fields safely ---
            $query = Docroutes::select('dts_docroutes.*')
                ->leftJoin('dts_docs', 'dts_docs.doc_id', '=', 'dts_docroutes.document_id')
                ->with(['document.doctype', 'document.origin_section', 'document.origin_office']);
            
            switch ($case) {
                case 1: // incoming
                    // Keep the route filters (qualified column names)
                    $query->where('dts_docroutes.datetime_route_accepted', 0)
                        ->where('dts_docroutes.active', 1)
                        ->where('dts_docroutes.route_accomplished', '=', 0);
                    break;
                case 2: // pending
                    $query->where('dts_docroutes.datetime_route_accepted', '<>', 0)
                        ->where('dts_docroutes.active', 1)
                        ->where('dts_docroutes.route_accomplished', '=', 0);
                    break;
                case 3: // forward
                    $query->where('dts_docroutes.datetime_route_accepted', '<>', 0)
                        ->where('dts_docroutes.active', 1)
                        ->where('dts_docroutes.route_accomplished', '=', 1);
                    break;
                case 4: // deferred
                    $query->where('dts_docroutes.datetime_route_accepted', '<>', 0)
                        ->where('dts_docroutes.active', 1)
                        ->where('dts_docroutes.route_accomplished', '=', 4);
                    break;
                case 5: // mydocs
                    $query->where('dts_docroutes.datetime_route_accepted', 0)
                        ->where('dts_docroutes.active', 1);
                    break;
                case 6: // keep
                    $query->where('dts_docroutes.datetime_route_accepted', '<>', 0)
                        ->where('dts_docroutes.active', 1)
                        ->where('dts_docroutes.route_accomplished', '=', 2);
                    break;
                case 7: // release
                    $query->where('dts_docroutes.datetime_route_accepted', '<>', 0)
                        ->where('dts_docroutes.active', 1)
                        ->where('dts_docroutes.route_accomplished', '=', 3);
                    break;
                default:
                    $query->where('dts_docroutes.datetime_route_accepted', 0)
                        ->where('dts_docroutes.active', 1)
                        ->where('dts_docroutes.route_accomplished', '=', 0);
            }

            // Toggle: personal vs office
            if ($request->has('toggle')) {
                // use boolean() to allow 'true'/'false' strings
                if ($request->boolean('toggle')) {
                    switch ($case) {
                        case 3: $query->where('dts_docroutes.route_fromuser_id', $user->id);
                            break;
                        case 5: $query->where('dts_docroutes.route_fromuser_id', $user->id);
                            break;
                        default: $query->where('dts_docroutes.route_touser_id', $user->id);
                            break;
                    }
                } else {
                    switch ($case) {
                        case 3: $query->where('dts_docroutes.route_fromsection_id', $user->section_id);
                            break;
                        case 5: $query->where('dts_docroutes.route_fromsection_id', $user->section_id);
                            break;
                        default: $query->where('dts_docroutes.route_tosection_id', $user->section_id);
                            break;
                    }
                }
            } else {
                switch ($case) {
                    case 3: $query->where('dts_docroutes.route_fromsection_id', $user->section_id);
                        break;
                    case 5: $query->where('dts_docroutes.origin_section', $user->section_id);
                        break;
                    default: $query->where('dts_docroutes.route_tosection_id', $user->section_id);
                        break;
                }
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

            $sql = $query->toSql();
            $bindings = $query->getBindings();
            $fullSql = vsprintf(str_replace('?', "'%s'", $sql), $bindings);

            Log::info("DocmainController@index SQL DEBUG", [
                'sql' => $sql,
                'bindings' => $bindings,
                'full_sql' => $fullSql,
                'user_id' => $user->id,
                'case' => $case,
                'type' => $typeInput,
            ]);
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
            ])->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
                'Pragma' => 'no-cache',
                'Expires' => '0'
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
            // Get authenticated user
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please log in to submit documents'
                ], 401);
            }

            // Validate the request data
            $validatedData = $request->validate([
                'doc_type_id' => 'required|integer',
                'docs_description' => 'required|string',
                'origin_fname' => 'required|string|max:255',
                'receiving_section' => 'required|integer',
                'actions_needed' => 'required|string',
                'route_purpose' => 'required|string',
                // Optional fields
                'origin_school' => 'nullable|string|max:255',
                'origin_section' => 'nullable|integer',
            ]);

            DB::beginTransaction();

                try {
                    $docmainData = [
                    'doc_type_id' => $validatedData['doc_type_id'],
                    'tempdocs_id' => 0, // Set default value
                    'docs_description' => $validatedData['docs_description'],
                    'origin_fname' => $validatedData['origin_fname'],
                    'origin_userid' => $user->id,
                    'origin_school_id' => $validatedData['origin_school_id'] ?? 1,
                    'origin_school' => $validatedData['origin_school'] ?? 'Regional Office',
                    'origin_section' => $user->section_id,
                    'receiving_section' => $validatedData['receiving_section'],
                    'actions_needed' => $validatedData['actions_needed'],
                    'datetime_posted' => now(),
                    'track_issuedby_userid' => $user->id,
                    'active' => 1,
                    // Set default values for nullable columns
                    'datetime_accepted' =>  null,
                    'acceptedby_userid' => 0,
                    'acct_dvnum' => '',
                    'acct_payee' => '',
                    'acct_particulars' => '',
                    'acct_amount' => 0,
                    'final_actions_made' => '',
                    'updatedby_id' => 0,
                    'archive_id' => 0,
                    'deactivate_reason' => ''
                ];

                Log::info("Creating document", [
                    'user_id' => $user->id,
                    'docmainData' => $docmainData
                ]);

                // Create the document
                $document = Docmain::create($docmainData);
                
                // Update tracking number
                $document->doc_tracking = date('y') . '-' . str_pad($document->doc_id, 3, '0', STR_PAD_LEFT);
                $document->save();

                // Get destination section
                $toSection = Sections::where('section_id', $validatedData['receiving_section'])->first();

                //$fromUser = User::where('id', $request->employee)->first();

                //->update(['doc_tracking' => 'DTS' . str_pad($document->doc_id, 7, '0', STR_PAD_LEFT)]);
                
                // Handle file upload if present
                // if ($request->hasFile('document_file')) {
                //     $file = $request->file('document_file');
                //     $filename = time() . '_' . $file->getClientOriginalName();
                //     $file->storeAs('documents', $filename, 'public');
                    
                //     // You might want to save the file path to the document
                //     $document->update(['file_path' => 'documents/' . $filename]);
                // }

                // Create the initial route entry
                $routeData = [
                    'document_id' => $document->doc_id,
                    'previous_route_id' => 0,
                    'route_fromuser_id' => $user->id,
                    'route_from' => $user->name,
                    'route_fromsection_id' => $user->section_id,
                    'route_fromsection' => $user->section->section_description ?? '',
                    'route_tosection_id' => $validatedData['receiving_section'],
                    'route_tosection' => $toSection->section_description ?? '',
                    'route_touser_id' => 0,
                    'route_purpose' => $validatedData['actions_needed'],
                    'datetime_forwarded' => now(),
                    'datetime_route_accepted' => null,
                    'active' => 1
                ];

                Docroutes::create($routeData);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Document submitted successfully',
                    'data' => $document->load(['doctype', 'origin_section', 'origin_office'])
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error creating document', [
                'message' => $e->getMessage(),
                // 'file' => $e->getFile(),
                // 'line' => $e->getLine(),
                // 'trace' => $e->getTraceAsString(),
                'user_id' => $user->id ?? null,
            ]);

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
    public function markDone($request, $documentId = null): JsonResponse
{
    try {
        // Handle both Request object and direct string parameter
        if ($request instanceof Request) {
            // Called via API endpoint
            $validatedData = $request->validate([
                'actions_taken' => 'required|string'
            ]);
            $actions = $validatedData['actions_taken'];
            $docId = $documentId ?? $request->route('id');
        } else {
            // Called internally with string parameter
            $actions = $request;
            $docId = $documentId;
        }

        if (!$docId) {
            throw new \InvalidArgumentException('Document ID is required');
        }

        $document = Docmain::findOrFail($docId);

        $document->update([
            'done' => 1,
            'final_actions_made' => $actions,
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
    } catch (\Exception $e) {
        Log::error('Error marking document as done', [
            'document_id' => $docId ?? null,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error marking document as done',
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
    /**
 * Accept a document route
 */
public function acceptRoute(Request $request): JsonResponse
{
    try {
        // Log incoming request data
        Log::info('acceptRoute request data:', [
            'action_id' => $request->actionid,
            'user' => auth()->user()
        ]);

        // ✅ Validate request
        $validated = $request->validate([
            'actionid' => 'required|integer|exists:dts_docroutes,action_id',
            'accepting_remarks' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $route = Docroutes::findOrFail($validated['actionid']);

        // ✅ Authorization check
        Log::info('Route details for authorization check', [
            'route_tosection_id' => $route->route_tosection_id,
            'user_section_id' => $user->section_id,
            'route_touser_id' => $route->route_touser_id,
            'user_id' => $user->id
        ]);
        if ($route->route_tosection_id != $user->section_id )
            // && 
            // $route->route_touser_id != 0 && 
            // $route->route_touser_id != $user->id
         {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to accept this document'
            ], 403);
        }

        // ✅ Update the route
        $route->update([
            'datetime_route_accepted' => now(),
            'receivedby_id'           => $user->id,
            'received_by'             => $user->name,
            'route_touser_id'         => $user->id,
            'accepting_remarks'       => $validated['accepting_remarks'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document accepted successfully',
            'data'    => $route->load('document')
        ]);

    } catch (ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Document route not found'
        ], 404);
    } catch (\Exception $e) {
        Log::error('Error accepting document route', [
            'action_id' => $request->actionid,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error accepting document',
            'error' => $e->getMessage(),
            'debug_info' => app()->environment('local') ? [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ] : null
        ], 500);
    }
}

public function keepRoute(Request $request): JsonResponse
{
    try {
        // ✅ Validate request
        $validated = $request->validate([
            'actionid'      => 'required|integer|exists:dts_docroutes,action_id',
            'actions_taken' => 'required|string|max:1000',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $route = Docroutes::findOrFail($validated['actionid']);

        // ✅ Authorization check (section only)
        if ($route->route_tosection_id != $user->section_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to keep this document'
            ], 403);
        }

        // ✅ Update for keeping document
        $route->update([
            'action_datetime'    => now(),
            'actions_taken'      => $validated['actions_taken'],
            'actionby_id'        => $user->id,
            'acted_by'           => $user->name,
            'route_accomplished' => 2,
            'doc_copy'           => 1,
        ]);

        // ✅ Call markDone using remarks + document_id
        if (method_exists($this, 'markDone')) {
            $this->markDone($validated['actions_taken'], $route->document_id);
        }

        // ✅ Log activity
        Log::info('Document kept successfully', [
            'action_id'     => $validated['actionid'],
            'document_id'   => $route->document_id,
            'actions_taken' => $validated['actions_taken'],
            'user_id'       => $user->id,
            'user_name'     => $user->name,
            'section_id'    => $user->section_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document kept successfully',
            'data'    => $route->load('document')
        ]);

    } catch (ModelNotFoundException $e) {
        Log::warning('KeepRoute attempted on missing route', [
            'action_id' => $request->actionid,
            'user_id'   => auth()->id()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Document route not found'
        ], 404);
    } catch (\Exception $e) {
        Log::error('Error keeping document route', [
            'action_id'     => $request->actionid,
            'error'         => $e->getMessage(),
            'user_id'       => auth()->id()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error keeping document',
            'error'   => $e->getMessage()
        ], 500);
    }
}


public function deferredRoute(Request $request): JsonResponse
{
    try {
        Log::info('Deferred route request received', [
            'request' => $request->all(),
            'user_id' => auth()->id()
        ]);

        // ✅ Validate request
        $validated = $request->validate([
            'actionid'      => 'required|integer|exists:dts_docroutes,action_id',
            'actions_taken' => 'required|string|max:1000',
        ]);
        Log::info('Validation passed for deferred route', $validated);

        $user = auth()->user();
        if (!$user) {
            Log::warning('Deferred route attempt without authentication', [
                'actionid' => $request->actionid
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $route = Docroutes::findOrFail($validated['actionid']);
        Log::info('Deferred route found', [
            'actionid' => $route->action_id,
            'doc_id'   => $route->document_id,
            'user_id'  => $user->id
        ]);

        // ✅ Authorization check (section only)
        if ($route->route_tosection_id != $user->section_id) {
            Log::warning('Unauthorized defer attempt', [
                'actionid' => $route->action_id,
                'user_id'  => $user->id,
                'user_section' => $user->section_id,
                'route_section'=> $route->route_tosection_id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to defer this document'
            ], 403);
        }

        // ✅ Update for deferring document
        $route->update([
            'action_datetime'   => now(),
            'actions_taken'     => $validated['actions_taken'],
            'actionby_id'       => $user->id,
            'acted_by'          => $user->name,
            'end_remarks'       => $validated['actions_taken'],
            'def_reason'        => $validated['actions_taken'],
            'def_datetime'      => now(),
            'route_accomplished'=> 4,
            'doc_copy'          => 1,
        ]);
        Log::info('Deferred route updated successfully', [
            'actionid' => $route->action_id,
            'doc_id'   => $route->document_id,
            'user_id'  => $user->id
        ]);

        // ✅ Call markDone using document_id
        if (method_exists($this, 'markDone')) {
            $this->markDone($validated['actions_taken'], $route->document_id);
            Log::info('markDone executed after defer', [
                'actionid' => $route->action_id,
                'doc_id'   => $route->document_id
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Document deferred successfully',
            'data'    => $route->load('document')
        ]);

    } catch (ModelNotFoundException $e) {
        Log::warning('Deferred route not found', [
            'actionid' => $request->actionid,
            'user_id'  => auth()->id()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Document route not found'
        ], 404);
    } catch (\Exception $e) {
        Log::error('Error deferring document route', [
            'actionid' => $request->actionid,
            'error'    => $e->getMessage(),
            'trace'    => $e->getTraceAsString(),
            'user_id'  => auth()->id()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error deferring document',
            'error'   => $e->getMessage()
        ], 500);
    }
}

public function releaseRoute(Request $request): JsonResponse
{
    try {
        // ✅ Validate request
        $validated = $request->validate([
            'actionid'      => 'required|integer|exists:dts_docroutes,action_id',
            'actions_taken' => 'required|string|max:1000',
            'doc_copy'        => 'required|integer|in:0,1',
            'release_to'    => 'required|string|max:255',
            'logbook_page'  => 'required|string|max:255',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $route = Docroutes::findOrFail($validated['actionid']);

        // ✅ Authorization check (section only)
        if ($route->route_tosection_id != $user->section_id) {
            Log::warning('Unauthorized release attempt', [
                'action_id'   => $validated['actionid'],
                'document_id' => $route->document_id,
                'user_id'     => $user->id,
                'user_name'   => $user->name,
                'section_id'  => $user->section_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to release this document'
            ], 403);
        }

        // ✅ Build end remarks
        $endRemarks = "Release to " . $validated['release_to'] . " Ref: " . $validated['logbook_page'];

        // ✅ Update for releasing document
        $route->update([
            'action_datetime'    => now(),
            'actions_taken'      => $validated['actions_taken'],
            'actionby_id'        => $user->id,
            'acted_by'           => $user->name,
            'doc_copy'           => $validated['doc_copy'],
            'out_released_to'    => $validated['release_to'],
            'logbook_page'       => $validated['logbook_page'],
            'route_accomplished' => 3,
            'end_remarks'        => $endRemarks,
        ]);

        // ✅ Call markDone with remarks and document_id
        if (method_exists($this, 'markDone')) {
            $this->markDone($endRemarks, $route->document_id);
        }

        // ✅ Log success
        Log::info('Document released successfully', [
            'action_id'     => $validated['actionid'],
            'document_id'   => $route->document_id,
            'actions_taken' => $validated['actions_taken'],
            'release_to'    => $validated['release_to'],
            'logbook_page'  => $validated['logbook_page'],
            'user_id'       => $user->id,
            'user_name'     => $user->name,
            'section_id'    => $user->section_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document released successfully',
            'data'    => $route->load('document')
        ]);

    } catch (ModelNotFoundException $e) {
        Log::warning('ReleaseRoute attempted on missing route', [
            'action_id' => $request->actionid,
            'user_id'   => auth()->id()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Document route not found'
        ], 404);
    } catch (\Exception $e) {
        Log::error('Error releasing document route', [
            'action_id' => $request->actionid,
            'error'     => $e->getMessage(),
            'user_id'   => auth()->id()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error releasing document',
            'error'   => $e->getMessage()
        ], 500);
    }
}

/**
 * Forward a document to another section/user
 */

public function forwardRoute(Request $request): JsonResponse
{
    try {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        // ✅ Validate request
        $validated = $request->validate([
            'actionid'          => 'required|integer|exists:dts_docroutes,action_id',
            'actions_taken'     => 'required|string|max:1000',
            'doc_copy'        => 'required|integer|in:0,1',
            'route_tosection_id'=> 'required|integer',
            'route_touser_id'   => 'required|integer',
            'fwd_remarks'       => 'nullable|string|max:1000',
        ]);

        $currentRoute = Docroutes::findOrFail($validated['actionid']);

        // ✅ Authorization check (only section)
        if ($currentRoute->route_tosection_id != $user->section_id) {
            Log::warning('Unauthorized forward attempt', [
                'action_id'   => $validated['actionid'],
                'document_id' => $currentRoute->document_id,
                'user_id'     => $user->id,
                'user_name'   => $user->name,
                'section_id'  => $user->section_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to forward this document'
            ], 403);
        }

        DB::beginTransaction();

        try {
            // ✅ Update current route
            $currentRoute->update([
                'actions_datetime'   => now(),
                'actions_taken'      => $validated['actions_taken'],
                'actionby_id'        => $user->id,
                'acted_by'           => $user->name,
                'route_accomplished' => 1,
                'doc_copy'           => $validated['doc_copy'],
                'fwd_remarks'        => $validated['fwd_remarks'],
            ]);

            Log::info('Docroutes updated', [
                'actions_taken'      => $validated['actions_taken'],
                'actionby_id'        => $user->id,
                'acted_by'           => $user->name,
                'route_accomplished' => 1,
                'doc_copy'           => $validated['doc_copy'],
                'fwd_remarks'        => $validated['fwd_remarks'],
            ]);

            // ✅ Create new route
            $toSection = Sections::where('section_id', $validated['route_tosection_id'])->first();
            $newRouteData = [
                'document_id'          => $currentRoute->document_id,
                'previous_route_id'    => $validated['actionid'],
                'route_fromuser_id'    => $user->id,
                'route_from'           => $user->name, // 👈 Always set from user
                'route_fromsection_id' => $user->section_id,
                'route_fromsection'    => $user->section->section_description ?? '(No Section)',
                'route_tosection_id'   => $validated['route_tosection_id'],
                'route_tosection'      => $toSection->section_description ?? '(Unknown Section)',
                'route_touser_id'      => $validated['route_touser_id'],
                'route_purpose'        => $validated['actions_taken'],
                'fwd_remarks'          => $validated['fwd_remarks'] ?? '',
                'datetime_forwarded'   => now(),
                'datetime_route_accepted' => null,
                'active'               => 1,
            ];

            $newRoute = Docroutes::create($newRouteData);

            DB::commit();

            // ✅ Log success
            Log::info('Document forwarded successfully', [
                'action_id'        => $validated['actionid'],
                'document_id'      => $currentRoute->document_id,
                'actions_taken'    => $validated['actions_taken'],
                'route_from_user'  => $user->name, // 👈 explicit user
                'route_from_section'=> $user->section->section_name ?? '(No Section)',
                'route_to_section' => $toSection->section_description ?? '(Unknown Section)',
                'route_purpose'    => $validated['actions_taken'],
                'fwd_remarks'      => $validated['fwd_remarks'] ?? '',
                'user_id'          => $user->id,
                'user_name'        => $user->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document forwarded successfully',
                'data'    => $newRoute->load('document')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('DB transaction failed during forwardRoute', [
                'action_id'   => $validated['actionid'],
                'document_id' => $currentRoute->document_id ?? null,
                'error'       => $e->getMessage(),
                'user_id'     => $user->id,
            ]);

            throw $e;
        }

    } catch (ModelNotFoundException $e) {
        Log::warning('ForwardRoute attempted on missing route', [
            'action_id' => $request->actionid,
            'user_id'   => auth()->id()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Document route not found'
        ], 404);
    } catch (ValidationException $e) {
        Log::warning('ForwardRoute validation failed', [
            'errors'  => $e->errors(),
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error forwarding document route', [
            'action_id' => $request->actionid,
            'error'     => $e->getMessage(),
            'user_id'   => auth()->id()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error forwarding document',
            'error'   => $e->getMessage()
        ], 500);
    }
}

}
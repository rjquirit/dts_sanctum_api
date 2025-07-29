<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Docmain;
use App\Models\Doctypes;
use App\Models\Sections;
use App\Models\Offices;
use App\Models\User;
use App\Models\Docroutes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use DB;

class DocmainController extends Controller
{
    public function index(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search', '');
        $status = $request->input('status', 'is_done');
        $isUserDocs = $authUser->user_type == 'teacher' ? 'personal' : $request->input('scope', 'personal');

        $filters = [
            'personal' => [
                'route_column' => 'route_touser_id',
                'origin_column' => 'origin_userid',
                'receivedby_column' => 'receivedby_id',
                'scope_value' => 'personal',
                'auth_value' => $authUser->id
            ],
            'office' => [
                'route_column' => 'route_tosection_id',
                'origin_column' => 'origin_section',
                'receivedby_column' => 'receivedby_id',
                'scope_value' => 'office',
                'auth_value' => $authUser->section_id
            ]
        ];

        $filter = $filters[$isUserDocs] ?? $filters['personal'];

        $latestActions = Docroutes::selectRaw('MAX(action_id) as max_action_id')
            ->groupBy('document_id');

        $maindocQuery = Docroutes::with(['document.doctype','document.origin_office', 'document.origin_section','toUser'])
            ->whereIn('action_id', function ($query) use ($latestActions) {
                $query->select('max_action_id')->fromSub($latestActions, 'latest');
            });

        if ($search) {
            $maindocQuery->whereExists(function ($query) use ($search) {
                $query->select(DB::raw(1))
                    ->from('dts_docs')
                    ->whereColumn('dts_docs.doc_id', 'dts_docroutes.document_id')
                    ->where(function ($q) use ($search) {
                        $q->where('dts_docs.doc_tracking', 'like', "%{$search}%")
                        ->orWhere('dts_docs.docs_description', 'like', "%{$search}%")
                        ->orWhereExists(function ($subquery) use ($search) {
                            $subquery->select(DB::raw(1))
                                ->from('dts_docstype')
                                ->whereColumn('dts_docstype.doctype_id', 'dts_docs.doc_type_id')
                                ->where('dts_docstype.doctype_description', 'like', "%{$search}%");
                        });
                    });
            });
        }

        if ($status !== 'all') {
            $maindocQuery->whereExists(function ($query) use ($status, $filter) {
                $query->select(DB::raw(1))
                    ->from('dts_docs')
                    ->whereColumn('dts_docs.doc_id', 'dts_docroutes.document_id');

                if ($status === 'is_done') {
                    $query->where($filter['origin_column'], $filter['auth_value']);
                } elseif ($status === 'is_pending') {
                    $query->where('done', 0)
                        ->where($filter['receivedby_column'], '!=', 0)
                        ->where('dts_docroutes.' . $filter['route_column'], $filter['auth_value']);
                } else {
                    $query->where($filter['receivedby_column'], 0)
                        ->where('dts_docroutes.' . $filter['route_column'], $filter['auth_value']);
                }
            });
        }

        $maindoc = $maindocQuery
            ->orderBy('datetime_forwarded', 'DESC')
            ->orderByDesc('action_id')
            ->paginate(10);

        $totals = (object) [
            'total_docs' => Docmain::where($filter['origin_column'], $filter['auth_value'])->count(),
            'total_incoming' => Docroutes::where($filter['route_column'], $filter['auth_value'])
                                ->where('receivedby_id', 0)
                                ->distinct('document_id')
                                ->count('document_id'),
            'total_pending' => Docroutes::where($filter['route_column'], $filter['auth_value'])
                                ->where('receivedby_id', '!=', 0)
                                ->where('route_accomplished', 0)
                                ->distinct('document_id')
                                ->count('document_id')
        ];

        return response()->json([
            'maindoc' => $maindoc,
            'filters' => ['search' => $search],
            'is_user_docs' => $filter['scope_value'],
            'total_incoming' => $totals->total_incoming ?? 0,
            'total_pending' => $totals->total_pending ?? 0,
            'total_docs' => $totals->total_docs ?? 0,
            'status' => $status
        ]);
    }

    public function create()
    {
        $authUser = Auth::user();
        $division = Offices::when($authUser->division_code != 1, function($query) use ($authUser) {
            return $query->where('sch_id', $authUser->division_code)
                ->orWhere('district', $authUser->division_code)
                ->orWhere('sch_id', 1);
        })->get();

        $divisiondistrict = $authUser->division_code != 1 
            ? $division->where('sch_id', $authUser->division_code)->pluck('district')->toArray()
            : [];

        $sections = Sections::when($authUser->division_code != 1, function($query) use ($authUser, $divisiondistrict) {
            return $query->where('active', 1)
                ->where('office_id', $authUser->division_code)
                ->orWhereIn('office_id', $divisiondistrict)
                ->orWhere('office_id', 1);
        })->get();

        $employees = User::when($authUser->division_code != 1, function($query) use ($authUser, $division) {
            $divisionid = $division->where('district', $authUser->division_code)->pluck('sch_id')->toArray();
            $divisiondistrict = $division->where('sch_id', $authUser->division_code)->pluck('district')->toArray();
            
            return $query->where('division_code', $authUser->division_code)
                ->orWhereIn('division_code', $divisionid)
                ->orWhereIn('division_code', $divisiondistrict)
                ->orWhere('division_code', 1);
        })
        ->where('active', 1)
        ->get()
        ->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'active' => $employee->active,
                'section_id' => $employee->section_id,
                'division_code' => $employee->division_code,
                'user_type' => $employee->user_type,
            ];
        });

        return response()->json([
            'doctypes' => Doctypes::where('active', 1)->get(),
            'divisions' => $division,
            'sections' => $sections,
            'employee' => $employees,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'doc_type_id' => 'required|exists:dts_docstype,doctype_id',
                'docs_description' => 'required|string',
                'receiving_section' => 'required|exists:dts_sections,section_id',
                'employee' => 'required',
                'actions_needed' => 'required|string',
            ]);

            $document = Docmain::create([
                'track_issuedby_userid' => auth()->id(),
                'doc_type_id' => $request->doc_type_id,
                'docs_description' => trim($request->docs_description),
                'origin_name' => auth()->user()->name,
                'origin_userid' => auth()->id(),
                'origin_section' => auth()->user()->section_id,
                'receiving_section' => $request->receiving_section,
                'actions_needed' => trim($request->actions_needed),
                'datetime_posted' => now(),
                'active' => 1,
            ]);

            $fromSection = Sections::where('section_id', auth()->user()->section_id)->first();
            $toSection = Sections::where('section_id', $request->receiving_section)->first();

            $doc_routes = Docroutes::create([
                'document_id' => $document->doc_id,
                'route_fromuser_id' => auth()->id(),
                'route_from' => auth()->user()->name,
                'route_fromsection_id' => auth()->user()->section_id,
                'route_fromsection' => $fromSection->section_description,
                'route_tosection_id' => $request->receiving_section,
                'route_tosection' => $toSection->section_description,
                'route_touser_id' => $request->employee,
                'route_purpose' => trim($request->actions_needed),
                'datetime_forwarded' => now(),
                'active' => 1,
            ]);

            return response()->json([
                'message' => 'Document created successfully',
                'action_id' => $doc_routes->action_id,
                'document' => $document
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating document', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Failed to create document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $route = Docroutes::with(['document.doctype'])
            ->where('action_id', $id)
            ->orderBy('action_id', 'desc')
            ->first();

        if (!$route || !$route->document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        $document = $route->document;
        $sender = User::with('section', 'office')
            ->where('id', $document->origin_userid)
            ->first();

        $receiver = User::with('section')
            ->where('id', $document->receiving_userid)
            ->first();

        $document->sender = [
            'name' => $sender->name,
            'section' => $sender->section,
            'office' => $sender->office
        ];
        
        $document->receiver = $receiver;
        $document->formatted_date = date('F j, Y g:i A', strtotime($document->datetime_posted));

        return response()->json([
            'docroutes' => $route,
            'document' => $document
        ]);
    }

    public function updateStatus(Request $request)
    {
        try {
            $doc = Docmain::where("doc_id", $request->id)->firstOrFail();
            $route = DocRoutes::where("action_id", $request->action_id)->firstOrFail();

            $route->route_accomplished = 2;
            $route->save();
            $doc->done = 1;
            $doc->save();

            return response()->json([
                'message' => 'Document status updated successfully',
                'document' => $doc,
                'route' => $route
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update document status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id, $action_id)
    {
        try {
            $document = Docmain::where('doc_id', $id)->firstOrFail();
            $route = DocRoutes::where('action_id', $action_id)->firstOrFail();
            $section = Sections::where('section_id', $request->receiving_section)->firstOrFail();

            $validatedData = $request->validate([
                'doc_type_id' => 'required',
                'docs_description' => 'required',
                'actions_needed' => 'required',
                'receiving_section' => 'required',
                'employee' => 'required',
            ]);

            $document->update([
                'doc_type_id' => $validatedData['doc_type_id'],
                'docs_description' => $validatedData['docs_description'],
                'actions_needed' => $validatedData['actions_needed'],
            ]);

            $route->update([
                'route_tosection_id' => $validatedData['receiving_section'],
                'route_touser_id' => $validatedData['employee'],
                'route_tosection' => $section->section_description
            ]);

            return response()->json([
                'message' => 'Document updated successfully',
                'document' => $document,
                'route' => $route
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update document',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

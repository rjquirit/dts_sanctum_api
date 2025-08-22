<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Docroutes;
use App\Models\Docmain;
use App\Models\Sections;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DocroutesController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Docroutes::with(['document.doctype', 'fromUser', 'fromSection', 'toUser', 'toSection'])
                ->where('route_touser_id', Auth::id())
                ->orWhere('route_tosection_id', Auth::user()->section_id);

            if ($search = $request->input('search')) {
                $query->whereHas('document', function($q) use ($search) {
                    $q->where('doc_tracking', 'like', "%{$search}%")
                      ->orWhere('docs_description', 'like', "%{$search}%");
                });
            }

            if ($status = $request->input('status')) {
                $query->where('route_accomplished', $status);
            }

            $routes = $query->orderBy('datetime_forwarded', 'desc')
                ->paginate($request->input('per_page', 10));

            return response()->json($routes);
        } catch (\Exception $e) {
            Log::error('Error fetching document routes:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to fetch document routes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($document, $actionId)
    {
        try {
            $route = Docroutes::with([
                'document.doctype',
                'fromUser',
                'fromSection',
                'toUser',
                'toSection'
            ])->where('document_id', $document)
              ->where('action_id', $actionId)
              ->firstOrFail();

            return response()->json($route);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Document route not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function forward(Request $request, $documentId)
    {
        try {
            $validated = $request->validate([
                'to_section_id' => 'required|exists:dts_sections,section_id',
                'to_user_id' => 'required|exists:users,id',
                'purpose' => 'required|string'
            ]);

            $document = Docmain::findOrFail($documentId);
            $fromSection = Sections::findOrFail(Auth::user()->section_id);
            $toSection = Sections::findOrFail($validated['to_section_id']);
            $toUser = User::findOrFail($validated['to_user_id']);

            // Mark previous route as accomplished
            Docroutes::where('document_id', $documentId)
                ->where('route_touser_id', Auth::id())
                ->update(['route_accomplished' => 1]);

            // Create new route
            $route = Docroutes::create([
                'document_id' => $documentId,
                'route_fromuser_id' => Auth::id(),
                'route_from' => Auth::user()->name,
                'route_fromsection_id' => Auth::user()->section_id,
                'route_fromsection' => $fromSection->section_description,
                'route_tosection_id' => $validated['to_section_id'],
                'route_tosection' => $toSection->section_description,
                'route_touser_id' => $validated['to_user_id'],
                'route_purpose' => $validated['purpose'],
                'datetime_forwarded' => now(),
                'active' => 1,
                'route_accomplished' => 0
            ]);

            return response()->json([
                'message' => 'Document forwarded successfully',
                'route' => $route->load(['document', 'fromUser', 'fromSection', 'toUser', 'toSection'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error forwarding document:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to forward document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function receive(Request $request)
    {
        try {
            $validated = $request->validate([
                'action_id' => 'required|exists:dts_docroutes,action_id'
            ]);

            $route = Docroutes::findOrFail($validated['action_id']);

            if ($route->route_touser_id != Auth::id() && $route->route_tosection_id != Auth::user()->section_id) {
                return response()->json([
                    'message' => 'Unauthorized to receive this document'
                ], 403);
            }

            $route->update([
                'receivedby_id' => Auth::id(),
                'datetime_received' => now()
            ]);

            return response()->json([
                'message' => 'Document received successfully',
                'route' => $route->load(['document', 'fromUser', 'fromSection', 'toUser', 'toSection'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error receiving document:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to receive document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function reRoute($actionId)
    {
        try {
            $route = Docroutes::with(['document', 'fromUser', 'fromSection', 'toUser', 'toSection'])
                ->findOrFail($actionId);

            $sections = Sections::where('active', 1)->get();
            $users = User::where('active', 1)->get();

            return response()->json([
                'route' => $route,
                'sections' => $sections,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch re-route information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function reRouteUpdate(Request $request)
    {
        try {
            $validated = $request->validate([
                'action_id' => 'required|exists:dts_docroutes,action_id',
                'to_section_id' => 'required|exists:dts_sections,section_id',
                'to_user_id' => 'required|exists:users,id',
                'purpose' => 'required|string'
            ]);

            $route = Docroutes::findOrFail($validated['action_id']);
            $toSection = Sections::findOrFail($validated['to_section_id']);

            $route->update([
                'route_tosection_id' => $validated['to_section_id'],
                'route_tosection' => $toSection->section_description,
                'route_touser_id' => $validated['to_user_id'],
                'route_purpose' => $validated['purpose'],
                'datetime_forwarded' => now(),
                'receivedby_id' => null,
                'datetime_received' => null
            ]);

            return response()->json([
                'message' => 'Document re-routed successfully',
                'route' => $route->load(['document', 'fromUser', 'fromSection', 'toUser', 'toSection'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error re-routing document:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to re-route document',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

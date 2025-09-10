<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Docmain;
use App\Models\Docroutes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DocumentTrackingController extends Controller
{
    public function track(Request $request)
    {
        try {
            
            // $validated = $request->validate([
            //     'doc_id' => 'required'
            // ]);
            // Get the main document id


            $document = Docmain::where('doc_tracking', $request['doc_id'])
                ->with(['doctype', 'origin_office', 'origin_section'])
                ->first();

            

            if (!$document) {
                return response()->json([
                    'message' => 'Document not found'
                ], 404);
                
                Log::error('Error tracking document:', [
                'doc_id' =>$document->doc_id,
                ]);
            }
            
            // Get the document route history
            $routes = Docroutes::where('document_id', $document->doc_id)
                ->with(['fromUser', 'fromSection', 'toUser', 'toSection'])
                ->orderBy('datetime_forwarded', 'desc')
                ->get();

            // Calculate document statistics
            // $stats = [
            //     'total_forwards' => $routes->count(),
            //     'days_in_process' => now()->diffInDays($document->datetime_posted),
            //     'current_location' => $routes->first() ? [
            //         'section' => $routes->first()->toSection?->section_description,
            //         'user' => $routes->first()->toUser?->name,
            //         'date' => $routes->first()->datetime_forwarded
            //     ] : null,
            //     'is_completed' => $document->done == 1
            // ];

            return response()->json([
                'document' => $document,
                'routes' => $routes,
               // 'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error tracking document:', [
                'error' => $e->getMessage(),
                'doc_id' => $request['doc_id'],
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to track document',
                'doc_id' =>  $request['doc_id'],
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

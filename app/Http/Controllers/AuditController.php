<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\AuditItem;

class AuditController extends Controller
{
    public function index(Request $request, ?Project $project = null)
    {
        $projectId = $request->input('project_id', $project?->id);
        
        $itemsByCategory = [];
        $score = 0;

        if ($projectId) {
            $items = AuditItem::where('project_id', $projectId)->get();
            $itemsByCategory = $items->groupBy('category');
            
            if ($items->isNotEmpty()) {
                $score = 100;
                foreach ($items as $item) {
                    if ($item->status === 'fail') {
                        $score -= match($item->severity) { 'critical'=>20, 'high'=>10, 'medium'=>5, 'low'=>2, default=>0 };
                    } elseif ($item->status === 'partial') {
                        $score -= match($item->severity) { 'critical'=>10, 'high'=>5, 'medium'=>2, 'low'=>1, default=>0 };
                    }
                }
                $score = max(0, min(100, $score));
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'itemsByCategory' => $itemsByCategory,
                'score' => $score
            ]);
        }

        $projects = Project::orderBy('domain')->get();
        return view('audit.index', compact('projects', 'projectId', 'itemsByCategory', 'score'));
    }

    public function updateStatus(Request $request, AuditItem $item)
    {
        $status = $request->input('status');
        $item->update([
            'status' => $status,
            'last_checked_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Audit item updated.',
            'item' => $item->refresh()
        ]);
    }

    public function updateNotes(Request $request, AuditItem $item)
    {
        $notes = $request->input('notes');
        $item->update(['notes' => $notes]);
        
        return response()->json([
            'success' => true,
            'message' => 'Notes updated.',
            'item' => $item->refresh()
        ]);
    }

    public function seed(Request $request)
    {
        $projectId = $request->input('project_id');
        if (!$projectId) {
            return response()->json(['success' => false, 'message' => 'Select a project first.'], 400);
        }

        $items = [
            ['category'=>'Application Security','item_name'=>'APP_DEBUG=false in production','severity'=>'critical'],
            ['category'=>'Application Security','item_name'=>'APP_ENV=production set','severity'=>'high'],
            ['category'=>'Application Security','item_name'=>'No stack traces exposed publicly','severity'=>'critical'],
            ['category'=>'Application Security','item_name'=>'HTTPS enforced','severity'=>'high'],
            ['category'=>'Application Security','item_name'=>'CSRF protection active','severity'=>'critical'],
            ['category'=>'Application Security','item_name'=>'XSS protection headers set','severity'=>'high'],
            ['category'=>'Application Security','item_name'=>'Content Security Policy header','severity'=>'medium'],
            ['category'=>'Authentication','item_name'=>'Strong password policy enforced','severity'=>'high'],
            ['category'=>'Authentication','item_name'=>'Rate limiting on login','severity'=>'high'],
            ['category'=>'Authentication','item_name'=>'Account lockout after failed attempts','severity'=>'medium'],
            ['category'=>'Data Protection','item_name'=>'.env file not publicly accessible','severity'=>'critical'],
            ['category'=>'Data Protection','item_name'=>'Database backups running','severity'=>'high'],
            ['category'=>'Data Protection','item_name'=>'API keys not in repository','severity'=>'critical'],
            ['category'=>'Dependencies','item_name'=>'Composer packages up to date','severity'=>'medium'],
            ['category'=>'Dependencies','item_name'=>'No known vulnerable packages','severity'=>'high'],
            ['category'=>'Infrastructure','item_name'=>'File permissions correct (644 files, 755 dirs)','severity'=>'medium'],
            ['category'=>'Infrastructure','item_name'=>'Upload directory PHP execution disabled','severity'=>'high'],
            ['category'=>'Infrastructure','item_name'=>'Error logging to file not screen','severity'=>'medium'],
        ];
        
        foreach ($items as $item) {
            AuditItem::firstOrCreate(
                ['project_id' => $projectId, 'item_name' => $item['item_name']],
                array_merge($item, ['status' => 'unchecked', 'project_id' => $projectId])
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Audit checklist loaded.'
        ]);
    }
}

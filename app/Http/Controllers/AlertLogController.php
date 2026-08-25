<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AlertLog;

class AlertLogController extends Controller
{
    public function index(Request $request)
    {
        $filterType = $request->input('filterType');

        $query = AlertLog::with(['project', 'incident'])
            ->when($filterType, function ($q) use ($filterType) {
                $q->where('alert_type', $filterType);
            })
            ->orderByDesc('created_at');

        $logs = $query->paginate(25);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $logs->items(),
                'meta' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'total' => $logs->total(),
                    'per_page' => $logs->perPage()
                ]
            ]);
        }

        return view('alerts.index', compact('logs', 'filterType'));
    }
}

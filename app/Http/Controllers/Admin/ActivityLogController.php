<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Tampilkan log aktivitas admin, terbaru terlebih dahulu.
     */
    public function index(): View
    {
        $logs = ActivityLog::query()
            ->with(['user', 'subject'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.activity-logs.index', [
            'logs' => $logs,
        ]);
    }
}

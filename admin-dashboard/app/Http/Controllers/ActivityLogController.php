<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ActivityLogController extends Controller
{
    /**
     * List activity logs with filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ActivityLog::with(['user:id,name,email', 'admin:id,name,email']);

        // Filter by action
        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        // Filter by admin
        if ($adminId = $request->input('admin_id')) {
            $query->where('admin_id', $adminId);
        }

        // Filter by user
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        // Search in description
        if ($search = $request->input('search')) {
            $query->where('description', 'like', "%{$search}%");
        }

        // Date range
        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }
        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->input('to'));
        }

        // Sorting
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy('created_at', $sortOrder);

        // Pagination
        $perPage = min($request->input('per_page', 25), 100);
        $logs = $query->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * Get logs for a specific user.
     */
    public function userLogs(Request $request, int $userId): JsonResponse
    {
        $query = ActivityLog::with(['admin:id,name,email'])
            ->where('user_id', $userId)
            ->orWhere('admin_id', $userId);

        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy('created_at', $sortOrder);

        $perPage = min($request->input('per_page', 25), 100);
        $logs = $query->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * Get available action types.
     */
    public function actions(): JsonResponse
    {
        $actions = ActivityLog::distinct()
            ->pluck('action')
            ->sort()
            ->values();

        return response()->json($actions);
    }
}

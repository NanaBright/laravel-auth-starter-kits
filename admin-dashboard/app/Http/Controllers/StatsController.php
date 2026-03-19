<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\StatsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function __construct(
        protected StatsService $statsService
    ) {}

    /**
     * Get dashboard statistics.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'stats' => $this->statsService->getDashboardStats(),
            'registration_trends' => $this->statsService->getRegistrationTrends(14),
            'user_status' => $this->statsService->getUsersByStatus(),
        ]);
    }

    /**
     * Get detailed registration statistics.
     */
    public function registrations(Request $request): JsonResponse
    {
        $days = min($request->input('days', 30), 365);

        return response()->json([
            'daily' => $this->statsService->getRegistrationTrends($days),
            'monthly' => $this->statsService->getMonthlyGrowth(12),
        ]);
    }

    /**
     * Get activity statistics.
     */
    public function activity(Request $request): JsonResponse
    {
        $days = min($request->input('days', 30), 365);

        return response()->json([
            'login_trends' => $this->statsService->getLoginTrends($days),
            'action_distribution' => $this->statsService->getActionDistribution($days),
            'top_admins' => $this->statsService->getTopAdmins(5),
        ]);
    }
}

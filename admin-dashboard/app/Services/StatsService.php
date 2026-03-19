<?php

namespace App\Services;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StatsService
{
    /**
     * Get dashboard statistics.
     */
    public function getDashboardStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'verified_users' => User::verified()->count(),
            'admin_users' => User::admins()->count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
            'new_users_this_month' => User::where('created_at', '>=', now()->subMonth())->count(),
            'active_today' => User::whereDate('last_login_at', today())->count(),
        ];
    }

    /**
     * Get registration trends.
     */
    public function getRegistrationTrends(int $days = 30): Collection
    {
        $startDate = now()->subDays($days)->startOfDay();
        
        $users = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $trends = collect();
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $trends->push([
                'date' => $date,
                'count' => $users->get($date)?->count ?? 0,
            ]);
        }

        return $trends;
    }

    /**
     * Get login activity trends.
     */
    public function getLoginTrends(int $days = 30): Collection
    {
        $startDate = now()->subDays($days)->startOfDay();
        
        $logins = ActivityLog::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('action', 'login')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $trends = collect();
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $trends->push([
                'date' => $date,
                'count' => $logins->get($date)?->count ?? 0,
            ]);
        }

        return $trends;
    }

    /**
     * Get user growth by month.
     */
    public function getMonthlyGrowth(int $months = 12): Collection
    {
        $startDate = now()->subMonths($months)->startOfMonth();
        
        $users = User::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $growth = collect();
        for ($i = $months; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $growth->push([
                'month' => $month,
                'label' => now()->subMonths($i)->format('M Y'),
                'count' => $users->get($month)?->count ?? 0,
            ]);
        }

        return $growth;
    }

    /**
     * Get action distribution.
     */
    public function getActionDistribution(int $days = 30): Collection
    {
        return ActivityLog::selectRaw('action, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('action')
            ->orderByDesc('count')
            ->get();
    }

    /**
     * Get top active admins.
     */
    public function getTopAdmins(int $limit = 5): Collection
    {
        return User::select('users.id', 'users.name', 'users.email')
            ->selectRaw('COUNT(activity_logs.id) as action_count')
            ->leftJoin('activity_logs', 'users.id', '=', 'activity_logs.admin_id')
            ->where('users.is_admin', true)
            ->where('activity_logs.created_at', '>=', now()->subDays(30))
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('action_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get users by registration source (if tracking).
     */
    public function getUsersByStatus(): array
    {
        $total = User::count();
        
        if ($total === 0) {
            return [
                ['status' => 'Active & Verified', 'count' => 0, 'percentage' => 0],
                ['status' => 'Active & Unverified', 'count' => 0, 'percentage' => 0],
                ['status' => 'Inactive', 'count' => 0, 'percentage' => 0],
            ];
        }

        $activeVerified = User::active()->verified()->count();
        $activeUnverified = User::active()->whereNull('email_verified_at')->count();
        $inactive = User::where('is_active', false)->count();

        return [
            [
                'status' => 'Active & Verified',
                'count' => $activeVerified,
                'percentage' => round(($activeVerified / $total) * 100, 1),
            ],
            [
                'status' => 'Active & Unverified',
                'count' => $activeUnverified,
                'percentage' => round(($activeUnverified / $total) * 100, 1),
            ],
            [
                'status' => 'Inactive',
                'count' => $inactive,
                'percentage' => round(($inactive / $total) * 100, 1),
            ],
        ];
    }
}

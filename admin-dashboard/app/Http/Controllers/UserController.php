<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    /**
     * List users with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by admin
        if ($request->has('is_admin')) {
            $query->where('is_admin', $request->boolean('is_admin'));
        }

        // Filter by verified
        if ($request->has('verified')) {
            if ($request->boolean('verified')) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        // Date range
        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }
        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->input('to'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->input('per_page', 15), 100);
        $users = $query->paginate($perPage);

        return response()->json($users);
    }

    /**
     * Get single user details.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'is_active' => $user->is_active,
                'email_verified_at' => $user->email_verified_at,
                'last_login_at' => $user->last_login_at,
                'login_count' => $user->login_count,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'recent_activity' => $user->activityLogs()
                ->latest()
                ->limit(10)
                ->get(['action', 'description', 'created_at']),
        ]);
    }

    /**
     * Create new user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'is_admin' => ['boolean'],
            'is_active' => ['boolean'],
            'send_welcome_email' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => $validated['is_admin'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'email_verified_at' => $request->boolean('mark_verified') ? now() : null,
        ]);

        // Log activity
        ActivityLog::log(
            'user_created',
            "Created user {$user->name} ({$user->email})",
            $user->id,
            $request->user()->id,
            ['created_by_admin' => true]
        );

        return response()->json([
            'message' => 'User created successfully.',
            'user' => $user,
        ], 201);
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'password' => ['sometimes', Password::defaults()],
            'is_admin' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $changes = [];

        if (isset($validated['name']) && $validated['name'] !== $user->name) {
            $changes['name'] = ['from' => $user->name, 'to' => $validated['name']];
            $user->name = $validated['name'];
        }

        if (isset($validated['email']) && $validated['email'] !== $user->email) {
            $changes['email'] = ['from' => $user->email, 'to' => $validated['email']];
            $user->email = $validated['email'];
        }

        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $changes['password'] = 'changed';
        }

        if (isset($validated['is_admin']) && $validated['is_admin'] !== $user->is_admin) {
            $changes['is_admin'] = ['from' => $user->is_admin, 'to' => $validated['is_admin']];
            $user->is_admin = $validated['is_admin'];
        }

        if (isset($validated['is_active']) && $validated['is_active'] !== $user->is_active) {
            $changes['is_active'] = ['from' => $user->is_active, 'to' => $validated['is_active']];
            $user->is_active = $validated['is_active'];
        }

        $user->save();

        // Log activity
        if (!empty($changes)) {
            ActivityLog::log(
                'user_updated',
                "Updated user {$user->name} ({$user->email})",
                $user->id,
                $request->user()->id,
                ['changes' => $changes]
            );
        }

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user,
        ]);
    }

    /**
     * Delete user.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        // Prevent self-deletion
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Cannot delete your own account.',
            ], 422);
        }

        $name = $user->name;
        $email = $user->email;
        $userId = $user->id;

        $user->delete();

        // Log activity
        ActivityLog::log(
            'user_deleted',
            "Deleted user {$name} ({$email})",
            null,
            $request->user()->id,
            ['deleted_user_id' => $userId, 'deleted_user_email' => $email]
        );

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    /**
     * Bulk delete users.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
        ]);

        // Exclude current admin from deletion
        $ids = array_filter($validated['ids'], fn($id) => $id !== $request->user()->id);

        if (empty($ids)) {
            return response()->json([
                'message' => 'No valid users to delete.',
            ], 422);
        }

        $users = User::whereIn('id', $ids)->get(['id', 'name', 'email']);
        $count = User::whereIn('id', $ids)->delete();

        // Log activity
        ActivityLog::log(
            'bulk_delete',
            "Bulk deleted {$count} users",
            null,
            $request->user()->id,
            ['deleted_users' => $users->toArray()]
        );

        return response()->json([
            'message' => "{$count} users deleted successfully.",
            'count' => $count,
        ]);
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        // Prevent self-deactivation
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Cannot change your own status.',
            ], 422);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';

        // Log activity
        ActivityLog::log(
            "user_{$status}",
            "{ucfirst($status)} user {$user->name} ({$user->email})",
            $user->id,
            $request->user()->id
        );

        return response()->json([
            'message' => "User {$status} successfully.",
            'is_active' => $user->is_active,
        ]);
    }

    /**
     * Export users to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = User::query();

        // Apply same filters as index
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('is_admin')) {
            $query->where('is_admin', $request->boolean('is_admin'));
        }

        // Log activity
        ActivityLog::log(
            'users_exported',
            'Exported users list to CSV',
            null,
            $request->user()->id
        );

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users_' . date('Y-m-d_His') . '.csv"',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'ID',
                'Name',
                'Email',
                'Admin',
                'Active',
                'Email Verified',
                'Last Login',
                'Login Count',
                'Created At',
            ]);

            $query->chunk(100, function ($users) use ($handle) {
                foreach ($users as $user) {
                    fputcsv($handle, [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->is_admin ? 'Yes' : 'No',
                        $user->is_active ? 'Yes' : 'No',
                        $user->email_verified_at?->format('Y-m-d H:i:s') ?? 'No',
                        $user->last_login_at?->format('Y-m-d H:i:s') ?? 'Never',
                        $user->login_count,
                        $user->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Resend verification email.
     */
    public function resendVerification(Request $request, User $user): JsonResponse
    {
        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'User is already verified.',
            ], 422);
        }

        // Here you would trigger email verification notification
        // $user->sendEmailVerificationNotification();

        // Log activity
        ActivityLog::log(
            'verification_resent',
            "Resent verification email to {$user->email}",
            $user->id,
            $request->user()->id
        );

        return response()->json([
            'message' => 'Verification email sent.',
        ]);
    }

    /**
     * Mark user as verified.
     */
    public function markVerified(Request $request, User $user): JsonResponse
    {
        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'User is already verified.',
            ], 422);
        }

        $user->email_verified_at = now();
        $user->save();

        // Log activity
        ActivityLog::log(
            'manually_verified',
            "Manually verified {$user->name} ({$user->email})",
            $user->id,
            $request->user()->id
        );

        return response()->json([
            'message' => 'User marked as verified.',
        ]);
    }
}

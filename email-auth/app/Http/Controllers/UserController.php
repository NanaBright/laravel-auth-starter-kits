<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Get the authenticated user.
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
                'message' => 'User retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'exception' => $e,
            ]);
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'retrieval_failed',
                    'message' => 'Failed to retrieve user information.',
                ],
            ], 500);
        }
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $validated = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->id],
            ]);

            $emailChanged = isset($validated['email']) && $validated['email'] !== $user->email;
            
            if ($emailChanged) {
                $validated['email_verified_at'] = null;
            }

            $user->update($validated);

            if ($emailChanged) {
                $user->sendEmailVerificationNotification();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
                'message' => 'Profile updated successfully.' . ($emailChanged ? ' Please verify your new email address.' : ''),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'validation_failed',
                    'message' => 'The provided data is invalid.',
                    'details' => $e->errors(),
                ],
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update user: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'exception' => $e,
            ]);
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'update_failed',
                    'message' => 'Failed to update profile.',
                ],
            ], 500);
        }
    }

    /**
     * Delete the authenticated user's account.
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Revoke all tokens
            $user->tokens()->delete();
            
            // Delete the user
            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete user: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'exception' => $e,
            ]);
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'deletion_failed',
                    'message' => 'Failed to delete account.',
                ],
            ], 500);
        }
    }
}

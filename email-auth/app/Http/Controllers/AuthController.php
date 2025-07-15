<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMagicLinkRequest;
use App\Http\Requests\VerifyMagicLinkRequest;
use App\Models\User;
use App\Services\MagicLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function __construct(
        protected MagicLinkService $magicLinkService
    ) {}

    /**
     * Send a magic link to the user's email address.
     */
    public function sendMagicLink(SendMagicLinkRequest $request): JsonResponse
    {
        $email = $request->email;
        
        // Check rate limiting
        $key = 'magic-link:' . $email;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'rate_limited',
                    'message' => 'Too many requests. Please try again in ' . $seconds . ' seconds.',
                    'retry_after' => $seconds,
                ],
            ], 429);
        }

        try {
            $result = $this->magicLinkService->sendMagicLink($email);
            
            RateLimiter::hit($key, 60); // 1 minute decay
            
            return response()->json([
                'success' => true,
                'data' => [
                    'email' => $email,
                    'expires_in' => config('auth.magic_links.expiry', 15) * 60, // Convert to seconds
                    'resend_after' => 60,
                ],
                'message' => 'Magic link sent successfully to your email address.',
            ]);
        } catch (\Exception $e) {
            Log::error('Magic link send failed: ' . $e->getMessage(), [
                'email' => $email,
                'exception' => $e,
            ]);
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'send_failed',
                    'message' => 'Failed to send magic link. Please try again.',
                ],
            ], 500);
        }
    }

    /**
     * Verify a magic link token and authenticate the user.
     */
    public function verifyMagicLink(VerifyMagicLinkRequest $request): JsonResponse
    {
        $email = $request->email;
        $token = $request->token;
        
        // Check rate limiting for verification attempts
        $key = 'magic-link-verify:' . $email;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'rate_limited',
                    'message' => 'Too many verification attempts. Please try again in ' . $seconds . ' seconds.',
                    'retry_after' => $seconds,
                ],
            ], 429);
        }

        try {
            $result = $this->magicLinkService->verifyMagicLink($email, $token);
            
            if (!$result['success']) {
                RateLimiter::hit($key, 60);
                
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => $result['error_code'],
                        'message' => $result['message'],
                    ],
                ], 422);
            }

            // Clear rate limiting on successful verification
            RateLimiter::clear($key);
            
            $user = $result['user'];
            $token = $user->createToken('auth-token')->plainTextToken;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'name' => $user->name,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ],
                    'action' => $result['action'], // 'registered' or 'authenticated'
                ],
                'message' => 'Authentication successful.',
            ]);
        } catch (\Exception $e) {
            Log::error('Magic link verification failed: ' . $e->getMessage(), [
                'email' => $email,
                'exception' => $e,
            ]);
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'verification_failed',
                    'message' => 'Failed to verify magic link. Please try again.',
                ],
            ], 500);
        }
    }

    /**
     * Send email verification link.
     */
    public function sendEmailVerification(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'already_verified',
                    'message' => 'Email address is already verified.',
                ],
            ], 422);
        }

        // Check rate limiting
        $key = 'email-verification:' . $user->email;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'rate_limited',
                    'message' => 'Too many requests. Please try again in ' . $seconds . ' seconds.',
                    'retry_after' => $seconds,
                ],
            ], 429);
        }

        try {
            $user->sendEmailVerificationNotification();
            
            RateLimiter::hit($key, 60);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'expires_in' => config('auth.verification.expire', 60) * 60, // Convert to seconds
                    'resend_after' => 60,
                ],
                'message' => 'Email verification link sent successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Email verification send failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
                'exception' => $e,
            ]);
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'send_failed',
                    'message' => 'Failed to send verification email. Please try again.',
                ],
            ], 500);
        }
    }

    /**
     * Logout the authenticated user.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'exception' => $e,
            ]);
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'logout_failed',
                    'message' => 'Failed to logout. Please try again.',
                ],
            ], 500);
        }
    }
}

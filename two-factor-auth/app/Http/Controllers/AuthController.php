<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], Response::HTTP_CREATED);
    }

    /**
     * Login step 1: Validate credentials.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Rate limiting
        $key = 'login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => ["Too many login attempts. Try again in {$seconds} seconds."],
            ]);
        }

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        RateLimiter::clear($key);

        // Check if 2FA is enabled
        if ($user->hasTwoFactorEnabled()) {
            // Store user ID in session for 2FA verification
            session(['two_factor_user_id' => $user->id]);

            return response()->json([
                'two_factor_required' => true,
                'message' => 'Two-factor authentication required.',
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'two_factor_required' => false,
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Login step 2: Verify 2FA code.
     */
    public function twoFactor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'recovery' => 'boolean',
        ]);

        $userId = session('two_factor_user_id');

        if (!$userId) {
            return response()->json([
                'message' => 'No two-factor authentication session.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::find($userId);

        if (!$user) {
            session()->forget('two_factor_user_id');
            return response()->json([
                'message' => 'User not found.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Rate limiting for 2FA attempts
        $key = 'two_factor:' . $userId;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => "Too many attempts. Try again in {$seconds} seconds.",
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $isValid = false;

        if ($validated['recovery'] ?? false) {
            // Verify recovery code
            $recoveryService = app(\App\Services\RecoveryCodeService::class);
            $isValid = $recoveryService->verify($user, $validated['code']);
        } else {
            // Verify TOTP code
            $twoFactorService = app(\App\Services\TwoFactorService::class);
            $isValid = $twoFactorService->verify($user, $validated['code']);
        }

        if (!$isValid) {
            RateLimiter::hit($key, 300);
            return response()->json([
                'message' => 'Invalid verification code.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        RateLimiter::clear($key);
        session()->forget('two_factor_user_id');

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => $user,
            'two_factor_enabled' => $user->hasTwoFactorEnabled(),
        ]);
    }
}

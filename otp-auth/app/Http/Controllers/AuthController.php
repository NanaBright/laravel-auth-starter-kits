<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\User;
use App\Services\BackupCodeService;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        protected OtpService $otpService,
        protected BackupCodeService $backupCodeService
    ) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Generate backup codes
        $backupCodes = $this->backupCodeService->generate($user);

        // Send OTP for verification
        $result = $this->otpService->send($user, $request->preferred_channel);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please verify your account.',
            'user_id' => $user->id,
            'backup_codes' => $this->backupCodeService->formatForDisplay($backupCodes),
            'otp' => $result,
        ], 201);
    }

    /**
     * Login user (request OTP).
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $identifier = $request->identifier;
        
        // Find user by email or phone
        $user = User::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // Send OTP
        $result = $this->otpService->send($user, $request->channel);

        if (!$result['success']) {
            return response()->json($result, 429);
        }

        return response()->json($result);
    }

    /**
     * Verify OTP and complete login.
     */
    public function verify(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->otpService->verify($request->identifier, $request->code);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        $user = $result['user'];
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Verify using backup code.
     */
    public function verifyBackupCode(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'code' => 'required|string',
        ]);

        $user = User::where('email', $request->identifier)
            ->orWhere('phone', $request->identifier)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $result = $this->backupCodeService->verify($user, $request->code);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'user' => $result['user'],
            'token' => $token,
            'remaining_backup_codes' => $result['remaining_codes'],
        ]);
    }

    /**
     * Resend OTP.
     */
    public function resend(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'channel' => 'nullable|string|in:email,sms',
        ]);

        $user = User::where('email', $request->identifier)
            ->orWhere('phone', $request->identifier)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $result = $this->otpService->send($user, $request->channel);

        if (!$result['success']) {
            return response()->json($result, 429);
        }

        return response()->json($result);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user' => $request->user(),
        ]);
    }

    /**
     * Get user's backup codes count.
     */
    public function getBackupCodes(Request $request): JsonResponse
    {
        $remaining = $this->backupCodeService->getRemainingCount($request->user());

        return response()->json([
            'success' => true,
            'remaining_codes' => $remaining,
            'has_backup_codes' => $remaining > 0,
        ]);
    }

    /**
     * Regenerate backup codes.
     */
    public function regenerateBackupCodes(Request $request): JsonResponse
    {
        $codes = $this->backupCodeService->generate($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Backup codes regenerated successfully.',
            'backup_codes' => $this->backupCodeService->formatForDisplay($codes),
        ]);
    }
}

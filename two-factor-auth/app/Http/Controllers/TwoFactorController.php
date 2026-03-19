<?php

namespace App\Http\Controllers;

use App\Services\RecoveryCodeService;
use App\Services\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorController extends Controller
{
    public function __construct(
        protected TwoFactorService $twoFactorService,
        protected RecoveryCodeService $recoveryCodeService
    ) {}

    /**
     * Enable 2FA (step 1: get secret and QR code).
     */
    public function enable(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => 'Two-factor authentication is already enabled.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $this->twoFactorService->enable($user);

        return response()->json([
            'secret' => $data['secret'],
            'qr_code_svg' => $data['qr_code_svg'],
            'message' => 'Scan the QR code with your authenticator app, then confirm with a code.',
        ]);
    }

    /**
     * Confirm 2FA setup (step 2: verify code).
     */
    public function confirm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => 'Two-factor authentication is already enabled.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$user->hasTwoFactorPending()) {
            return response()->json([
                'message' => 'No two-factor setup pending. Please enable 2FA first.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $data = $this->twoFactorService->confirm($user, $validated['code'], $this->recoveryCodeService);

            return response()->json([
                'message' => 'Two-factor authentication enabled successfully.',
                'recovery_codes' => $data['recovery_codes'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Disable 2FA.
     */
    public function disable(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (!$user->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $this->twoFactorService->disable($user, $validated['password']);

            return response()->json([
                'message' => 'Two-factor authentication disabled.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Get recovery codes.
     */
    public function recoveryCodes(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'recovery_codes' => $user->getRecoveryCodes(),
            'count' => $this->recoveryCodeService->getRemainingCount($user),
        ]);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (!$user->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!password_verify($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid password.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $codes = $this->recoveryCodeService->generate($user);

        return response()->json([
            'recovery_codes' => $codes,
            'message' => 'Recovery codes regenerated. Previous codes are no longer valid.',
        ]);
    }

    /**
     * Get 2FA status.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'enabled' => $user->hasTwoFactorEnabled(),
            'pending' => $user->hasTwoFactorPending(),
            'recovery_codes_remaining' => $user->hasTwoFactorEnabled() 
                ? $this->recoveryCodeService->getRemainingCount($user) 
                : 0,
        ]);
    }
}

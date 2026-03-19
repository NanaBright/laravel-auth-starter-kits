<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SocialAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class SocialController extends Controller
{
    public function __construct(
        protected SocialAuthService $socialAuthService
    ) {}

    /**
     * Redirect to the OAuth provider.
     */
    public function redirect(string $provider): RedirectResponse|JsonResponse
    {
        if (!$this->socialAuthService->isProviderSupported($provider)) {
            return response()->json([
                'message' => 'Provider not supported.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return Socialite::driver($provider)
            ->stateless()
            ->redirect();
    }

    /**
     * Handle the OAuth callback.
     */
    public function callback(string $provider): RedirectResponse
    {
        if (!$this->socialAuthService->isProviderSupported($provider)) {
            return redirect('/login?error=provider_not_supported');
        }

        try {
            $socialUser = Socialite::driver($provider)
                ->stateless()
                ->user();

            if (!$socialUser->getEmail()) {
                return redirect('/login?error=email_required');
            }

            $user = $this->socialAuthService->findOrCreateUser($provider, $socialUser);

            // Create API token
            $token = $user->createToken('social-auth')->plainTextToken;

            // Redirect to frontend with token
            return redirect('/auth/callback?token=' . $token);
        } catch (\Exception $e) {
            report($e);
            return redirect('/login?error=authentication_failed');
        }
    }

    /**
     * Link a social account to the authenticated user.
     */
    public function link(Request $request, string $provider): RedirectResponse|JsonResponse
    {
        if (!$this->socialAuthService->isProviderSupported($provider)) {
            return response()->json([
                'message' => 'Provider not supported.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Store intent in session
        session(['social_link' => true]);

        return Socialite::driver($provider)
            ->stateless()
            ->redirect();
    }

    /**
     * Handle the link callback.
     */
    public function linkCallback(Request $request, string $provider): RedirectResponse
    {
        if (!$this->socialAuthService->isProviderSupported($provider)) {
            return redirect('/settings/accounts?error=provider_not_supported');
        }

        try {
            $socialUser = Socialite::driver($provider)
                ->stateless()
                ->user();

            $user = $request->user();

            if (!$user) {
                return redirect('/login?error=authentication_required');
            }

            $this->socialAuthService->linkAccount($user, $provider, $socialUser);

            return redirect('/settings/accounts?success=account_linked');
        } catch (\Exception $e) {
            report($e);
            return redirect('/settings/accounts?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Get the authenticated user's connected accounts.
     */
    public function connectedAccounts(Request $request): JsonResponse
    {
        $accounts = $request->user()->socialAccounts()->get(['id', 'provider', 'nickname', 'avatar', 'created_at']);

        return response()->json([
            'accounts' => $accounts,
            'providers' => $this->socialAuthService->getSupportedProviders(),
        ]);
    }

    /**
     * Disconnect a social account.
     */
    public function disconnect(Request $request, int $accountId): JsonResponse
    {
        try {
            $this->socialAuthService->unlinkAccount($request->user(), $accountId);

            return response()->json([
                'message' => 'Account disconnected successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Logout the user.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}

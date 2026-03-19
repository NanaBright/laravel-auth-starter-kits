<?php

namespace App\Services;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialAuthService
{
    /**
     * Supported OAuth providers.
     */
    protected array $supportedProviders = [
        'google',
        'github',
        'facebook',
        'twitter',
    ];

    /**
     * Check if a provider is supported.
     */
    public function isProviderSupported(string $provider): bool
    {
        return in_array($provider, $this->supportedProviders);
    }

    /**
     * Get the list of supported providers.
     */
    public function getSupportedProviders(): array
    {
        return $this->supportedProviders;
    }

    /**
     * Find or create a user from social provider data.
     */
    public function findOrCreateUser(string $provider, SocialiteUser $socialUser): User
    {
        return DB::transaction(function () use ($provider, $socialUser) {
            // Check if we have an existing social account
            $socialAccount = SocialAccount::where('provider', $provider)
                ->where('provider_id', $socialUser->getId())
                ->first();

            if ($socialAccount) {
                // Update tokens
                $this->updateSocialAccount($socialAccount, $socialUser);
                return $socialAccount->user;
            }

            // Check if user exists with this email
            $user = User::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                // Create new user
                $user = $this->createUser($socialUser);
            }

            // Create social account for this user
            $this->createSocialAccount($user, $provider, $socialUser);

            return $user;
        });
    }

    /**
     * Link a social account to an existing user.
     */
    public function linkAccount(User $user, string $provider, SocialiteUser $socialUser): SocialAccount
    {
        // Check if this provider is already linked to another account
        $existingAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($existingAccount && $existingAccount->user_id !== $user->id) {
            throw new \Exception('This social account is already linked to another user.');
        }

        // Check if user already has this provider
        $userAccount = $user->getSocialAccount($provider);
        
        if ($userAccount) {
            $this->updateSocialAccount($userAccount, $socialUser);
            return $userAccount;
        }

        return $this->createSocialAccount($user, $provider, $socialUser);
    }

    /**
     * Unlink a social account from a user.
     */
    public function unlinkAccount(User $user, int $accountId): bool
    {
        $account = $user->socialAccounts()->find($accountId);

        if (!$account) {
            throw new \Exception('Social account not found.');
        }

        // Ensure user has at least one way to log in
        if ($user->socialAccounts()->count() <= 1) {
            throw new \Exception('Cannot disconnect the only linked account.');
        }

        return $account->delete();
    }

    /**
     * Create a new user from social data.
     */
    protected function createUser(SocialiteUser $socialUser): User
    {
        return User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'email' => $socialUser->getEmail(),
            'avatar' => $socialUser->getAvatar(),
            'email_verified_at' => now(), // Social emails are considered verified
        ]);
    }

    /**
     * Create a social account for a user.
     */
    protected function createSocialAccount(User $user, string $provider, SocialiteUser $socialUser): SocialAccount
    {
        return $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'provider_token' => $socialUser->token,
            'provider_refresh_token' => $socialUser->refreshToken ?? null,
            'provider_token_expires_at' => isset($socialUser->expiresIn) 
                ? now()->addSeconds($socialUser->expiresIn) 
                : null,
            'avatar' => $socialUser->getAvatar(),
            'nickname' => $socialUser->getNickname(),
        ]);
    }

    /**
     * Update an existing social account.
     */
    protected function updateSocialAccount(SocialAccount $account, SocialiteUser $socialUser): void
    {
        $account->update([
            'provider_token' => $socialUser->token,
            'provider_refresh_token' => $socialUser->refreshToken ?? $account->provider_refresh_token,
            'provider_token_expires_at' => isset($socialUser->expiresIn) 
                ? now()->addSeconds($socialUser->expiresIn) 
                : null,
            'avatar' => $socialUser->getAvatar(),
            'nickname' => $socialUser->getNickname(),
        ]);

        // Update user avatar if changed
        if ($socialUser->getAvatar()) {
            $account->user->update(['avatar' => $socialUser->getAvatar()]);
        }
    }
}

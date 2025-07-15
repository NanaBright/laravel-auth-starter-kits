<?php

namespace App\Services;

use App\Models\MagicLink;
use App\Models\User;
use App\Notifications\MagicLinkNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MagicLinkService
{
    /**
     * Send a magic link to the specified email address.
     */
    public function sendMagicLink(string $email): array
    {
        return DB::transaction(function () use ($email) {
            // Find or create user
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $this->generateNameFromEmail($email),
                    'password' => Hash::make(Str::random(32)), // Random password
                ]
            );

            // Create magic link
            $plainTextToken = Str::random(64);
            $magicLink = $user->magicLinks()->create([
                'token' => hash('sha256', $plainTextToken),
                'expires_at' => now()->addMinutes(config('auth.magic_links.expiry', 15)),
            ]);

            // Send notification
            try {
                $user->notify(new MagicLinkNotification($plainTextToken, $magicLink->expires_at));
                
                Log::info('Magic link sent successfully', [
                    'user_id' => $user->id,
                    'email' => $email,
                    'expires_at' => $magicLink->expires_at,
                ]);
                
                return [
                    'success' => true,
                    'user' => $user,
                    'magic_link' => $magicLink,
                ];
            } catch (\Exception $e) {
                Log::error('Failed to send magic link notification', [
                    'user_id' => $user->id,
                    'email' => $email,
                    'exception' => $e->getMessage(),
                ]);
                
                throw $e;
            }
        });
    }

    /**
     * Verify a magic link token and authenticate the user.
     */
    public function verifyMagicLink(string $email, string $token): array
    {
        return DB::transaction(function () use ($email, $token) {
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                return [
                    'success' => false,
                    'error_code' => 'invalid_token',
                    'message' => 'Invalid magic link token.',
                ];
            }

            $hashedToken = hash('sha256', $token);
            $magicLink = $user->magicLinks()
                ->where('token', $hashedToken)
                ->first();

            if (!$magicLink) {
                return [
                    'success' => false,
                    'error_code' => 'invalid_token',
                    'message' => 'Invalid magic link token.',
                ];
            }

            if ($magicLink->isExpired()) {
                return [
                    'success' => false,
                    'error_code' => 'expired_token',
                    'message' => 'Magic link has expired. Please request a new one.',
                ];
            }

            if ($magicLink->isUsed()) {
                return [
                    'success' => false,
                    'error_code' => 'used_token',
                    'message' => 'Magic link has already been used.',
                ];
            }

            // Mark magic link as used
            $magicLink->markAsUsed();
            
            // Determine if this is a new user or existing user
            $isNewUser = $user->created_at->gt(now()->subMinutes(1));
            
            // Mark email as verified if not already
            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            Log::info('Magic link verified successfully', [
                'user_id' => $user->id,
                'email' => $email,
                'is_new_user' => $isNewUser,
            ]);

            return [
                'success' => true,
                'user' => $user,
                'action' => $isNewUser ? 'registered' : 'authenticated',
            ];
        });
    }

    /**
     * Generate a name from an email address.
     */
    private function generateNameFromEmail(string $email): string
    {
        $localPart = Str::before($email, '@');
        
        // Replace common separators with spaces and title case
        $name = Str::title(str_replace(['.', '_', '-'], ' ', $localPart));
        
        return $name ?: 'User';
    }

    /**
     * Clean up expired magic links.
     */
    public function cleanupExpiredLinks(): int
    {
        return MagicLink::where('expires_at', '<', now())->delete();
    }
}

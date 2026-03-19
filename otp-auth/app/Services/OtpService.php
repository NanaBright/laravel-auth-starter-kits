<?php

namespace App\Services;

use App\Models\OtpVerification;
use App\Models\User;
use App\Models\UserChannel;
use App\Notifications\OtpNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OtpService
{
    /**
     * Generate and send OTP to user.
     */
    public function send(User $user, ?string $channel = null): array
    {
        $channel = $channel ?? $this->getPrimaryChannel($user);
        $identifier = $this->getIdentifierForChannel($user, $channel);
        
        if (!$identifier) {
            return [
                'success' => false,
                'message' => "No {$channel} configured for this user.",
            ];
        }

        // Check rate limiting
        if ($this->isRateLimited($identifier)) {
            return [
                'success' => false,
                'message' => 'Too many OTP requests. Please try again later.',
                'retry_after' => $this->getRetryAfter($identifier),
            ];
        }

        // Invalidate any existing OTPs
        $this->invalidateExisting($identifier);

        // Generate new OTP
        $code = $this->generateCode();
        $expiresAt = now()->addMinutes(config('otp.code.expiry', 10));

        // Store OTP
        $otp = OtpVerification::create([
            'user_id' => $user->id,
            'identifier' => $identifier,
            'channel' => $channel,
            'code' => Hash::make($code),
            'expires_at' => $expiresAt,
            'attempts' => 0,
        ]);

        // Send OTP
        $sent = $this->deliverOtp($user, $channel, $identifier, $code);

        if (!$sent) {
            // Try fallback channel
            $fallback = config('otp.channels.fallback');
            if ($fallback && $fallback !== $channel) {
                $fallbackIdentifier = $this->getIdentifierForChannel($user, $fallback);
                if ($fallbackIdentifier) {
                    $sent = $this->deliverOtp($user, $fallback, $fallbackIdentifier, $code);
                    if ($sent) {
                        $otp->update([
                            'channel' => $fallback,
                            'identifier' => $fallbackIdentifier,
                        ]);
                    }
                }
            }
        }

        if (!$sent) {
            $otp->delete();
            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
            ];
        }

        // Log if enabled
        if (config('otp.logging.enabled')) {
            Log::channel(config('otp.logging.channel', 'daily'))->info('OTP sent', [
                'user_id' => $user->id,
                'channel' => $channel,
                'identifier' => $this->maskIdentifier($identifier),
                'code' => config('otp.logging.log_codes') ? $code : '[REDACTED]',
            ]);
        }

        return [
            'success' => true,
            'message' => "OTP sent to your {$channel}.",
            'channel' => $channel,
            'identifier' => $this->maskIdentifier($identifier),
            'expires_in' => config('otp.code.expiry', 10) * 60,
        ];
    }

    /**
     * Verify OTP code.
     */
    public function verify(string $identifier, string $code): array
    {
        $otp = OtpVerification::forIdentifier($identifier)
            ->pending()
            ->latest()
            ->first();

        if (!$otp) {
            return [
                'success' => false,
                'message' => 'No pending OTP found. Please request a new one.',
            ];
        }

        if ($otp->hasExceededAttempts()) {
            return [
                'success' => false,
                'message' => 'Too many failed attempts. Please request a new OTP.',
            ];
        }

        if ($otp->isExpired()) {
            return [
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.',
            ];
        }

        if (!Hash::check($code, $otp->code)) {
            $otp->incrementAttempts();
            $remainingAttempts = config('otp.rate_limiting.max_verify_attempts', 10) - $otp->attempts;
            
            return [
                'success' => false,
                'message' => 'Invalid OTP code.',
                'remaining_attempts' => max(0, $remainingAttempts),
            ];
        }

        $otp->markAsVerified();

        // Update user verification status
        $user = $otp->user;
        if ($otp->channel === 'email' && !$user->hasVerifiedEmail()) {
            $user->update(['email_verified_at' => now()]);
        }
        if ($otp->channel === 'sms' && !$user->hasVerifiedPhone()) {
            $user->update(['phone_verified_at' => now()]);
        }

        return [
            'success' => true,
            'message' => 'OTP verified successfully.',
            'user' => $user,
        ];
    }

    /**
     * Generate OTP code.
     */
    protected function generateCode(): string
    {
        $length = config('otp.code.length', 6);
        $type = config('otp.code.type', 'numeric');

        return match ($type) {
            'numeric' => str_pad((string) random_int(0, (int) pow(10, $length) - 1), $length, '0', STR_PAD_LEFT),
            'alpha' => Str::upper(Str::random($length)),
            'alphanumeric' => Str::upper(Str::random($length)),
            default => str_pad((string) random_int(0, (int) pow(10, $length) - 1), $length, '0', STR_PAD_LEFT),
        };
    }

    /**
     * Deliver OTP via specified channel.
     */
    protected function deliverOtp(User $user, string $channel, string $identifier, string $code): bool
    {
        try {
            if ($channel === 'email') {
                $user->notify(new OtpNotification($code, 'email'));
                return true;
            }

            if ($channel === 'sms') {
                return app(SmsService::class)->send($identifier, $this->formatSmsMessage($code));
            }

            return false;
        } catch (\Exception $e) {
            Log::error('OTP delivery failed', [
                'channel' => $channel,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get primary channel for user.
     */
    protected function getPrimaryChannel(User $user): string
    {
        $primaryChannel = $user->getPrimaryChannel();
        
        if ($primaryChannel) {
            return $primaryChannel->type;
        }

        return config('otp.channels.primary', 'email');
    }

    /**
     * Get identifier for a channel.
     */
    protected function getIdentifierForChannel(User $user, string $channel): ?string
    {
        if ($channel === 'email') {
            return $user->email;
        }

        if ($channel === 'sms') {
            return $user->phone;
        }

        return null;
    }

    /**
     * Invalidate existing OTPs for identifier.
     */
    protected function invalidateExisting(string $identifier): void
    {
        OtpVerification::forIdentifier($identifier)
            ->pending()
            ->delete();
    }

    /**
     * Check if identifier is rate limited.
     */
    protected function isRateLimited(string $identifier): bool
    {
        if (!config('otp.rate_limiting.enabled', true)) {
            return false;
        }

        $recentCount = OtpVerification::forIdentifier($identifier)
            ->where('created_at', '>=', now()->subMinutes(config('otp.rate_limiting.decay_minutes', 10)))
            ->count();

        return $recentCount >= config('otp.rate_limiting.max_send_attempts', 5);
    }

    /**
     * Get retry after time.
     */
    protected function getRetryAfter(string $identifier): int
    {
        $oldest = OtpVerification::forIdentifier($identifier)
            ->where('created_at', '>=', now()->subMinutes(config('otp.rate_limiting.decay_minutes', 10)))
            ->oldest()
            ->first();

        if (!$oldest) {
            return 0;
        }

        $availableAt = $oldest->created_at->addMinutes(config('otp.rate_limiting.decay_minutes', 10));
        
        return max(0, $availableAt->diffInSeconds(now()));
    }

    /**
     * Mask identifier for display.
     */
    protected function maskIdentifier(string $identifier): string
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $parts = explode('@', $identifier);
            $name = $parts[0];
            $domain = $parts[1];
            $maskedName = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 4)) . substr($name, -2);
            return $maskedName . '@' . $domain;
        }

        // Phone number
        return substr($identifier, 0, 3) . str_repeat('*', max(0, strlen($identifier) - 6)) . substr($identifier, -3);
    }

    /**
     * Format SMS message with code.
     */
    protected function formatSmsMessage(string $code): string
    {
        return str_replace(
            [':code', ':minutes'],
            [$code, config('otp.code.expiry', 10)],
            config('otp.messages.sms', 'Your verification code is: :code. Valid for :minutes minutes.')
        );
    }
}

<?php

namespace App\Services;

use App\Models\BackupCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BackupCodeService
{
    /**
     * Generate backup codes for a user.
     */
    public function generate(User $user): array
    {
        // Delete existing backup codes
        $user->backupCodes()->delete();

        $count = config('otp.backup_codes.count', 10);
        $length = config('otp.backup_codes.length', 8);
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $plainCode = $this->generateCode($length);
            $codes[] = $plainCode;

            BackupCode::create([
                'user_id' => $user->id,
                'code' => Hash::make($plainCode),
            ]);
        }

        return $codes;
    }

    /**
     * Verify a backup code.
     */
    public function verify(User $user, string $code): array
    {
        $backupCodes = $user->backupCodes()->unused()->get();

        foreach ($backupCodes as $backupCode) {
            if ($backupCode->verify($code)) {
                $backupCode->markAsUsed();

                $remainingCodes = $user->backupCodes()->unused()->count();

                return [
                    'success' => true,
                    'message' => 'Backup code verified successfully.',
                    'remaining_codes' => $remainingCodes,
                    'user' => $user,
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Invalid backup code.',
        ];
    }

    /**
     * Get remaining backup codes count.
     */
    public function getRemainingCount(User $user): int
    {
        return $user->backupCodes()->unused()->count();
    }

    /**
     * Check if user has backup codes.
     */
    public function hasBackupCodes(User $user): bool
    {
        return $user->backupCodes()->unused()->exists();
    }

    /**
     * Generate a single backup code.
     */
    protected function generateCode(int $length): string
    {
        $format = config('otp.backup_codes.format', 'alphanumeric');

        return match ($format) {
            'numeric' => str_pad((string) random_int(0, (int) pow(10, $length) - 1), $length, '0', STR_PAD_LEFT),
            'alpha' => Str::upper(Str::random($length)),
            'alphanumeric' => Str::upper(Str::random($length)),
            default => Str::upper(Str::random($length)),
        };
    }

    /**
     * Format codes for display (with dashes for readability).
     */
    public function formatForDisplay(array $codes): array
    {
        return array_map(function ($code) {
            if (strlen($code) === 8) {
                return substr($code, 0, 4) . '-' . substr($code, 4);
            }
            return $code;
        }, $codes);
    }
}

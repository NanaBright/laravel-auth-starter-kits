<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class RecoveryCodeService
{
    /**
     * Generate recovery codes for a user.
     */
    public function generate(User $user): array
    {
        $count = config('two-factor.recovery_codes_count', 8);
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = $this->generateCode();
        }

        $user->setRecoveryCodes($codes);

        return $codes;
    }

    /**
     * Generate a single recovery code.
     */
    protected function generateCode(): string
    {
        return Str::upper(Str::random(4)) . '-' . Str::upper(Str::random(4));
    }

    /**
     * Verify and use a recovery code.
     */
    public function verify(User $user, string $code): bool
    {
        $normalizedCode = Str::upper(str_replace(' ', '', $code));

        return $user->useRecoveryCode($normalizedCode);
    }

    /**
     * Get the remaining recovery codes count.
     */
    public function getRemainingCount(User $user): int
    {
        return count($user->getRecoveryCodes());
    }
}

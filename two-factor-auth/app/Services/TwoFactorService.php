<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate a new secret key for a user.
     */
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Get the QR code URL for the user.
     */
    public function getQrCodeUrl(User $user, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
    }

    /**
     * Generate QR code SVG.
     */
    public function generateQrCodeSvg(string $qrCodeUrl): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($qrCodeUrl);
    }

    /**
     * Verify a TOTP code.
     */
    public function verify(User $user, string $code): bool
    {
        $secret = $user->getTwoFactorSecret();

        if (!$secret) {
            return false;
        }

        $window = config('two-factor.window', 1);

        return $this->google2fa->verifyKey($secret, $code, $window);
    }

    /**
     * Enable 2FA for a user (generate secret and QR code).
     */
    public function enable(User $user): array
    {
        $secret = $this->generateSecret();
        $user->setTwoFactorSecret($secret);

        $qrCodeUrl = $this->getQrCodeUrl($user, $secret);
        $qrCodeSvg = $this->generateQrCodeSvg($qrCodeUrl);

        return [
            'secret' => $secret,
            'qr_code_svg' => $qrCodeSvg,
        ];
    }

    /**
     * Confirm 2FA setup with a valid code.
     */
    public function confirm(User $user, string $code, RecoveryCodeService $recoveryCodeService): array
    {
        if (!$this->verify($user, $code)) {
            throw new \Exception('Invalid verification code.');
        }

        $user->confirmTwoFactor();

        // Generate recovery codes
        $recoveryCodes = $recoveryCodeService->generate($user);

        return [
            'recovery_codes' => $recoveryCodes,
        ];
    }

    /**
     * Disable 2FA for a user.
     */
    public function disable(User $user, string $password): void
    {
        if (!password_verify($password, $user->password)) {
            throw new \Exception('Invalid password.');
        }

        $user->disableTwoFactor();
    }
}

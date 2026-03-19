<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
        'provider_token_expires_at',
        'avatar',
        'nickname',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'provider_token',
        'provider_refresh_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'provider_token_expires_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this social account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the token is expired.
     */
    public function isTokenExpired(): bool
    {
        if (!$this->provider_token_expires_at) {
            return false;
        }

        return $this->provider_token_expires_at->isPast();
    }

    /**
     * Update the OAuth tokens.
     */
    public function updateTokens(
        string $token,
        ?string $refreshToken = null,
        ?int $expiresIn = null
    ): void {
        $this->update([
            'provider_token' => $token,
            'provider_refresh_token' => $refreshToken ?? $this->provider_refresh_token,
            'provider_token_expires_at' => $expiresIn 
                ? now()->addSeconds($expiresIn) 
                : null,
        ]);
    }
}

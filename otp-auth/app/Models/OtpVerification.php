<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'identifier',
        'channel',
        'code',
        'expires_at',
        'verified_at',
        'attempts',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * Get the user that owns the OTP verification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the OTP has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the OTP is still valid.
     */
    public function isValid(): bool
    {
        return !$this->isExpired() && is_null($this->verified_at);
    }

    /**
     * Check if max attempts reached.
     */
    public function hasExceededAttempts(): bool
    {
        return $this->attempts >= config('otp.rate_limiting.max_verify_attempts', 10);
    }

    /**
     * Increment the attempts counter.
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    /**
     * Mark as verified.
     */
    public function markAsVerified(): void
    {
        $this->update(['verified_at' => now()]);
    }

    /**
     * Scope for pending (unverified, not expired) OTPs.
     */
    public function scopePending($query)
    {
        return $query->whereNull('verified_at')
                     ->where('expires_at', '>', now());
    }

    /**
     * Scope for a specific identifier.
     */
    public function scopeForIdentifier($query, string $identifier)
    {
        return $query->where('identifier', $identifier);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MagicLink extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'used_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Get the user that owns this magic link.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the magic link has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    /**
     * Check if the magic link has been used.
     */
    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    /**
     * Mark the magic link as used.
     */
    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    /**
     * Scope to get only valid (not expired and not used) magic links.
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now())
                    ->whereNull('used_at');
    }
}

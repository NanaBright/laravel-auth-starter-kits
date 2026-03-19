<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserChannel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'identifier',
        'is_primary',
        'is_verified',
        'verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_primary' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the user that owns the channel.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is an email channel.
     */
    public function isEmail(): bool
    {
        return $this->type === 'email';
    }

    /**
     * Check if this is an SMS channel.
     */
    public function isSms(): bool
    {
        return $this->type === 'sms';
    }

    /**
     * Mark as verified.
     */
    public function markAsVerified(): void
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }

    /**
     * Set as primary channel.
     */
    public function setAsPrimary(): void
    {
        // Remove primary from other channels
        $this->user->channels()
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);
        
        $this->update(['is_primary' => true]);
    }

    /**
     * Scope for verified channels.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for primary channel.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope for a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}

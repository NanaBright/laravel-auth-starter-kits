<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class BackupCode extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'code',
        'used_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'used_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'code',
    ];

    /**
     * Get the user that owns the backup code.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the code has been used.
     */
    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    /**
     * Mark the code as used.
     */
    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    /**
     * Verify a code against this backup code.
     */
    public function verify(string $code): bool
    {
        return Hash::check($code, $this->code);
    }

    /**
     * Scope for unused codes.
     */
    public function scopeUnused($query)
    {
        return $query->whereNull('used_at');
    }
}

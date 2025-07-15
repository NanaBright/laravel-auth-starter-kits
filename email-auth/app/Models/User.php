<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the magic links for this user.
     */
    public function magicLinks()
    {
        return $this->hasMany(MagicLink::class);
    }

    /**
     * Create a new magic link for this user.
     */
    public function createMagicLink(): MagicLink
    {
        // Delete any existing magic links for this user
        $this->magicLinks()->delete();
        
        return $this->magicLinks()->create([
            'token' => hash('sha256', $plainTextToken = \Illuminate\Support\Str::random(64)),
            'expires_at' => now()->addMinutes(config('auth.magic_links.expiry', 15)),
        ]);
    }

    /**
     * Find a magic link by token.
     */
    public function findMagicLink(string $token): ?MagicLink
    {
        $hashedToken = hash('sha256', $token);
        
        return $this->magicLinks()
            ->where('token', $hashedToken)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Mark the email as verified.
     */
    public function markEmailAsVerified(): void
    {
        $this->update(['email_verified_at' => now()]);
    }
}

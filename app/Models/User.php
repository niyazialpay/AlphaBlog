<?php

namespace App\Models;

use App\Models\PersonalNotes\PersonalNoteCategories;
use App\Models\PersonalNotes\PersonalNotes;
use App\Models\Post\Posts;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laragear\WebAuthn\Contracts\WebAuthnAuthenticatable;
use Laragear\WebAuthn\WebAuthnAuthentication;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticationProvider;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements WebAuthnAuthenticatable
{
    use HasApiTokens, Notifiable, TwoFactorAuthenticatable, WebAuthnAuthentication;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'username',
        'nickname',
        'otp',
        'location',
        'about',
        'education',
        'job_title',
        'skills',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'username',
    ];

    protected $attributes = [
        'otp' => false,
        'role' => 'user',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function notes(): HasMany
    {
        return $this->hasMany(PersonalNotes::class, 'user_id');
    }

    public function social(): HasOne
    {
        return $this->hasOne(SocialNetworks::class, 'user_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Posts::class, 'user_id');
    }

    public function noteCategories(): HasMany
    {
        return $this->hasMany(PersonalNoteCategories::class, 'user_id');
    }

    public function confirmTwoFactorAuth($code): bool
    {
        $codeIsValid = app(TwoFactorAuthenticationProvider::class)
            ->verify(decrypt($this->two_factor_secret), $code);

        if ($codeIsValid) {
            $this->otp = true;
            $this->two_factor_confirmed_at = now();
            $this->save();

            return true;
        }

        return false;
    }

    public function WebAuthn(): HasMany
    {
        return $this->hasMany(\App\Models\WebAuthnCredential::class, 'authenticatable_id')->select(['id', 'device_name']);
    }
}

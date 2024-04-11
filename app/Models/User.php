<?php

namespace App\Models;

use App\Models\PersonalNotes\PersonalNoteCategories;
use App\Models\PersonalNotes\PersonalNotes;
use App\Models\Post\Posts;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticationProvider;
use Laravel\Sanctum\HasApiTokens;
use MongoDB\Laravel\Auth\User as Authenticatable;
use MongoDB\Laravel\Relations\HasMany;
use MongoDB\Laravel\Relations\HasOne;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, TwoFactorAuthenticatable;

    protected $collection = 'users';

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
        return $this->hasMany(PersonalNotes::class, 'user_id', '_id');
    }

    public function social(): HasOne
    {
        return $this->hasOne(SocialNetworks::class, 'user_id', '_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Posts::class, 'user_id', '_id');
    }

    public function noteCategories(): HasMany
    {
        return $this->hasMany(PersonalNoteCategories::class, 'user_id', '_id');
    }

    public function confirmTwoFactorAuth($code): bool
    {
        $codeIsValid = app(TwoFactorAuthenticationProvider::class)
            ->verify(decrypt($this->two_factor_secret), $code);

        if ($codeIsValid) {
            $this->two_factor_confirmed_at = true;
            $this->otp = true;
            $this->save();

            return true;
        }

        return false;
    }
}

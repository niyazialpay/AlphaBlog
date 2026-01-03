<?php

namespace App\Models;

use App\Models\PersonalNotes\PersonalNoteCategories;
use App\Models\PersonalNotes\PersonalNotes;
use App\Models\Post\Posts;
use App\Traits\ModelLogger;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laragear\WebAuthn\Contracts\WebAuthnAuthenticatable;
use Laragear\WebAuthn\WebAuthnAuthentication;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticationProvider;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements MustVerifyEmail, WebAuthnAuthenticatable, HasMedia
{
    use HasApiTokens, Notifiable, Searchable, TwoFactorAuthenticatable, WebAuthnAuthentication;
    use InteractsWithMedia;
    use Searchable;
    use SoftDeletes;
    use ModelLogger;

    protected $table = 'users';

    public function preferredLocale()
    {
        return $this->locale;
    }

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
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $attributes = [
        'otp' => false,
        'role' => 'user',
    ];

    protected $appends = [
        'profile_image',
        'full_name',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

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

    public function searchableAs(): string
    {
        return config('scout.prefix').'users';
    }

    public function toSearchableArray(): array
    {
        $array = [];
        $array['id'] = $this->id;
        $array['name'] = $this->name;
        $array['surname'] = $this->surname;
        $array['username'] = $this->username;
        $array['email'] = $this->email;
        $array['nickname'] = $this->nickname;
        $array['profile_image'] = $this->profile_image.'?s=750';

        return $array;
    }

    public function getProfileImageAttribute(): string
    {
        if($this->getFirstMediaUrl('profile')){
            return $this->getFirstMediaUrl('profile').'?';
        }
        else{
            return 'https://www.gravatar.com/avatar/'.hash('sha256', strtolower(trim($this->email))).'?d=mp&s=128';
        }
    }

    public function getFullNameAttribute(): string
    {
        return $this->name.' '.$this->surname;
    }

    public function privacy(): HasOne
    {
        return $this->hasOne(ProfilePrivacy::class);
    }

    // App\Models\User.php

    public function getDisplayNameAttribute()
    {
        $nameParts = [];

        if ($this->privacy && $this->privacy->show_name) {
            $nameParts[] = $this->name;
        }

        if ($this->privacy && $this->privacy->show_surname) {
            $nameParts[] = $this->surname;
        }

        return implode(' ', $nameParts);
    }

    public function getDisplayJobTitleAttribute()
    {
        return ($this->privacy && $this->privacy->show_job_title) ? $this->job_title : null;
    }

    public function getDisplayAboutAttribute()
    {
        return ($this->privacy && $this->privacy->show_about) ? $this->about : null;
    }

    public function getDisplaySkillsAttribute()
    {
        return ($this->privacy && $this->privacy->show_skills) ? explode(',', $this->skills) : [];
    }

    public function getVisibleSocialLinksAttribute()
    {
        $links = [];

        if ($this->privacy && $this->privacy->show_social_links && $this->social) {
            $socialPlatforms = [
                'github' => 'https://github.com/',
                'linkedin' => 'https://www.linkedin.com/in/',
                'facebook' => 'https://facebook.com/',
                'x' => 'https://x.com/',
                'devto' => 'https://dev.to/',
                'instagram' => 'https://instagram.com/',
                'medium' => 'https://medium.com/@',
                'deviantart' => 'https://deviantart.com/',
                'youtube' => 'https://youtube.com/',
                'reddit' => 'https://reddit.com/',
                'xbox' => 'https://account.xbox.com/en-us/profile?gamertag=',
                'twitch' => 'https://twitch.tv/',
                'telegram' => 'https://t.me/',
                'discord' => 'https://discordapp.com/users/',
                'website' => '',
            ];

            foreach ($socialPlatforms as $platform => $baseUrl) {
                $username = $this->social->{$platform};

                if ($username) {
                    if($platform == 'x'){
                        $icon = 'fa-brands fa-x-twitter';
                    }
                    elseif($platform == 'devto'){
                        $icon = 'fa-brands fa-dev';
                    }
                    elseif($platform ==  'reddit'){
                        $icon = 'fa-brands fa-reddit-alien';
                    }
                    elseif($platform == 'website'){
                        $icon = 'fa-solid fa-globe-pointer';
                    }
                    else{
                        $icon = 'fa-brands fa-'.$platform;
                    }
                    $links[$platform] = [
                        'url' => $baseUrl . $username,
                        'icon' => $icon,
                    ];
                }
            }
        }

        return $links;
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(UserSessions::class);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('resized')
            ->width(1920)
            ->height(1080)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('cover')
            ->width(850)
            ->height(480)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('thumb')
            ->width(365)
            ->height(200)
            ->nonOptimized()->keepOriginalImageFormat();
    }
}

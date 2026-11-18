<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'password', 'affiliate_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public const NATIONAL_ROLES = ['national_admin', 'national_user'];

    public const AFFILIATE_ROLES = ['affiliate_admin', 'affiliate_user'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole([...self::NATIONAL_ROLES, ...self::AFFILIATE_ROLES]);
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_registrations')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Records this viewer may see: national staff see every affiliate, affiliate
     * accounts only their own. An account with no affiliate sees nothing
     * (never `where affiliate_id is null`, which would expose national staff).
     */
    public function scopeVisibleTo(Builder $query, self $viewer): void
    {
        if ($viewer->isNational()) {
            return;
        }

        $viewer->affiliate_id
            ? $query->where('users.affiliate_id', $viewer->affiliate_id)
            : $query->whereRaw('0 = 1');
    }

    public function isNational(): bool
    {
        return $this->hasAnyRole(self::NATIONAL_ROLES);
    }

    public function isNationalAdmin(): bool
    {
        return $this->hasRole('national_admin');
    }

    public function isAffiliateAdmin(): bool
    {
        return $this->hasRole('affiliate_admin');
    }
}

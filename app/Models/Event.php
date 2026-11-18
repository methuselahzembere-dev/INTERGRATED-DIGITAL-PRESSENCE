<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['affiliate_id', 'created_by', 'title', 'description', 'venue', 'starts_at', 'attachments', 'published_at'])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'published_at' => 'datetime',
            'attachments' => 'array',
        ];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_registrations')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeUpcoming(Builder $query): void
    {
        $query->where('starts_at', '>=', now());
    }

    public function scopeVisibleTo(Builder $query, User $viewer): void
    {
        if ($viewer->isNational()) {
            return;
        }

        if (! $viewer->affiliate_id) {
            $query->whereRaw('0 = 1');

            return;
        }

        $query->where('events.affiliate_id', $viewer->affiliate_id);

        // Drafts are only visible to the affiliate's admin.
        if (! $viewer->isAffiliateAdmin()) {
            $query->published();
        }
    }

    public function canBeRsvpedBy(User $user): bool
    {
        return $user->affiliate_id !== null
            && $user->affiliate_id === $this->affiliate_id
            && $this->isPublished()
            && $this->starts_at->isFuture();
    }

    public function isAttendedBy(User $user): bool
    {
        return $this->registrations()
            ->where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->exists();
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lte(now());
    }
}

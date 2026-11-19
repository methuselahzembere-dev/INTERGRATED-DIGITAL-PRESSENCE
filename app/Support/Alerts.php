<?php

namespace App\Support;

use App\Models\Event;
use App\Models\User;
use Filament\Notifications\Notification;

/** Automatic in-app alerts (they appear in the bell in the side menu). */
class Alerts
{
    /** Tell every member of the event's affiliate that it was published. */
    public static function eventPublished(Event $event): void
    {
        $members = User::where('affiliate_id', $event->affiliate_id)
            ->where('id', '!=', $event->created_by)
            ->get();

        if ($members->isEmpty()) {
            return;
        }

        Notification::make()
            ->title('New event: '.$event->title)
            ->body($event->starts_at->format('D j M Y, H:i').' at '.$event->venue)
            ->icon('heroicon-o-calendar-days')
            ->sendToDatabase($members);
    }

    /** Tell the affiliate's admins and the national admins that someone joined. */
    public static function memberJoined(User $member): void
    {
        $recipients = User::role(['affiliate_admin', 'national_admin'])
            ->where(fn ($q) => $q->where('affiliate_id', $member->affiliate_id)->orWhereNull('affiliate_id'))
            ->where('id', '!=', $member->id)
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::make()
            ->title('New member: '.$member->name)
            ->body('Joined '.($member->affiliate?->name ?? 'the party').'.')
            ->icon('heroicon-o-user-plus')
            ->sendToDatabase($recipients);
    }
}

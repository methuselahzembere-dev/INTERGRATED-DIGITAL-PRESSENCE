<?php

namespace App\Filament\Widgets;

use App\Models\Affiliate;
use App\Models\Event;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    // Scoped through visibleTo(): national sees the whole party, an affiliate admin only their affiliate.
    public static function canView(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isNational() || $user?->isAffiliateAdmin());
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $members = User::visibleTo($user)->whereNotNull('affiliate_id');

        $stats = [
            Stat::make('Registered members', (clone $members)->count()),
            Stat::make('New this month', (clone $members)->where('users.created_at', '>=', now()->startOfMonth())->count()),
            Stat::make('Upcoming events', Event::visibleTo($user)->published()->upcoming()->count()),
        ];

        if ($user->isNational()) {
            array_unshift($stats, Stat::make('Affiliates', Affiliate::where('is_active', true)->count()));
        }

        return $stats;
    }
}

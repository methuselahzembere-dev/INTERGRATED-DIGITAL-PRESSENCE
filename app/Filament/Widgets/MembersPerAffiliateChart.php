<?php

namespace App\Filament\Widgets;

use App\Models\Affiliate;
use Filament\Widgets\ChartWidget;

class MembersPerAffiliateChart extends ChartWidget
{
    protected ?string $heading = 'Registered members by affiliate';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->isNational();
    }

    protected function getData(): array
    {
        $affiliates = Affiliate::withCount('users')->orderBy('name')->get();

        return [
            'datasets' => [[
                'label' => 'Members',
                'data' => $affiliates->pluck('users_count')->all(),
            ]],
            'labels' => $affiliates->pluck('name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

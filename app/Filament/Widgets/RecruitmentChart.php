<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class RecruitmentChart extends ChartWidget
{
    protected ?string $heading = 'Recruitment (new members per month)';

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = '12';

    public static function canView(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isNational() || $user?->isAffiliateAdmin());
    }

    protected function getFilters(): ?array
    {
        return [
            '3' => 'Last 3 months',
            '6' => 'Last 6 months',
            '12' => 'Last 12 months',
            'year' => 'This year',
        ];
    }

    protected function getData(): array
    {
        $start = $this->filter === 'year'
            ? now()->startOfYear()
            : now()->subMonths(((int) $this->filter) - 1)->startOfMonth();

        // Group in PHP so this works on SQLite (dev) and MySQL/Postgres alike.
        $counts = User::visibleTo(auth()->user())
            ->whereNotNull('affiliate_id')
            ->where('users.created_at', '>=', $start)
            ->pluck('created_at')
            ->countBy(fn ($date) => $date->format('Y-m'));

        $labels = [];
        $data = [];
        for ($month = $start->copy(); $month->lte(now()); $month->addMonth()) {
            $labels[] = $month->format('M Y');
            $data[] = $counts->get($month->format('Y-m'), 0);
        }

        return [
            'datasets' => [['label' => 'New members', 'data' => $data]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

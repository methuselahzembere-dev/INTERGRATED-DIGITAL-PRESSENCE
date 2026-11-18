<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    public function getHeading(): string|Htmlable
    {
        return config('branding.party_name').' — Integrated Digital Presence';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return '“'.config('branding.motto').'”';
    }
}

<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class Dashboard extends BaseDashboard
{
    public function getHeading(): string|Htmlable
    {
        return config('branding.party_name').' Integrated Digital Presence';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return new HtmlString('<span class="zp-motto">'.e(config('branding.motto')).'</span>');
    }
}

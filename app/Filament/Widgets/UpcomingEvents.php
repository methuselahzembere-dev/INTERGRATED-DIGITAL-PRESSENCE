<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class UpcomingEvents extends TableWidget
{
    protected static ?string $heading = 'Upcoming events';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Event::query()
                ->visibleTo(auth()->user())
                ->published()
                ->upcoming()
                ->with('affiliate')
                ->orderBy('starts_at'))
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('affiliate.name')->label('Affiliate'),
                TextColumn::make('starts_at')->label('Date & time')->dateTime(),
                TextColumn::make('venue'),
            ])
            ->paginated([5, 10]);
    }
}

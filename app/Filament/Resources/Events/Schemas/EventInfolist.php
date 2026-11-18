<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('affiliate.name')->label('Affiliate'),
                TextEntry::make('starts_at')->label('Date & time')->dateTime(),
                TextEntry::make('venue'),
                TextEntry::make('description')->label('Additional information')->placeholder('-')->columnSpanFull(),
                TextEntry::make('attachments')
                    ->listWithLineBreaks()
                    ->formatStateUsing(fn ($state): string => basename((string) $state))
                    ->url(fn ($state): ?string => $state ? asset('storage/'.$state) : null, shouldOpenInNewTab: true)
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('published_at')->dateTime()->placeholder('Draft'),
                TextEntry::make('creator.name')->label('Created by')->placeholder('-'),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Models\Event;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('affiliate_id')
                    ->label('Affiliate')
                    ->relationship('affiliate', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->visible(fn (): bool => (bool) auth()->user()?->isNational()),
                TextInput::make('title')->required()->maxLength(255),
                DateTimePicker::make('starts_at')
                    ->label('Date & time')
                    ->seconds(false)
                    ->required(),
                TextInput::make('venue')->required()->maxLength(255),
                Textarea::make('description')
                    ->label('Additional information')
                    ->rows(5)
                    ->columnSpanFull(),
                FileUpload::make('attachments')
                    ->multiple()
                    ->disk('public')
                    ->directory('event-attachments')
                    ->maxSize(10240)
                    ->maxFiles(5)
                    ->acceptedFileTypes(['application/pdf', 'image/png', 'image/jpeg', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->columnSpanFull(),
                Toggle::make('is_published')
                    ->label('Publish (visible to members)')
                    ->helperText('Leave off to keep this event as a draft.')
                    ->afterStateHydrated(fn (Toggle $component, ?Event $record) => $component->state($record?->published_at !== null)),
            ]);
    }

    /**
     * Normalise submitted data: affiliate admins are always pinned to their own
     * affiliate (whatever the request says) and the publish toggle becomes published_at.
     */
    public static function prepare(array $data, ?Event $existing = null): array
    {
        $user = auth()->user();

        if (! $user->isNational()) {
            $data['affiliate_id'] = $user->affiliate_id;
        }

        $publish = (bool) ($data['is_published'] ?? false);
        unset($data['is_published']);

        $data['published_at'] = $publish ? ($existing?->published_at ?? now()) : null;

        return $data;
    }
}

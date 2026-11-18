<?php

namespace App\Filament\Resources\Events\Tables;

use App\Models\Event;
use App\Models\EventRegistration;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('affiliate.name')
                    ->label('Affiliate')
                    ->sortable()
                    ->visible(fn (): bool => (bool) auth()->user()?->isNational()),
                TextColumn::make('starts_at')->label('Date & time')->dateTime()->sortable(),
                TextColumn::make('venue')->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->state(fn (Event $record): string => match (true) {
                        ! $record->isPublished() => 'Draft',
                        $record->starts_at->isPast() => 'Past',
                        default => 'Upcoming',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Draft' => 'gray',
                        'Past' => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('registrations_count')
                    ->label('RSVPs')
                    ->counts(['registrations' => fn ($query) => $query->where('status', 'confirmed')]),
            ])
            ->defaultSort('starts_at', 'desc')
            ->filters([
                SelectFilter::make('affiliate_id')
                    ->label('Affiliate')
                    ->relationship('affiliate', 'name')
                    ->visible(fn (): bool => (bool) auth()->user()?->isNational()),
            ])
            ->recordActions([
                Action::make('rsvp')
                    ->label('RSVP')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Event $record): bool => $record->canBeRsvpedBy(auth()->user()) && ! $record->isAttendedBy(auth()->user()))
                    ->action(function (Event $record): void {
                        abort_unless($record->canBeRsvpedBy(auth()->user()), 403);
                        EventRegistration::updateOrCreate(
                            ['event_id' => $record->id, 'user_id' => auth()->id()],
                            ['status' => 'confirmed'],
                        );
                        Notification::make()->title('Attendance confirmed')->success()->send();
                    }),
                Action::make('cancelRsvp')
                    ->label('Cancel RSVP')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Event $record): bool => $record->canBeRsvpedBy(auth()->user()) && $record->isAttendedBy(auth()->user()))
                    ->action(function (Event $record): void {
                        abort_unless($record->canBeRsvpedBy(auth()->user()), 403);
                        EventRegistration::where('event_id', $record->id)
                            ->where('user_id', auth()->id())
                            ->update(['status' => 'cancelled']);
                        Notification::make()->title('RSVP cancelled')->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

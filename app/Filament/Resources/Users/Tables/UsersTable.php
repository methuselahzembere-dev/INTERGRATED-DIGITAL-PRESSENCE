<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->label('Email address')->searchable(),
                TextColumn::make('phone')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('affiliate.name')->label('Affiliate')->placeholder('National')->sortable(),
                TextColumn::make('roles.name')->label('Role')->badge(),
                TextColumn::make('created_at')->label('Registered')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('affiliate_id')
                    ->label('Affiliate')
                    ->relationship('affiliate', 'name')
                    ->visible(fn (): bool => (bool) auth()->user()?->isNational()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('promote')
                    ->label('Promote to affiliate admin')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => (bool) auth()->user()?->isNationalAdmin() && $record->hasRole('affiliate_user'))
                    ->action(function ($record): void {
                        abort_unless(auth()->user()->isNationalAdmin(), 403);
                        $record->syncRoles(['affiliate_admin']);
                        Notification::make()->title($record->name.' is now an affiliate admin')->success()->send();
                    }),
                Action::make('demote')
                    ->label('Demote to affiliate user')
                    ->icon('heroicon-o-arrow-down-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => (bool) auth()->user()?->isNationalAdmin() && $record->hasRole('affiliate_admin'))
                    ->action(function ($record): void {
                        abort_unless(auth()->user()->isNationalAdmin(), 403);
                        $record->syncRoles(['affiliate_user']);
                        Notification::make()->title($record->name.' is now an affiliate user')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('sendAlert')
                        ->label('Send alert')
                        ->icon('heroicon-o-bell-alert')
                        ->visible(fn (): bool => (bool) (auth()->user()?->isNationalAdmin() || auth()->user()?->isAffiliateAdmin()))
                        ->schema([
                            TextInput::make('title')->required()->maxLength(120),
                            Textarea::make('body')->required()->maxLength(500),
                        ])
                        // $records comes from the scoped table query, so affiliate admins can only alert their own members.
                        ->action(function (Collection $records, array $data): void {
                            abort_unless(auth()->user()->isNationalAdmin() || auth()->user()->isAffiliateAdmin(), 403);
                            Notification::make()
                                ->title($data['title'])
                                ->body($data['body'])
                                ->sendToDatabase($records);
                            Notification::make()->title('Alert sent to '.$records->count().' user(s)')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

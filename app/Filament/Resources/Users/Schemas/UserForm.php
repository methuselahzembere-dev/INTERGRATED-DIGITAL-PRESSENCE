<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')->tel()->maxLength(30),
                Select::make('role')
                    ->options([
                        'national_admin' => 'National admin',
                        'national_user' => 'National user',
                        'affiliate_admin' => 'Affiliate admin',
                        'affiliate_user' => 'Affiliate user',
                    ])
                    ->required()
                    ->live()
                    ->afterStateHydrated(fn (Select $component, ?User $record) => $record
                        ? $component->state($record->roles->first()?->name)
                        : null),
                Select::make('affiliate_id')
                    ->label('Affiliate')
                    ->relationship('affiliate', 'name')
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get): bool => str_starts_with((string) $get('role'), 'affiliate_'))
                    ->visible(fn (Get $get): bool => str_starts_with((string) $get('role'), 'affiliate_')),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state)),
            ]);
    }
}

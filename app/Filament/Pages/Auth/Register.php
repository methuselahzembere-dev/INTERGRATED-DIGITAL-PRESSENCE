<?php

namespace App\Filament\Pages\Auth;

use App\Models\Affiliate;
use App\Models\User;
use App\Support\Alerts;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use SensitiveParameter;

/**
 * Self-registration: a new member joins one affiliate and starts as an
 * affiliate_user. Promotion to affiliate_admin stays with the national admin.
 */
class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),
            TextInput::make('phone')->tel()->maxLength(30),
            Select::make('affiliate_id')
                ->label('Affiliate')
                ->options(fn () => Affiliate::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required(),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
        ]);
    }

    protected function handleRegistration(#[SensitiveParameter] array $data): Model
    {
        /** @var User $user */
        $user = parent::handleRegistration($data);
        $user->assignRole('affiliate_user');
        Alerts::memberJoined($user);

        return $user;
    }
}

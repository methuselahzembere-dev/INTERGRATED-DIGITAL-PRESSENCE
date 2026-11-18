<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $role = Arr::pull($data, 'role');

        if (str_starts_with($role, 'national_')) {
            $data['affiliate_id'] = null;
        }

        $user = User::create($data);
        $user->syncRoles([$role]);

        return $user;
    }
}

<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Household;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;

class RegisterHousehold extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Buat Ruang Keuangan Baru';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Ruang Keuangan / Keluarga')
                    ->placeholder('contoh: Keluarga Kami, Kas Rumah Tangga')
                    ->required(),
                TextInput::make('description')
                    ->label('Keterangan')
                    ->placeholder('contoh: Pencatatan keuangan bersama suami dan istri'),
            ]);
    }

    protected function handleRegistration(array $data): Household
    {
        $household = Household::create($data);
        $household->users()->attach(auth()->user(), ['role' => 'owner']);

        return $household;
    }
}

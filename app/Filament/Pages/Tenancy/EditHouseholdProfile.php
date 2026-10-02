<?php

namespace App\Filament\Pages\Tenancy;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;

class EditHouseholdProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Pengaturan Ruang Keuangan';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Ruang')
                    ->required(),
                TextInput::make('description')
                    ->label('Deskripsi'),
                TextInput::make('payday_date')
                    ->label('Tanggal Gajian (Cut-off date)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(31)
                    ->default(25)
                    ->required()
                    ->helperText('Tanggal ini akan digunakan sebagai awal periode pelaporan bulanan di Dashboard.'),
            ]);
    }
}

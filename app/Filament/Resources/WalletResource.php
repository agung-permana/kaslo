<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletResource\Pages;
use App\Models\Wallet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $modelLabel = 'Dompet / Rekening';

    protected static ?string $pluralModelLabel = 'Dompet & Rekening';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Akun / Dompet')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Dompet / Akun')
                            ->placeholder('contoh: BCA Bersama, Dompet Tunai, GoPay')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->label('Jenis Dompet')
                            ->options([
                                'cash' => '💵 Uang Tunai (Cash)',
                                'bank' => '🏦 Rekening Bank',
                                'e-wallet' => '📱 E-Wallet (GoPay, OVO, Dana, dll)',
                                'investment' => '📈 Investasi / Tabungan',
                            ])
                            ->default('cash')
                            ->required(),
                        Forms\Components\TextInput::make('account_number')
                            ->label('Nomor Rekening / No. E-Wallet')
                            ->placeholder('Opsional, misal: 5420912345')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('initial_balance')
                            ->label('Saldo Awal')
                            ->prefix('Rp')
                            ->placeholder('0')
                            ->extraInputAttributes([
                                'x-on:input' => '$el.value = $el.value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")',
                            ])
                            ->formatStateUsing(fn ($state) => $state ? number_format((float) str_replace('.', '', (string) $state), 0, ',', '.') : null)
                            ->dehydrateStateUsing(fn ($state) => $state ? (float) str_replace('.', '', (string) $state) : 0)
                            ->default(0)
                            ->disabledOn('edit')
                            ->helperText('Saldo awal saat dompet pertama kali dibuat. Saldo berjalan akan otomatis disinkronkan oleh transaksi.'),
                        Forms\Components\ColorPicker::make('color')
                            ->label('Warna Indikator')
                            ->default('#10b981'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label('')
                    ->width('30px'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Dompet')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'bank' => 'Bank',
                        'e-wallet' => 'E-Wallet',
                        'investment' => 'Investasi',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'bank' => 'info',
                        'e-wallet' => 'warning',
                        'investment' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('account_number')
                    ->label('No. Rekening')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('current_balance')
                    ->label('Saldo Saat Ini')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->weight('semibold')
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Filter Tipe')
                    ->options([
                        'cash' => 'Tunai',
                        'bank' => 'Bank',
                        'e-wallet' => 'E-Wallet',
                        'investment' => 'Investasi',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWallets::route('/'),
            'create' => Pages\CreateWallet::route('/create'),
            'edit' => Pages\EditWallet::route('/{record}/edit'),
        ];
    }
}

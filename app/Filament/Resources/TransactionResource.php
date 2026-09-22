<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $modelLabel = 'Transaksi';

    protected static ?string $pluralModelLabel = 'Catatan Transaksi';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id()),

                Forms\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Jenis Transaksi')
                            ->options([
                                'expense' => '🔴 Pengeluaran',
                                'income' => '🟢 Pemasukan',
                                'transfer' => '🔄 Transfer Saldo',
                            ])
                            ->default('expense')
                            ->required()
                            ->selectablePlaceholder(false)
                            ->live()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Nominal Transaksi')
                            ->prefix('Rp')
                            ->placeholder('contoh: 50.000')
                            ->extraInputAttributes([
                                'class' => 'text-lg font-bold',
                                'x-on:input' => '$el.value = $el.value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")',
                            ])
                            ->formatStateUsing(fn ($state) => $state ? number_format((float) str_replace('.', '', (string) $state), 0, ',', '.') : null)
                            ->dehydrateStateUsing(fn ($state) => $state ? (float) str_replace('.', '', (string) $state) : 0)
                            ->required(),

                        Forms\Components\DatePicker::make('transaction_date')
                            ->label('Tanggal Transaksi')
                            ->default(now())
                            ->required(),

                        Forms\Components\Select::make('wallet_id')
                            ->label(fn (Get $get) => $get('type') === 'transfer' ? 'Dompet Sumber (Asal)' : 'Dompet / Rekening')
                            ->options(fn () => Filament::getTenant()?->wallets()->where('is_active', true)->pluck('name', 'id') ?? [])
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('destination_wallet_id')
                            ->label('Dompet Tujuan')
                            ->options(function (Get $get) {
                                $sourceWalletId = $get('wallet_id');
                                return Filament::getTenant()?->wallets()
                                    ->where('is_active', true)
                                    ->when($sourceWalletId, fn ($q) => $q->where('id', '!=', $sourceWalletId))
                                    ->pluck('name', 'id') ?? [];
                            })
                            ->visible(fn (Get $get) => $get('type') === 'transfer')
                            ->required(fn (Get $get) => $get('type') === 'transfer')
                            ->searchable(),

                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->options(function (Get $get) {
                                $type = $get('type');
                                if (!in_array($type, ['expense', 'income'])) {
                                    return [];
                                }
                                return Filament::getTenant()?->categories()->where('type', $type)->pluck('name', 'id') ?? [];
                            })
                            ->visible(fn (Get $get) => in_array($get('type'), ['expense', 'income']))
                            ->required(fn (Get $get) => in_array($get('type'), ['expense', 'income']))
                            ->searchable()
                            ->preload(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan / Keterangan')
                            ->placeholder('contoh: Makan siang keluarga, bayar tagihan internet IndiHome')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('receipt_path')
                            ->label('Foto Struk / Bukti Transaksi')
                            ->image()
                            ->directory('receipts')
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('transaction_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori / Info')
                    ->description(fn (Transaction $record) => $record->notes)
                    ->icon(fn (Transaction $record): ?string => $record->category?->icon)
                    ->placeholder(fn (Transaction $record) => $record->type === 'transfer' ? 'Transfer Antar Dompet' : '-')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'expense' => 'Pengeluaran',
                        'income' => 'Pemasukan',
                        'transfer' => 'Transfer',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'expense' => 'danger',
                        'income' => 'success',
                        'transfer' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->formatStateUsing(function (Transaction $record): string {
                        $prefix = match ($record->type) {
                            'income' => '+ Rp ',
                            'expense' => '- Rp ',
                            'transfer' => '⇄ Rp ',
                            default => 'Rp ',
                        };
                        return $prefix . number_format($record->amount, 0, ',', '.');
                    })
                    ->color(fn (Transaction $record): string => match ($record->type) {
                        'income' => 'success',
                        'expense' => 'danger',
                        'transfer' => 'info',
                        default => 'gray',
                    })
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('wallet.name')
                    ->label('Dompet')
                    ->formatStateUsing(function (Transaction $record): string {
                        if ($record->type === 'transfer' && $record->destinationWallet) {
                            return ($record->wallet?->name ?? '-') . ' ➔ ' . $record->destinationWallet->name;
                        }
                        return $record->wallet?->name ?? '-';
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dicatat Oleh')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('receipt_path')
                    ->label('Bukti')
                    ->circular()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis Transaksi')
                    ->options([
                        'expense' => 'Pengeluaran',
                        'income' => 'Pemasukan',
                        'transfer' => 'Transfer',
                    ]),
                Tables\Filters\SelectFilter::make('wallet_id')
                    ->label('Dompet')
                    ->options(fn () => Filament::getTenant()?->wallets()->pluck('name', 'id') ?? []),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->options(fn () => Filament::getTenant()?->categories()->pluck('name', 'id') ?? []),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pencatat')
                    ->relationship('user', 'name', fn (Builder $query) => Filament::getTenant() ? $query->whereHas('households', fn ($q) => $q->where('households.id', Filament::getTenant()->id)) : $query),
                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('transaction_date', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('transaction_date', '<=', $date));
                    }),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}

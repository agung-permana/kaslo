<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\TransactionResource;
use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTransactionsWidget extends BaseWidget
{
    protected static ?string $heading = 'Transaksi Terkini';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(TransactionResource::getEloquentQuery()->latest('transaction_date')->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->placeholder(fn (Transaction $record) => $record->type === 'transfer' ? 'Transfer Antar Dompet' : '-')
                    ->icon(fn (Transaction $record) => $record->category?->icon)
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
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('wallet.name')
                    ->label('Dompet')
                    ->formatStateUsing(function (Transaction $record): string {
                        if ($record->type === 'transfer' && $record->destinationWallet) {
                            return ($record->wallet?->name ?? '-') . ' ➔ ' . $record->destinationWallet->name;
                        }
                        return $record->wallet?->name ?? '-';
                    }),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pencatat')
                    ->badge()
                    ->color('gray'),
            ])
            ->paginated(false);
    }
}

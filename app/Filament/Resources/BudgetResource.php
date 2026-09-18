<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetResource\Pages;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $modelLabel = 'Anggaran';

    protected static ?string $pluralModelLabel = 'Target Anggaran';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Rencana Anggaran Bulanan')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori Pengeluaran')
                            ->options(fn () => Category::where('type', 'expense')->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('month_year')
                            ->label('Periode Bulan (YYYY-MM)')
                            ->default(now()->format('Y-m'))
                            ->placeholder('contoh: ' . now()->format('Y-m'))
                            ->required()
                            ->maxLength(7)
                            ->helperText('Gunakan format tahun-bulan, contoh: ' . now()->format('Y-m')),

                        Forms\Components\TextInput::make('amount')
                            ->label('Batas Maksimal Anggaran')
                            ->prefix('Rp')
                            ->placeholder('contoh: 2.500.000')
                            ->extraInputAttributes([
                                'x-on:input' => '$el.value = $el.value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")',
                            ])
                            ->formatStateUsing(fn ($state) => $state ? number_format((float) str_replace('.', '', (string) $state), 0, ',', '.') : null)
                            ->dehydrateStateUsing(fn ($state) => $state ? (float) str_replace('.', '', (string) $state) : 0)
                            ->required(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Anggaran')
                            ->placeholder('contoh: Batas belanja bahan makanan per bulan')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->icon(fn (Budget $record) => $record->category?->icon)
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('month_year')
                    ->label('Periode')
                    ->formatStateUsing(fn (string $state) => Carbon::createFromFormat('Y-m', $state)->translatedFormat('F Y'))
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Target Anggaran')
                    ->money('IDR', locale: 'id')
                    ->weight('semibold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('spent')
                    ->label('Realisasi Terpakai')
                    ->getStateUsing(function (Budget $record): string {
                        $start = Carbon::createFromFormat('Y-m', $record->month_year)->startOfMonth();
                        $end = Carbon::createFromFormat('Y-m', $record->month_year)->endOfMonth();

                        $totalSpent = Transaction::where('category_id', $record->category_id)
                            ->where('type', 'expense')
                            ->whereBetween('transaction_date', [$start, $end])
                            ->sum('amount');

                        return 'Rp ' . number_format($totalSpent, 0, ',', '.');
                    })
                    ->color(function (Budget $record): string {
                        $start = Carbon::createFromFormat('Y-m', $record->month_year)->startOfMonth();
                        $end = Carbon::createFromFormat('Y-m', $record->month_year)->endOfMonth();

                        $totalSpent = Transaction::where('category_id', $record->category_id)
                            ->where('type', 'expense')
                            ->whereBetween('transaction_date', [$start, $end])
                            ->sum('amount');

                        return $totalSpent > $record->amount ? 'danger' : 'success';
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function (Budget $record): string {
                        $start = Carbon::createFromFormat('Y-m', $record->month_year)->startOfMonth();
                        $end = Carbon::createFromFormat('Y-m', $record->month_year)->endOfMonth();

                        $totalSpent = Transaction::where('category_id', $record->category_id)
                            ->where('type', 'expense')
                            ->whereBetween('transaction_date', [$start, $end])
                            ->sum('amount');

                        if ($record->amount <= 0) return 'Tidak ada limit';
                        $percentage = round(($totalSpent / $record->amount) * 100);

                        if ($percentage > 100) {
                            return "Over Budget ({$percentage}%)";
                        } elseif ($percentage >= 80) {
                            return "Waspada ({$percentage}%)";
                        }
                        return "Aman ({$percentage}%)";
                    })
                    ->color(function (Budget $record): string {
                        $start = Carbon::createFromFormat('Y-m', $record->month_year)->startOfMonth();
                        $end = Carbon::createFromFormat('Y-m', $record->month_year)->endOfMonth();

                        $totalSpent = Transaction::where('category_id', $record->category_id)
                            ->where('type', 'expense')
                            ->whereBetween('transaction_date', [$start, $end])
                            ->sum('amount');

                        if ($record->amount <= 0) return 'gray';
                        $percentage = ($totalSpent / $record->amount) * 100;

                        if ($percentage > 100) return 'danger';
                        if ($percentage >= 80) return 'warning';
                        return 'success';
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('month_year')
                    ->label('Filter Bulan')
                    ->options(function () {
                        return Budget::query()->distinct()->pluck('month_year', 'month_year');
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
            'index' => Pages\ListBudgets::route('/'),
            'create' => Pages\CreateBudget::route('/create'),
            'edit' => Pages\EditBudget::route('/{record}/edit'),
        ];
    }
}

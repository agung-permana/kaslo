<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $modelLabel = 'Kategori';

    protected static ?string $pluralModelLabel = 'Kategori Transaksi';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Kategori')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Kategori')
                            ->placeholder('contoh: Belanja Dapur, Gaji, Makan & Minum')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->label('Tipe Kategori')
                            ->options([
                                'expense' => '🔴 Pengeluaran (Expense)',
                                'income' => '🟢 Pemasukan (Income)',
                            ])
                            ->default('expense')
                            ->required(),
                        Forms\Components\ColorPicker::make('color')
                            ->label('Warna Label')
                            ->default('#6366f1'),
                        Forms\Components\Select::make('icon')
                            ->label('Ikon')
                            ->options([
                                'heroicon-o-shopping-cart' => 'Keranjang Belanja',
                                'heroicon-o-cake' => 'Makanan / Kue',
                                'heroicon-o-truck' => 'Transport / Bensin',
                                'heroicon-o-bolt' => 'Listrik / Utilitas',
                                'heroicon-o-film' => 'Hiburan / Rekreasi',
                                'heroicon-o-briefcase' => 'Pekerjaan / Gaji',
                                'heroicon-o-banknotes' => 'Uang / Bisnis',
                                'heroicon-o-heart' => 'Kesehatan',
                                'heroicon-o-academic-cap' => 'Pendidikan',
                                'heroicon-o-home' => 'Rumah Tangga',
                            ])
                            ->searchable()
                            ->placeholder('Pilih ikon visual'),
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
                    ->label('Nama Kategori')
                    ->icon(fn (Category $record): ?string => $record->icon)
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'income' ? 'Pemasukan' : 'Pengeluaran')
                    ->color(fn (string $state): string => $state === 'income' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('transactions_count')
                    ->counts('transactions')
                    ->label('Total Transaksi')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Filter Tipe')
                    ->options([
                        'expense' => 'Pengeluaran',
                        'income' => 'Pemasukan',
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}

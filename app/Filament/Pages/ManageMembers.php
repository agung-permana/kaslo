<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class ManageMembers extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Anggota Keluarga';

    protected static ?string $title = 'Kelola Anggota Ruang Keuangan';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.manage-members';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('invite')
                ->label('+ Tambah / Hubungkan Pasangan')
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->form([
                    TextInput::make('email')
                        ->label('Alamat Email Pasangan / Anggota')
                        ->email()
                        ->required()
                        ->placeholder('contoh: istri@kaslo.test'),
                    TextInput::make('name')
                        ->label('Nama Lengkap (jika belum terdaftar)')
                        ->placeholder('contoh: Siti Rahma'),
                    Select::make('role')
                        ->label('Peran')
                        ->options([
                            'member' => 'Anggota (Bisa catat transaksi & lihat saldo)',
                            'owner' => 'Pemilik Bersama (Hak akses penuh)',
                        ])
                        ->default('member')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $household = Filament::getTenant();

                    $user = User::where('email', $data['email'])->first();

                    if (!$user) {
                        $user = User::create([
                            'name' => $data['name'] ?: explode('@', $data['email'])[0],
                            'email' => $data['email'],
                            'password' => Hash::make('password123'),
                        ]);

                        Notification::make()
                            ->title('Pengguna Baru Dibuat')
                            ->body("Akun baru untuk {$user->email} berhasil dibuat dengan password awal: password123")
                            ->info()
                            ->send();
                    }

                    if ($household->users()->where('user_id', $user->id)->exists()) {
                        Notification::make()
                            ->title('Sudah Terhubung')
                            ->body("Pengguna {$user->name} ({$user->email}) sudah menjadi anggota ruang keuangan ini.")
                            ->warning()
                            ->send();
                        return;
                    }

                    $household->users()->attach($user->id, ['role' => $data['role']]);

                    Notification::make()
                        ->title('Berhasil Menghubungkan Anggota')
                        ->body("{$user->name} telah berhasil ditambahkan ke {$household->name}.")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function table(Table $table): Table
    {
        $household = Filament::getTenant();

        return $table
            ->query($household ? $household->users()->getQuery() : User::query()->whereRaw('1 = 0'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pivot.role')
                    ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state === 'owner' ? 'Pemilik / Pengelola' : 'Anggota')
                    ->color(fn (?string $state) => $state === 'owner' ? 'success' : 'info'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Bergabung')
                    ->dateTime('d M Y'),
            ])
            ->actions([
                Tables\Actions\Action::make('remove')
                    ->label('Hapus Akses')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => $record->id !== auth()->id())
                    ->action(function (User $record): void {
                        $household = Filament::getTenant();
                        $household->users()->detach($record->id);

                        Notification::make()
                            ->title('Akses Dihapus')
                            ->body("{$record->name} tidak lagi memiliki akses ke ruang keuangan ini.")
                            ->success()
                            ->send();
                    }),
            ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Household;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Suami & Istri
        $suami = User::create([
            'name' => 'Budi Santoso',
            'email' => 'suami@kaslo.test',
            'password' => Hash::make('password'),
        ]);

        $istri = User::create([
            'name' => 'Siti Rahma',
            'email' => 'istri@kaslo.test',
            'password' => Hash::make('password'),
        ]);

        // 2. Buat Ruang Keuangan Bersama (Household)
        $household = Household::create([
            'name' => 'Keluarga Budi & Siti',
            'description' => 'Ruang pencatatan keuangan dan anggaran bersama',
        ]);

        // Hubungkan suami & istri ke ruang keuangan yang sama
        $household->users()->attach($suami->id, ['role' => 'owner']);
        $household->users()->attach($istri->id, ['role' => 'member']);

        // 3. Buat Dompet / Rekening
        $bca = Wallet::create([
            'household_id' => $household->id,
            'name' => 'BCA Rekening Bersama',
            'type' => 'bank',
            'account_number' => '5420912345',
            'initial_balance' => 15000000,
            'current_balance' => 15000000,
            'color' => '#3b82f6',
        ]);

        $tunai = Wallet::create([
            'household_id' => $household->id,
            'name' => 'Kas Tunai Dompet',
            'type' => 'cash',
            'initial_balance' => 1500000,
            'current_balance' => 1500000,
            'color' => '#10b981',
        ]);

        $gopay = Wallet::create([
            'household_id' => $household->id,
            'name' => 'GoPay',
            'type' => 'e-wallet',
            'account_number' => '08123456789',
            'initial_balance' => 650000,
            'current_balance' => 650000,
            'color' => '#06b6d4',
        ]);

        // 4. Buat Kategori Pengeluaran & Pemasukan
        $catMakan = Category::create(['household_id' => $household->id, 'name' => 'Makan & Minum', 'type' => 'expense', 'color' => '#f59e0b', 'icon' => 'heroicon-o-cake']);
        $catDapur = Category::create(['household_id' => $household->id, 'name' => 'Belanja Dapur', 'type' => 'expense', 'color' => '#ef4444', 'icon' => 'heroicon-o-shopping-cart']);
        $catTransport = Category::create(['household_id' => $household->id, 'name' => 'Transport & Bensin', 'type' => 'expense', 'color' => '#8b5cf6', 'icon' => 'heroicon-o-truck']);
        $catTagihan = Category::create(['household_id' => $household->id, 'name' => 'Tagihan Listrik & Internet', 'type' => 'expense', 'color' => '#ec4899', 'icon' => 'heroicon-o-bolt']);
        $catHiburan = Category::create(['household_id' => $household->id, 'name' => 'Hiburan & Liburan', 'type' => 'expense', 'color' => '#14b8a6', 'icon' => 'heroicon-o-film']);

        $catGajiSuami = Category::create(['household_id' => $household->id, 'name' => 'Gaji Suami', 'type' => 'income', 'color' => '#10b981', 'icon' => 'heroicon-o-briefcase']);
        $catGajiIstri = Category::create(['household_id' => $household->id, 'name' => 'Gaji Istri', 'type' => 'income', 'color' => '#059669', 'icon' => 'heroicon-o-currency-dollar']);
        $catBisnis = Category::create(['household_id' => $household->id, 'name' => 'Bisnis Sampingan', 'type' => 'income', 'color' => '#3b82f6', 'icon' => 'heroicon-o-banknotes']);

        // 5. Buat Anggaran Bulanan (Bulan Berjalan)
        $currentMonth = Carbon::now()->format('Y-m');
        Budget::create(['household_id' => $household->id, 'category_id' => $catMakan->id, 'amount' => 2500000, 'month_year' => $currentMonth]);
        Budget::create(['household_id' => $household->id, 'category_id' => $catDapur->id, 'amount' => 3000000, 'month_year' => $currentMonth]);
        Budget::create(['household_id' => $household->id, 'category_id' => $catTagihan->id, 'amount' => 1200000, 'month_year' => $currentMonth]);

        // 6. Buat Beberapa Transaksi Contoh
        // Pemasukan Gaji Suami
        Transaction::create([
            'household_id' => $household->id,
            'user_id' => $suami->id,
            'wallet_id' => $bca->id,
            'category_id' => $catGajiSuami->id,
            'type' => 'income',
            'amount' => 12000000,
            'transaction_date' => Carbon::now()->startOfMonth()->toDateString(),
            'notes' => 'Gaji bulanan kantor',
        ]);

        // Pemasukan Gaji Istri
        Transaction::create([
            'household_id' => $household->id,
            'user_id' => $istri->id,
            'wallet_id' => $bca->id,
            'category_id' => $catGajiIstri->id,
            'type' => 'income',
            'amount' => 8000000,
            'transaction_date' => Carbon::now()->startOfMonth()->addDays(2)->toDateString(),
            'notes' => 'Gaji bulanan mengajar',
        ]);

        // Pengeluaran Belanja Dapur dicatat Istri
        Transaction::create([
            'household_id' => $household->id,
            'user_id' => $istri->id,
            'wallet_id' => $tunai->id,
            'category_id' => $catDapur->id,
            'type' => 'expense',
            'amount' => 450000,
            'transaction_date' => Carbon::now()->subDays(3)->toDateString(),
            'notes' => 'Belanja mingguan di pasar & sayur',
        ]);

        // Pengeluaran Makan di luar dicatat Suami
        Transaction::create([
            'household_id' => $household->id,
            'user_id' => $suami->id,
            'wallet_id' => $gopay->id,
            'category_id' => $catMakan->id,
            'type' => 'expense',
            'amount' => 125000,
            'transaction_date' => Carbon::now()->subDays(1)->toDateString(),
            'notes' => 'Makan malam keluarga',
        ]);

        // Transfer dari BCA ke GoPay
        Transaction::create([
            'household_id' => $household->id,
            'user_id' => $suami->id,
            'wallet_id' => $bca->id,
            'destination_wallet_id' => $gopay->id,
            'type' => 'transfer',
            'amount' => 500000,
            'transaction_date' => Carbon::now()->subDays(2)->toDateString(),
            'notes' => 'Topup saldo GoPay dari BCA',
        ]);
    }
}

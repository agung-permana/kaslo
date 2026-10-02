<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $household = Filament::getTenant();

        if (! $household) {
            return [];
        }

        $payday = $household->payday_date ?? 25; // Tanggal gajian dari Settings, default 25
        $now = Carbon::now();
        
        if ($now->day >= $payday) {
            // Periode dimulai dari bulan ini tanggal 25, sampai bulan depan tanggal 24
            $startOfMonth = $now->copy()->setDay($payday)->startOfDay();
            $endOfMonth = $startOfMonth->copy()->addMonth()->subDay()->endOfDay();
        } else {
            // Periode dimulai dari bulan lalu tanggal 25, sampai bulan ini tanggal 24
            $startOfMonth = $now->copy()->subMonth()->setDay($payday)->startOfDay();
            $endOfMonth = $now->copy()->setDay($payday)->subDay()->endOfDay();
        }

        $periodDescription = $startOfMonth->translatedFormat('d M') . ' - ' . $endOfMonth->translatedFormat('d M Y');

        // Total Saldo Semua Dompet Aktif milik Ruang Keuangan saat ini
        $totalBalance = $household->wallets()->where('is_active', true)->sum('current_balance');

        // Pemasukan Bulan Berjalan milik Ruang Keuangan saat ini
        $incomeThisMonth = $household->transactions()->where('type', 'income')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Pengeluaran Bulan Berjalan milik Ruang Keuangan saat ini
        $expenseThisMonth = $household->transactions()->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Net Cash Flow (Tabungan Bersih)
        $netCashFlow = $incomeThisMonth - $expenseThisMonth;

        return [
            Stat::make('Total Saldo Bersama', 'Rp ' . number_format($totalBalance, 0, ',', '.'))
                ->description('Akumulasi semua rekening & dompet')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('success'),

            Stat::make('Pemasukan Bulan Ini', 'Rp ' . number_format($incomeThisMonth, 0, ',', '.'))
                ->description($periodDescription)
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Pengeluaran Bulan Ini', 'Rp ' . number_format($expenseThisMonth, 0, ',', '.'))
                ->description($periodDescription)
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Arus Kas Bersih', 'Rp ' . number_format($netCashFlow, 0, ',', '.'))
                ->description($netCashFlow >= 0 ? 'Surplus / Menabung' : 'Defisit')
                ->descriptionIcon($netCashFlow >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($netCashFlow >= 0 ? 'info' : 'danger'),
        ];
    }
}

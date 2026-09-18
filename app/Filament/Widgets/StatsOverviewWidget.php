<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Total Saldo Semua Dompet Aktif
        $totalBalance = Wallet::where('is_active', true)->sum('current_balance');

        // Pemasukan Bulan Berjalan
        $incomeThisMonth = Transaction::where('type', 'income')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Pengeluaran Bulan Berjalan
        $expenseThisMonth = Transaction::where('type', 'expense')
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
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Pengeluaran Bulan Ini', 'Rp ' . number_format($expenseThisMonth, 0, ',', '.'))
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Arus Kas Bersih', 'Rp ' . number_format($netCashFlow, 0, ',', '.'))
                ->description($netCashFlow >= 0 ? 'Surplus / Menabung' : 'Defisit')
                ->descriptionIcon($netCashFlow >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($netCashFlow >= 0 ? 'info' : 'danger'),
        ];
    }
}

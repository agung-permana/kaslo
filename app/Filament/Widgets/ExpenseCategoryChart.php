<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ExpenseCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Pengeluaran per Kategori (Bulan Ini)';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected function getData(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $categories = Category::where('type', 'expense')
            ->withSum(['transactions' => function ($query) use ($startOfMonth, $endOfMonth) {
                $query->where('type', 'expense')
                    ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth]);
            }], 'amount')
            ->get()
            ->filter(fn ($cat) => ($cat->transactions_sum_amount ?? 0) > 0);

        $labels = $categories->pluck('name')->toArray();
        $data = $categories->map(fn ($cat) => (float) $cat->transactions_sum_amount)->toArray();
        $colors = $categories->pluck('color')->map(fn ($c) => $c ?: '#6366f1')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Pengeluaran',
                    'data' => empty($data) ? [1] : $data,
                    'backgroundColor' => empty($colors) ? ['#e2e8f0'] : $colors,
                ],
            ],
            'labels' => empty($labels) ? ['Belum ada pengeluaran'] : $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

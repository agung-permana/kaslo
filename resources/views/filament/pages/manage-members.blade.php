<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 rounded-lg">
                    <x-heroicon-o-user-group class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Kolaborasi Keuangan Bersama</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Ruang keuangan ini dapat diakses dan dikelola bersama. Baik suami maupun istri dapat mencatat pengeluaran, memantau saldo dompet, dan melihat realisasi anggaran bulanan secara real-time dari perangkat masing-masing.
                    </p>
                </div>
            </div>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>

<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\Wallet;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        $this->applyBalance($transaction, 1);
    }

    public function updated(Transaction $transaction): void
    {
        // Revert old transaction values
        $oldTransaction = new Transaction();
        $oldTransaction->type = $transaction->getOriginal('type');
        $oldTransaction->amount = $transaction->getOriginal('amount');
        $oldTransaction->wallet_id = $transaction->getOriginal('wallet_id');
        $oldTransaction->destination_wallet_id = $transaction->getOriginal('destination_wallet_id');

        $this->applyBalance($oldTransaction, -1);

        // Apply new values
        $this->applyBalance($transaction, 1);
    }

    public function deleted(Transaction $transaction): void
    {
        $this->applyBalance($transaction, -1);
    }

    protected function applyBalance(Transaction $transaction, int $multiplier): void
    {
        $wallet = Wallet::find($transaction->wallet_id);
        if ($wallet) {
            if ($transaction->type === 'income') {
                $wallet->increment('current_balance', $transaction->amount * $multiplier);
            } elseif ($transaction->type === 'expense') {
                $wallet->decrement('current_balance', $transaction->amount * $multiplier);
            } elseif ($transaction->type === 'transfer') {
                $wallet->decrement('current_balance', $transaction->amount * $multiplier);
                if ($transaction->destination_wallet_id) {
                    $destWallet = Wallet::find($transaction->destination_wallet_id);
                    if ($destWallet) {
                        $destWallet->increment('current_balance', $transaction->amount * $multiplier);
                    }
                }
            }
        }
    }
}

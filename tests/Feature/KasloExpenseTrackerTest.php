<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Household;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasloExpenseTrackerTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_admin(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/admin');
    }

    public function test_household_and_multi_user_access(): void
    {
        $suami = User::factory()->create(['name' => 'Budi']);
        $istri = User::factory()->create(['name' => 'Siti']);

        $household = Household::create(['name' => 'Keluarga Budi & Siti']);
        $household->users()->attach($suami->id, ['role' => 'owner']);
        $household->users()->attach($istri->id, ['role' => 'member']);

        $this->assertTrue($suami->canAccessTenant($household));
        $this->assertTrue($istri->canAccessTenant($household));
        $this->assertCount(2, $household->users);
    }

    public function test_expense_decreases_wallet_balance(): void
    {
        $user = User::factory()->create();
        $household = Household::create(['name' => 'Kas Bersama']);
        $household->users()->attach($user->id);

        $wallet = Wallet::create([
            'household_id' => $household->id,
            'name' => 'Dompet Cash',
            'initial_balance' => 1000000,
            'current_balance' => 1000000,
        ]);

        $category = Category::create([
            'household_id' => $household->id,
            'name' => 'Makan',
            'type' => 'expense',
        ]);

        $transaction = Transaction::create([
            'household_id' => $household->id,
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 150000,
            'transaction_date' => now(),
        ]);

        $this->assertEquals(850000, $wallet->fresh()->current_balance);

        // Test delete reverts balance
        $transaction->delete();
        $this->assertEquals(1000000, $wallet->fresh()->current_balance);
    }

    public function test_income_increases_wallet_balance(): void
    {
        $user = User::factory()->create();
        $household = Household::create(['name' => 'Kas Bersama']);
        $household->users()->attach($user->id);

        $wallet = Wallet::create([
            'household_id' => $household->id,
            'name' => 'BCA',
            'initial_balance' => 5000000,
            'current_balance' => 5000000,
        ]);

        $category = Category::create([
            'household_id' => $household->id,
            'name' => 'Gaji',
            'type' => 'income',
        ]);

        Transaction::create([
            'household_id' => $household->id,
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => 10000000,
            'transaction_date' => now(),
        ]);

        $this->assertEquals(15000000, $wallet->fresh()->current_balance);
    }

    public function test_transfer_moves_balance_between_wallets(): void
    {
        $user = User::factory()->create();
        $household = Household::create(['name' => 'Kas Bersama']);
        $household->users()->attach($user->id);

        $sourceWallet = Wallet::create([
            'household_id' => $household->id,
            'name' => 'BCA',
            'initial_balance' => 2000000,
            'current_balance' => 2000000,
        ]);

        $destWallet = Wallet::create([
            'household_id' => $household->id,
            'name' => 'GoPay',
            'initial_balance' => 100000,
            'current_balance' => 100000,
        ]);

        Transaction::create([
            'household_id' => $household->id,
            'user_id' => $user->id,
            'wallet_id' => $sourceWallet->id,
            'destination_wallet_id' => $destWallet->id,
            'type' => 'transfer',
            'amount' => 500000,
            'transaction_date' => now(),
        ]);

        $this->assertEquals(1500000, $sourceWallet->fresh()->current_balance);
        $this->assertEquals(600000, $destWallet->fresh()->current_balance);
    }

    public function test_budget_creation(): void
    {
        $household = Household::create(['name' => 'Kas Bersama']);
        $category = Category::create(['household_id' => $household->id, 'name' => 'Belanja', 'type' => 'expense']);

        $budget = Budget::create([
            'household_id' => $household->id,
            'category_id' => $category->id,
            'amount' => 2500000,
            'month_year' => now()->format('Y-m'),
        ]);

        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'amount' => 2500000,
        ]);
    }
}

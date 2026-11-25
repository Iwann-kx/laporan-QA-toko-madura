<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\FinanceEntry;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class FinanceEntryControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_create_update_and_delete_a_finance_entry(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $createResponse = $this->post(route('finance-entries.store'), [
            'tanggal' => '2026-10-19',
            'type' => 'expense',
            'category' => 'Listrik',
            'description' => 'Tagihan listrik toko',
            'amount' => 250000,
            'created_by' => User::factory()->create(['role' => 'cashier'])->id,
        ]);

        $createResponse->assertRedirect();
        $entry = FinanceEntry::firstOrFail();
        $this->assertDatabaseHas('finance_entries', [
            'id' => $entry->id,
            'created_by' => $owner->id,
            'amount' => 250000,
        ]);

        $updateResponse = $this->put(route('finance-entries.update', $entry), [
            'tanggal' => '2026-10-18',
            'type' => 'income',
            'category' => 'Modal',
            'description' => 'Tambahan modal toko',
            'amount' => 500000,
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('finance_entries', [
            'id' => $entry->id,
            'type' => 'income',
            'category' => 'Modal',
            'amount' => 500000,
        ]);

        $deleteResponse = $this->delete(route('finance-entries.destroy', $entry));

        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('finance_entries', ['id' => $entry->id]);
    }

    public function test_cashier_cannot_create_a_finance_entry(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $entry = FinanceEntry::factory()->create(['created_by' => $owner->id]);
        $this->actingAs(User::factory()->create(['role' => 'cashier']));

        $response = $this->post(route('finance-entries.store'), [
            'tanggal' => '2026-10-19',
            'type' => 'income',
            'category' => 'Modal',
            'description' => 'Pemasukan tambahan',
            'amount' => 100000,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('finance_entries', 1);

        $this->put(route('finance-entries.update', $entry), [
            'tanggal' => '2026-10-19',
            'type' => 'income',
            'category' => 'Modal',
            'description' => 'Pemasukan tambahan',
            'amount' => 100000,
        ])->assertForbidden();
        $this->delete(route('finance-entries.destroy', $entry))->assertForbidden();
        $this->assertDatabaseHas('finance_entries', [
            'id' => $entry->id,
            'amount' => $entry->amount,
        ]);
    }

    public function test_invalid_finance_entry_amount_is_rejected(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'owner']));

        $response = $this->from(route('laporan'))->post(route('finance-entries.store'), [
            'tanggal' => '2026-10-19',
            'type' => 'expense',
            'category' => 'Listrik',
            'description' => 'Tagihan listrik',
            'amount' => 0,
        ]);

        $response->assertRedirect(route('laporan'))->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('finance_entries', 0);
    }

    public function test_report_separates_cash_flow_from_profit_and_loss(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);
        $this->freezeTime();

        Penjualan::factory()->create([
            'tanggal' => '2026-10-10 10:00:00',
            'total' => 30000,
        ])->details()->create([
            'barang_id' => Barang::factory()->create()->id,
            'qty' => 2,
            'harga' => 15000,
            'harga_modal' => 10000,
            'subtotal' => 30000,
        ]);
        Pembelian::factory()->create([
            'tanggal' => '2026-10-11 10:00:00',
            'total' => 8000,
        ]);
        FinanceEntry::factory()->create([
            'tanggal' => '2026-10-12',
            'type' => 'income',
            'amount' => 5000,
            'created_by' => $owner->id,
        ]);
        FinanceEntry::factory()->create([
            'tanggal' => '2026-10-13',
            'type' => 'expense',
            'amount' => 2000,
            'created_by' => $owner->id,
        ]);
        FinanceEntry::factory()->create([
            'tanggal' => '2026-10-14',
            'type' => 'capital_in',
            'amount' => 4000,
            'created_by' => $owner->id,
        ]);
        FinanceEntry::factory()->create([
            'tanggal' => '2026-10-15',
            'type' => 'owner_draw',
            'amount' => 1000,
            'created_by' => $owner->id,
        ]);
        Penjualan::factory()->create(['tanggal' => '2026-10-01 10:00:00', 'total' => 10000]);
        Pembelian::factory()->create(['tanggal' => '2026-10-02 10:00:00', 'total' => 3000]);
        FinanceEntry::factory()->create([
            'tanggal' => '2026-10-03',
            'type' => 'income',
            'amount' => 2000,
            'created_by' => $owner->id,
        ]);
        FinanceEntry::factory()->create([
            'tanggal' => '2026-10-04',
            'type' => 'expense',
            'amount' => 1000,
            'created_by' => $owner->id,
        ]);

        $response = $this->get(route('laporan', [
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-19',
        ]));

        $response->assertOk()
            ->assertViewHas('openingBalance', 8000)
            ->assertViewHas('cashInTotal', 39000)
            ->assertViewHas('cashOutTotal', 11000)
            ->assertViewHas('cashFlowNet', 28000)
            ->assertViewHas('closingBalance', 36000)
            ->assertViewHas('costOfGoodsSold', 20000)
            ->assertViewHas('netProfit', 13000)
            ->assertViewHas('missingCostCount', 0)
            ->assertViewHas('ledgerEntries', fn ($entries): bool => $entries->last()['balance'] === 36000);
    }

    public function test_report_marks_profit_incomplete_for_sales_without_historical_cost(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'owner']));
        Penjualan::factory()->create(['tanggal' => '2026-10-10 10:00:00'])
            ->details()->create([
                'barang_id' => Barang::factory()->create()->id,
                'qty' => 1,
                'harga' => 15000,
                'subtotal' => 15000,
            ]);

        $response = $this->get(route('laporan', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-19',
        ]));

        $response->assertOk()
            ->assertViewHas('missingCostCount', 1)
            ->assertViewHas('netProfit', null);
    }

    public function test_report_escapes_manual_finance_descriptions(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        FinanceEntry::factory()->create([
            'created_by' => $owner->id,
            'tanggal' => '2026-10-19',
            'description' => '<script>alert(1)</script>',
        ]);
        $this->actingAs($owner);

        $response = $this->get(route('laporan', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]));

        $response->assertSee('<script>alert(1)</script>');
        $response->assertDontSee('<script>alert(1)</script>', false);
    }
}

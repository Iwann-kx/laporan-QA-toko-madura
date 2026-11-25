<?php

namespace App\Http\Controllers;

use App\Models\FinanceEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FinanceEntryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'type' => ['required', 'in:income,expense,capital_in,owner_draw'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1', 'max:4294967295'],
        ]);

        $data['created_by'] = $request->user()->id;
        FinanceEntry::create($data);

        return back()->with('success', 'Catatan keuangan berhasil ditambahkan.');
    }

    public function update(Request $request, FinanceEntry $financeEntry): RedirectResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'type' => ['required', 'in:income,expense,capital_in,owner_draw'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1', 'max:4294967295'],
        ]);

        $financeEntry->update($data);

        return back()->with('success', 'Catatan keuangan berhasil diperbarui.');
    }

    public function destroy(FinanceEntry $financeEntry): RedirectResponse
    {
        $financeEntry->delete();

        return back()->with('success', 'Catatan keuangan berhasil dihapus.');
    }
}

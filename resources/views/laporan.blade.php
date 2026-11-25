@extends('layouts.warung')

@section('title', 'Laporan')
@section('section', 'Laporan keuangan')

@section('content')
<div class="page-heading">
    <div><div class="eyebrow">Arus kas dan hasil usaha</div><h1>Buku kas &amp; laporan keuangan</h1><p>Pantau uang masuk, uang keluar, dan laba-rugi sesuai rentang tanggal.</p></div>
    <div class="heading-actions"><button class="button secondary" type="button" onclick="window.print()"><i class="bi bi-printer"></i> Cetak</button></div>
</div>

<section class="panel" style="margin-bottom:16px">
    <div class="panel-body">
        <form class="filter-bar" method="GET" action="{{ route('laporan') }}">
            <div class="field"><label for="start-date">Tanggal mulai</label><input class="control" id="start-date" name="start_date" type="date" value="{{ $startDate }}" required></div>
            <div class="field"><label for="end-date">Tanggal akhir</label><input class="control" id="end-date" name="end_date" type="date" value="{{ $endDate }}" required></div>
            <button class="button" type="submit"><i class="bi bi-funnel"></i> Terapkan</button>
        </form>
    </div>
</section>

<section class="stats" aria-label="Ringkasan laporan">
    <div class="stat" style="--accent:var(--green)"><i class="bi bi-arrow-down-left stat-icon"></i><div class="stat-label">Total uang masuk</div><div class="stat-value">Rp {{ number_format($cashInTotal, 0, ',', '.') }}</div><div class="stat-foot">Penjualan Rp {{ number_format($salesTotal, 0, ',', '.') }} + pemasukan lain/modal Rp {{ number_format($otherIncomeTotal + $capitalInTotal, 0, ',', '.') }}</div></div>
    <div class="stat" style="--accent:var(--amber)"><i class="bi bi-arrow-up-right stat-icon"></i><div class="stat-label">Total uang keluar</div><div class="stat-value">Rp {{ number_format($cashOutTotal, 0, ',', '.') }}</div><div class="stat-foot">Stok Rp {{ number_format($purchasesTotal, 0, ',', '.') }} + biaya/prive Rp {{ number_format($otherExpenseTotal + $ownerDrawTotal, 0, ',', '.') }}</div></div>
    <div class="stat" style="--accent:var(--coral)"><i class="bi bi-cash-coin stat-icon"></i><div class="stat-label">Arus kas bersih</div><div class="stat-value">Rp {{ number_format($cashFlowNet, 0, ',', '.') }}</div><div class="stat-foot">Saldo akhir tercatat Rp {{ number_format($closingBalance, 0, ',', '.') }}</div></div>
    <div class="stat" style="--accent:#7c9638"><i class="bi bi-calculator stat-icon"></i><div class="stat-label">Laba / rugi bersih</div><div class="stat-value" style="font-size:{{ $netProfit === null ? '16px' : '23px' }}">{{ $netProfit === null ? 'Belum lengkap' : 'Rp '.number_format($netProfit, 0, ',', '.') }}</div><div class="stat-foot">{{ $netProfit === null ? $missingCostCount.' rincian penjualan lama belum memiliki HPP' : 'Setelah HPP dan pengeluaran lain' }}</div></div>
</section>

@if (auth()->user()->isOwner())
    <section class="panel" style="margin-bottom:16px">
        <div class="panel-head"><div><h2>Ringkasan laba-rugi</h2><small>Pembelian stok adalah arus kas keluar; HPP memakai harga beli produk saat penjualan dicatat.</small></div></div>
        <div class="table-wrap">
            <table style="min-width:0">
                <tbody>
                    <tr><td>Pendapatan penjualan</td><td class="text-right">Rp {{ number_format($salesTotal, 0, ',', '.') }}</td></tr>
                    <tr><td>Pendapatan lain (tidak termasuk setoran modal)</td><td class="text-right">Rp {{ number_format($otherIncomeTotal, 0, ',', '.') }}</td></tr>
                    <tr><td>Harga pokok barang terjual (HPP)</td><td class="text-right">Rp {{ number_format($costOfGoodsSold, 0, ',', '.') }}</td></tr>
                    <tr><td>Biaya/pengeluaran lain</td><td class="text-right">Rp {{ number_format($otherExpenseTotal, 0, ',', '.') }}</td></tr>
                    <tr>
                        <td class="table-primary">Laba / rugi bersih tercatat</td>
                        <td class="text-right table-primary">{{ $netProfit === null ? 'Belum dapat dihitung lengkap' : 'Rp '.number_format($netProfit, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if ($missingCostCount > 0)
            <div class="notice" style="margin:12px 16px">Ada {{ $missingCostCount }} rincian penjualan lama yang belum memiliki HPP historis. Nilai laba/rugi ditampilkan belum lengkap; penjualan baru akan menyimpan HPP saat transaksi dicatat.</div>
        @endif
    </section>

    <section class="panel" style="margin-bottom:16px">
        <div class="panel-head"><div><h2>Catat arus keuangan lain</h2><small>Pilih kategori yang tepat agar setoran modal dan prive tidak dihitung sebagai laba atau biaya.</small></div></div>
        <div class="panel-body">
            <form method="POST" action="{{ route('finance-entries.store') }}">
                @csrf
                <div class="field-grid">
                    <div class="field">
                        <label for="entry-date">Tanggal</label>
                        <input class="control" id="entry-date" name="tanggal" type="date" value="{{ old('tanggal', today()->toDateString()) }}" required>
                    </div>
                    <div class="field">
                        <label for="entry-type">Jenis arus</label>
                        <select class="control" id="entry-type" name="type" required>
                            <option value="income" @selected(old('type') === 'income')>Pendapatan lain</option>
                            <option value="expense" @selected(old('type') === 'expense')>Biaya usaha</option>
                            <option value="capital_in" @selected(old('type') === 'capital_in')>Setoran modal</option>
                            <option value="owner_draw" @selected(old('type') === 'owner_draw')>Prive / penarikan owner</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="entry-category">Kategori / tujuan</label>
                        <input class="control" id="entry-category" name="category" type="text" value="{{ old('category') }}" maxlength="100" placeholder="Contoh: listrik, sewa, modal" required>
                    </div>
                    <div class="field">
                        <label for="entry-amount">Jumlah (Rp)</label>
                        <input class="control" id="entry-amount" name="amount" type="number" min="1" step="1" value="{{ old('amount') }}" required>
                    </div>
                    <div class="field" style="grid-column:1/-1">
                        <label for="entry-description">Keterangan</label>
                        <input class="control" id="entry-description" name="description" type="text" value="{{ old('description') }}" maxlength="255" placeholder="Jelaskan sumber pemasukan atau tujuan pengeluaran" required>
                    </div>
                </div>
                <div class="form-actions"><button class="button" type="submit"><i class="bi bi-plus-lg"></i> Simpan catatan</button></div>
            </form>
        </div>
    </section>

    <section class="panel" style="margin-bottom:16px">
        <div class="panel-head">
            <div><h2>Buku kas</h2><small>Saldo awal tercatat: Rp {{ number_format($openingBalance, 0, ',', '.') }}</small></div>
            <span class="badge green">Saldo akhir Rp {{ number_format($closingBalance, 0, ',', '.') }}</span>
        </div>
        <div class="panel-body muted">Saldo dihitung dari transaksi yang tercatat. Saldo kas/modal awal sebelum pencatatan sistem perlu dimasukkan sebagai setoran modal agar ikut terhitung.</div>
        @if ($ledgerEntries->isEmpty())
            <div class="empty">Belum ada arus keuangan dalam periode ini.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Tanggal</th><th>Jenis</th><th>Kategori / keterangan</th><th>Referensi</th><th class="text-right">Uang masuk</th><th class="text-right">Uang keluar</th><th class="text-right">Saldo berjalan</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <tr><td>{{ \Illuminate\Support\Carbon::parse($startDate)->subDay()->format('d/m/Y') }}</td><td colspan="5">Saldo awal periode</td><td class="text-right table-primary">Rp {{ number_format($openingBalance, 0, ',', '.') }}</td><td></td></tr>
                        @foreach ($ledgerEntries as $ledgerEntry)
                            <tr>
                                <td>{{ $ledgerEntry['date']->format('d/m/Y') }}</td>
                                <td><span class="badge {{ $ledgerEntry['direction'] === 'income' ? 'green' : 'amber' }}">{{ $ledgerEntry['flowLabel'] }}</span></td>
                                <td><span class="table-primary">{{ $ledgerEntry['category'] }}</span><span class="table-sub">{{ $ledgerEntry['description'] }}</span></td>
                                <td class="mono">{{ $ledgerEntry['reference'] ?? '—' }}</td>
                                <td class="text-right">{{ $ledgerEntry['direction'] === 'income' ? 'Rp '.number_format($ledgerEntry['amount'], 0, ',', '.') : '—' }}</td>
                                <td class="text-right">{{ $ledgerEntry['direction'] === 'expense' ? 'Rp '.number_format($ledgerEntry['amount'], 0, ',', '.') : '—' }}</td>
                                <td class="text-right table-primary">Rp {{ number_format($ledgerEntry['balance'], 0, ',', '.') }}</td>
                                <td>
                                    @if ($ledgerEntry['source'] === 'manual')
                                        <details>
                                            <summary class="panel-link">Ubah</summary>
                                            <form method="POST" action="{{ route('finance-entries.update', $ledgerEntry['entry']) }}" style="min-width:220px;padding-top:10px">
                                                @csrf
                                                @method('PUT')
                                                <div class="field"><label>Tanggal</label><input class="control" name="tanggal" type="date" value="{{ $ledgerEntry['entry']->tanggal->toDateString() }}" required></div>
                                                <div class="field"><label>Jenis</label><select class="control" name="type" required><option value="income" @selected($ledgerEntry['type'] === 'income')>Pendapatan lain</option><option value="expense" @selected($ledgerEntry['type'] === 'expense')>Biaya usaha</option><option value="capital_in" @selected($ledgerEntry['type'] === 'capital_in')>Setoran modal</option><option value="owner_draw" @selected($ledgerEntry['type'] === 'owner_draw')>Prive / penarikan owner</option></select></div>
                                                <div class="field"><label>Kategori / tujuan</label><input class="control" name="category" type="text" value="{{ $ledgerEntry['category'] }}" maxlength="100" required></div>
                                                <div class="field"><label>Keterangan</label><input class="control" name="description" type="text" value="{{ $ledgerEntry['description'] }}" maxlength="255" required></div>
                                                <div class="field"><label>Jumlah (Rp)</label><input class="control" name="amount" type="number" min="1" step="1" value="{{ $ledgerEntry['amount'] }}" required></div>
                                                <div class="form-actions"><button class="button small" type="submit">Simpan perubahan</button></div>
                                            </form>
                                            <form method="POST" action="{{ route('finance-entries.destroy', $ledgerEntry['entry']) }}" onsubmit="return confirm('Hapus catatan keuangan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="button danger small" type="submit">Hapus</button>
                                            </form>
                                        </details>
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endif

<div class="two-tables">
    <section class="panel">
        <div class="panel-head"><div><h2>Penjualan</h2><small>{{ $sales->count() }} transaksi dalam periode ini</small></div><span class="badge green">Rp {{ number_format($salesTotal, 0, ',', '.') }}</span></div>
        @if ($sales->isEmpty())
            <div class="empty">Tidak ada transaksi penjualan pada periode ini.</div>
        @else
            <div class="table-wrap"><table><thead><tr><th>No. transaksi</th><th>Tanggal</th><th class="text-right">Total</th></tr></thead><tbody>
                @foreach ($sales as $sale)
                    <tr><td class="table-primary mono">{{ $sale->no_transaksi }}</td><td class="muted">{{ $sale->tanggal?->format('d/m/Y H:i') }}</td><td class="text-right table-primary">Rp {{ number_format($sale->total, 0, ',', '.') }}</td></tr>
                @endforeach
            </tbody></table></div>
        @endif
    </section>
    <section class="panel">
        <div class="panel-head"><div><h2>Pembelian</h2><small>{{ $purchases->count() }} faktur dalam periode ini</small></div><span class="badge amber">Rp {{ number_format($purchasesTotal, 0, ',', '.') }}</span></div>
        @if ($purchases->isEmpty())
            <div class="empty">Tidak ada transaksi pembelian pada periode ini.</div>
        @else
            <div class="table-wrap"><table><thead><tr><th>No. faktur</th><th>Supplier / tanggal</th><th class="text-right">Total</th></tr></thead><tbody>
                @foreach ($purchases as $purchase)
                    <tr><td class="table-primary mono">{{ $purchase->no_faktur }}</td><td><span>{{ $purchase->supplier->nama_supplier }}</span><span class="table-sub">{{ $purchase->tanggal?->format('d/m/Y H:i') }}</span></td><td class="text-right table-primary">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td></tr>
                @endforeach
            </tbody></table></div>
        @endif
    </section>
</div>
@endsection
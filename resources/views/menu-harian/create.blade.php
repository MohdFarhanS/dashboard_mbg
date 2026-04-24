@extends('layouts.app')
@section('title', 'Tambah Menu Harian')

@push('styles')
<style>
    .bahan-row { background: #f8f9fa; border-radius: 8px; padding: 10px; margin-bottom: 8px; position: relative; }
    .autocomplete-list { position: absolute; z-index: 1000; background: white;
        border: 1px solid #dee2e6; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,.1);
        max-height: 220px; overflow-y: auto; width: 100%; left: 0; top: 100%; }
    .autocomplete-item { padding: 8px 12px; cursor: pointer; font-size: .875rem;
        border-bottom: 1px solid #f0f0f0; }
    .autocomplete-item:hover { background: var(--primary-pale); }
    .autocomplete-item .kode { color: var(--primary); font-size: .75rem; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('menu-harian.index') }}" class="btn btn-sm btn-outline-secondary me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold mb-0" style="color:var(--primary)">Tambah Menu Harian</h4>
            <small class="text-muted">Unit: {{ auth()->user()->unit_sppg }}</small>
        </div>
    </div>

    @if($existing)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Menu untuk hari ini sudah ada.
        <a href="{{ route('menu-harian.edit', $existing) }}" class="fw-semibold">Edit menu hari ini →</a>
    </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('menu-harian.store') }}" method="POST" id="formMenu">
        @csrf
        <div class="row g-4">

            {{-- Kolom kiri: info --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top:80px">
                    <div class="card-header border-0 fw-semibold" style="background:var(--primary-pale)">
                        <i class="fas fa-info-circle me-2" style="color:var(--primary)"></i>Informasi Menu
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control"
                                   value="{{ old('tanggal', today()->format('Y-m-d')) }}" required>
                            @error('tanggal')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Unit SPPG</label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ auth()->user()->unit_sppg }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Menu</label>
                            <input type="text" name="nama_menu" class="form-control"
                                   placeholder="Contoh: Nasi Ayam Sayur"
                                   value="{{ old('nama_menu') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan</label>
                            <textarea name="catatan" class="form-control" rows="2"
                                      placeholder="Opsional...">{{ old('catatan') }}</textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Simpan sebagai</label>
                            <select name="status" class="form-select">
                                <option value="draft">Draft (bisa diedit)</option>
                                <option value="final">Final (terkunci)</option>
                            </select>
                        </div>

                        {{-- Estimasi gizi real-time --}}
                        <div class="card border-0" style="background:var(--primary-pale)">
                            <div class="card-body p-3">
                                <div class="fw-semibold small mb-2" style="color:var(--primary)">
                                    <i class="fas fa-calculator me-1"></i>Estimasi Gizi (Makan Siang)
                                </div>
                                <div class="row g-1 text-center">
                                    <div class="col-6">
                                        <div class="bg-white rounded p-2">
                                            <div class="fw-bold fs-5" id="sum-energi">0</div>
                                            <div class="text-muted" style="font-size:.7rem">Energi (kkal)</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-white rounded p-2">
                                            <div class="fw-bold fs-5" id="sum-protein">0</div>
                                            <div class="text-muted" style="font-size:.7rem">Protein (g)</div>
                                        </div>
                                    </div>
                                    <div class="col-6 mt-1">
                                        <div class="bg-white rounded p-2">
                                            <div class="fw-bold fs-5" id="sum-lemak">0</div>
                                            <div class="text-muted" style="font-size:.7rem">Lemak (g)</div>
                                        </div>
                                    </div>
                                    <div class="col-6 mt-1">
                                        <div class="bg-white rounded p-2">
                                            <div class="fw-bold fs-5" id="sum-karbo">0</div>
                                            <div class="text-muted" style="font-size:.7rem">Karbo (g)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 d-grid gap-2">
                        <button type="submit" class="btn btn-primary"
                                style="background:var(--primary);border-color:var(--primary)">
                            <i class="fas fa-save me-2"></i>Simpan Menu
                        </button>
                        <a href="{{ route('menu-harian.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </div>
            </div>

            {{-- Kolom kanan: daftar bahan --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center"
                         style="background:#d1e7dd">
                        <span class="fw-semibold" style="color:#0a3622">
                            <i class="fas fa-cloud-sun me-2"></i>Bahan Pangan — Makan Siang
                        </span>
                        <span class="text-muted small" id="jumlah-bahan-label">0 bahan</span>
                    </div>
                    <div class="card-body">
                        {{-- Header kolom --}}
                        <div class="row g-2 mb-2 px-1">
                            <div class="col-md-5"><small class="fw-semibold text-muted">Nama Bahan (TKPI)</small></div>
                            <div class="col-md-2"><small class="fw-semibold text-muted">Gram/Porsi</small></div>
                            <div class="col-md-2"><small class="fw-semibold text-muted">Jml Porsi</small></div>
                            <div class="col-md-2"><small class="fw-semibold text-muted">Energi</small></div>
                            <div class="col-md-1"></div>
                        </div>

                        <div id="bahan-list">
                            <div class="text-center text-muted py-4" id="empty-msg">
                                <i class="fas fa-carrot fa-2x mb-2 d-block opacity-25"></i>
                                Belum ada bahan ditambahkan
                            </div>
                        </div>

                        <button type="button" id="btn-tambah-bahan"
                                class="btn btn-outline-success btn-sm mt-3"
                                style="border-color:var(--primary);color:var(--primary)">
                            <i class="fas fa-plus me-1"></i> Tambah Bahan
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<template id="tpl-bahan">
    <div class="bahan-row" data-idx="__IDX__">
        <div class="row g-2 align-items-center">
            <div class="col-md-5 position-relative">
                <input type="text" class="form-control form-control-sm bahan-search"
                       placeholder="Cari nama / kode TKPI..." autocomplete="off">
                <input type="hidden" class="bahan-id">
                <input type="hidden" class="bahan-energi" value="0">
                <input type="hidden" class="bahan-protein" value="0">
                <input type="hidden" class="bahan-lemak" value="0">
                <input type="hidden" class="bahan-karbo" value="0">
                <input type="hidden" class="bahan-bdd" value="100">
                <div class="autocomplete-list d-none"></div>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control form-control-sm jumlah-gram"
                       placeholder="gram" min="1" step="0.1">
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control form-control-sm jumlah-porsi"
                       placeholder="porsi" min="1" value="1">
            </div>
            <div class="col-md-2">
                <small class="energi-preview text-muted">— kkal</small>
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger btn-hapus">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="mt-1 ps-1">
            <small class="bahan-label text-muted fst-italic">Pilih bahan terlebih dahulu</small>
        </div>
    </div>
</template>

@push('scripts')
<script>
let bahanCounter = 0;
let nutrisiState = {};

document.getElementById('btn-tambah-bahan').addEventListener('click', () => tambahBahan());

function tambahBahan(prefillData = null) {
    const tpl   = document.getElementById('tpl-bahan');
    const clone = tpl.content.cloneNode(true);
    const idx   = bahanCounter++;
    const list  = document.getElementById('bahan-list');

    // Set name attributes SEBELUM append
    clone.querySelector('.bahan-id').name     = `bahans[${idx}][bahan_pangan_id]`;
    clone.querySelector('.jumlah-gram').name  = `bahans[${idx}][jumlah_gram]`;
    clone.querySelector('.jumlah-porsi').name = `bahans[${idx}][jumlah_porsi]`;
    clone.querySelector('.bahan-row').setAttribute('data-idx', idx);

    document.getElementById('empty-msg')?.remove();

    // Append ke DOM
    list.appendChild(clone);

    // ✅ Ambil row pakai lastElementChild — paling reliable
    const row = list.lastElementChild;

    // Event hapus
    row.querySelector('.btn-hapus').addEventListener('click', function () {
        this.closest('.bahan-row').remove();
        delete nutrisiState[idx];
        updateGiziSummary();
        updateJumlahBahan();
        if (!document.querySelector('.bahan-row')) {
            document.getElementById('bahan-list').innerHTML =
                `<div class="text-center text-muted py-4" id="empty-msg">
                    <i class="fas fa-carrot fa-2x mb-2 d-block opacity-25"></i>
                    Belum ada bahan ditambahkan
                </div>`;
        }
    });

    // ✅ Autocomplete — pakai row dari lastElementChild
    const searchInput = row.querySelector('.bahan-search');
    const acList      = row.querySelector('.autocomplete-list');
    let debounce;

    searchInput.addEventListener('input', function () {
        clearTimeout(debounce);
        const q = this.value.trim();
        if (q.length < 2) { acList.classList.add('d-none'); return; }
        debounce = setTimeout(() => fetchBahan(q, acList, row, idx), 300);
    });

    document.addEventListener('click', e => {
        if (!row.contains(e.target)) acList.classList.add('d-none');
    });

    row.querySelector('.jumlah-gram').addEventListener('input',  () => recalcRow(row, idx));
    row.querySelector('.jumlah-porsi').addEventListener('input', () => recalcRow(row, idx));

    updateJumlahBahan();

    if (prefillData && prefillData.id) {
        pilihBahan(prefillData, row, idx, null);
        row.querySelector('.jumlah-gram').value  = prefillData.jumlah_gram;
        row.querySelector('.jumlah-porsi').value = prefillData.jumlah_porsi;
        recalcRow(row, idx);
    } else {
        row.querySelector('.bahan-search').value      = '';
        row.querySelector('.bahan-label').textContent = 'Pilih bahan terlebih dahulu';
        searchInput.focus();
    }
}

async function fetchBahan(q, acList, row, idx) {
    try {
        const res  = await fetch(`/api/bahan-pangan/search?q=${encodeURIComponent(q)}&limit=8`);
        const data = await res.json();
        acList.innerHTML = '';
        if (!data.length) {
            acList.innerHTML = '<div class="autocomplete-item text-muted">Tidak ditemukan</div>';
        } else {
            data.forEach(b => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item';
                item.innerHTML = `<span class="kode">${b.kode}</span> — ${b.nama_bahan}
                    <span class="text-muted ms-2" style="font-size:.7rem">${b.kategori}</span>`;
                item.addEventListener('click', () => pilihBahan(b, row, idx, acList));
                acList.appendChild(item);
            });
        }
        acList.classList.remove('d-none');
    } catch (e) { console.error(e); }
}

function pilihBahan(b, row, idx, acList) {
    if (!b || !b.id) return;

    const bdd    = b.bdd         ?? 100;
    const energi = b.energi      ?? 0;
    const protein= b.protein     ?? 0;
    const lemak  = b.lemak       ?? 0;
    const karbo  = b.karbohidrat ?? 0;

    row.querySelector('.bahan-search').value  = `${b.kode} — ${b.nama_bahan}`;
    row.querySelector('.bahan-id').value       = b.id;
    row.querySelector('.bahan-energi').value   = energi;
    row.querySelector('.bahan-protein').value  = protein;
    row.querySelector('.bahan-lemak').value    = lemak;
    row.querySelector('.bahan-karbo').value    = karbo;
    row.querySelector('.bahan-bdd').value      = bdd;
    row.querySelector('.bahan-label').textContent =
        `${b.kategori} | Energi: ${energi} kkal | Protein: ${protein} g | BDD: ${bdd}%`;

    if (acList) acList.classList.add('d-none');
    recalcRow(row, idx);
}

function recalcRow(row, idx) {
    const gram   = parseFloat(row.querySelector('.jumlah-gram').value)  || 0;
    const porsi  = parseInt(row.querySelector('.jumlah-porsi').value)   || 1;
    const bdd    = parseFloat(row.querySelector('.bahan-bdd').value)    || 100;
    const faktor = (gram * (bdd / 100)) / 100;

    const e = faktor * (parseFloat(row.querySelector('.bahan-energi').value)  || 0) * porsi;
    const p = faktor * (parseFloat(row.querySelector('.bahan-protein').value) || 0) * porsi;
    const l = faktor * (parseFloat(row.querySelector('.bahan-lemak').value)   || 0) * porsi;
    const k = faktor * (parseFloat(row.querySelector('.bahan-karbo').value)   || 0) * porsi;

    nutrisiState[idx] = { e, p, l, k };
    row.querySelector('.energi-preview').textContent = `${e.toFixed(1)} kkal`;
    updateGiziSummary();
}

function updateGiziSummary() {
    let totE=0, totP=0, totL=0, totK=0;
    Object.values(nutrisiState).forEach(n => { totE+=n.e; totP+=n.p; totL+=n.l; totK+=n.k; });
    document.getElementById('sum-energi').textContent  = totE.toFixed(1);
    document.getElementById('sum-protein').textContent = totP.toFixed(1);
    document.getElementById('sum-lemak').textContent   = totL.toFixed(1);
    document.getElementById('sum-karbo').textContent   = totK.toFixed(1);
}

function updateJumlahBahan() {
    const n = document.querySelectorAll('.bahan-row').length;
    document.getElementById('jumlah-bahan-label').textContent = `${n} bahan`;
}
</script>
@endpush
@endsection
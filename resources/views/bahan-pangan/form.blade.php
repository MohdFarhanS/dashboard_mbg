@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="container-fluid py-4">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('bahan-pangan.index') }}">Bahan Pangan</a></li>
            <li class="breadcrumb-item active">{{ $title }}</li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm" style="max-width: 900px; margin: 0 auto;">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0" style="color: var(--primary);">
                <i class="fas fa-{{ $bahan ? 'edit' : 'plus' }} me-2"></i>{{ $title }}
            </h5>
        </div>
        <div class="card-body p-4">

            @if($errors->any())
            <div class="alert alert-danger py-2 mb-4">
                <ul class="mb-0 small">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST"
                  action="{{ $bahan ? route('bahan-pangan.update', $bahan) : route('bahan-pangan.store') }}">
                @csrf
                @if($bahan) @method('PUT') @endif

                {{-- === INFO DASAR === --}}
                <h6 class="fw-semibold text-muted text-uppercase mb-3 border-bottom pb-2" style="font-size:.75rem; letter-spacing:.05em;">
                    Informasi Dasar
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Kode <span class="text-danger">*</span></label>
                        <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                               value="{{ old('kode', $bahan?->kode) }}"
                               placeholder="mis. AR001" maxlength="10" style="font-family:monospace">
                        @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Kode Lama</label>
                        <input type="text" name="kode_lama" class="form-control form-control-sm"
                               value="{{ old('kode_lama', $bahan?->kode_lama) }}"
                               placeholder="Opsional" maxlength="10" style="font-family:monospace">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Nama Bahan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_bahan" class="form-control @error('nama_bahan') is-invalid @enderror"
                               value="{{ old('nama_bahan', $bahan?->nama_bahan) }}"
                               placeholder="mis. Beras giling, mentah">
                        @error('nama_bahan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori" class="form-select @error('kategori') is-invalid @enderror">
                            <option value="">— Pilih Kategori —</option>
                            @foreach($kategoriList as $kat)
                            <option value="{{ $kat }}" {{ old('kategori', $bahan?->kategori) === $kat ? 'selected' : '' }}>
                                {{ $kat }}
                            </option>
                            @endforeach
                        </select>
                        @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Jenis</label>
                        <select name="sub_kategori" class="form-select">
                            <option value="">— Pilih Jenis —</option>
                            <option value="TUNGGAL" {{ old('sub_kategori', $bahan?->sub_kategori) === 'TUNGGAL' ? 'selected' : '' }}>Tunggal / Single</option>
                            <option value="OLAHAN"  {{ old('sub_kategori', $bahan?->sub_kategori) === 'OLAHAN'  ? 'selected' : '' }}>Olahan / Produk</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Sumber Data</label>
                        <input type="text" name="sumber" class="form-control"
                               value="{{ old('sumber', $bahan?->sumber) }}"
                               placeholder="mis. KZGMI-2001">
                    </div>
                </div>

                {{-- === PROKSIMAT === --}}
                <h6 class="fw-semibold text-muted text-uppercase mb-3 border-bottom pb-2" style="font-size:.75rem; letter-spacing:.05em;">
                    Proksimat <small class="text-muted fw-normal normal-case">(per 100g BDD)</small>
                </h6>
                <div class="row g-3 mb-4">
                    @php
                    $proksimat = [
                        ['key'=>'bdd',         'label'=>'BDD',         'unit'=>'%'],
                        ['key'=>'air',         'label'=>'Air',         'unit'=>'g'],
                        ['key'=>'energi',      'label'=>'Energi',      'unit'=>'Kal'],
                        ['key'=>'protein',     'label'=>'Protein',     'unit'=>'g'],
                        ['key'=>'lemak',       'label'=>'Lemak',       'unit'=>'g'],
                        ['key'=>'karbohidrat', 'label'=>'Karbohidrat', 'unit'=>'g'],
                        ['key'=>'serat',       'label'=>'Serat',       'unit'=>'g'],
                        ['key'=>'abu',         'label'=>'Abu',         'unit'=>'g'],
                    ];
                    @endphp
                    @foreach($proksimat as $col)
                    <div class="col-md-3 col-6">
                        <label class="form-label small fw-semibold mb-1">
                            {{ $col['label'] }}
                            <span class="text-muted fw-normal">({{ $col['unit'] }})</span>
                        </label>
                        <input type="number" step="0.01" name="{{ $col['key'] }}"
                               class="form-control form-control-sm @error($col['key']) is-invalid @enderror"
                               value="{{ old($col['key'], $bahan?->{$col['key']}) }}"
                               placeholder="—">
                        @error($col['key'])<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @endforeach
                </div>

                {{-- === MINERAL === --}}
                <h6 class="fw-semibold text-muted text-uppercase mb-3 border-bottom pb-2" style="font-size:.75rem; letter-spacing:.05em;">
                    Mineral <small class="text-muted fw-normal">(mg per 100g BDD)</small>
                </h6>
                <div class="row g-3 mb-4">
                    @php
                    $mineral = [
                        ['key'=>'kalsium','label'=>'Kalsium (Ca)'],
                        ['key'=>'fosfor','label'=>'Fosfor (P)'],
                        ['key'=>'besi','label'=>'Besi (Fe)'],
                        ['key'=>'natrium','label'=>'Natrium (Na)'],
                        ['key'=>'kalium','label'=>'Kalium (K)'],
                        ['key'=>'tembaga','label'=>'Tembaga (Cu)'],
                        ['key'=>'seng','label'=>'Seng (Zn)'],
                    ];
                    @endphp
                    @foreach($mineral as $col)
                    <div class="col-md-3 col-6">
                        <label class="form-label small fw-semibold mb-1">{{ $col['label'] }}</label>
                        <input type="number" step="0.01" name="{{ $col['key'] }}"
                               class="form-control form-control-sm"
                               value="{{ old($col['key'], $bahan?->{$col['key']}) }}"
                               placeholder="—">
                    </div>
                    @endforeach
                </div>

                {{-- === VITAMIN === --}}
                <h6 class="fw-semibold text-muted text-uppercase mb-3 border-bottom pb-2" style="font-size:.75rem; letter-spacing:.05em;">
                    Vitamin
                </h6>
                <div class="row g-3 mb-4">
                    @php
                    $vitamin = [
                        ['key'=>'retinol',    'label'=>'Retinol',         'unit'=>'mcg'],
                        ['key'=>'b_karoten',  'label'=>'β-Karoten',       'unit'=>'mcg'],
                        ['key'=>'kar_total',  'label'=>'Karoten Total',   'unit'=>'mcg'],
                        ['key'=>'thiamin',    'label'=>'Thiamin (B1)',     'unit'=>'mg'],
                        ['key'=>'riboflavin', 'label'=>'Riboflavin (B2)', 'unit'=>'mg'],
                        ['key'=>'niasin',     'label'=>'Niasin (B3)',     'unit'=>'mg'],
                        ['key'=>'vit_c',      'label'=>'Vitamin C',       'unit'=>'mg'],
                    ];
                    @endphp
                    @foreach($vitamin as $col)
                    <div class="col-md-3 col-6">
                        <label class="form-label small fw-semibold mb-1">
                            {{ $col['label'] }}
                            <span class="text-muted fw-normal">({{ $col['unit'] }})</span>
                        </label>
                        <input type="number" step="0.01" name="{{ $col['key'] }}"
                               class="form-control form-control-sm"
                               value="{{ old($col['key'], $bahan?->{$col['key']}) }}"
                               placeholder="—">
                    </div>
                    @endforeach
                </div>

                {{-- Status --}}
                <div class="form-check form-switch mb-4">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1"
                           {{ old('is_active', $bahan?->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold small" for="isActive">Bahan pangan aktif</label>
                </div>

                {{-- Tombol --}}
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i>
                        {{ $bahan ? 'Perbarui Data' : 'Simpan Bahan Pangan' }}
                    </button>
                    <a href="{{ route('bahan-pangan.index') }}" class="btn btn-outline-secondary px-4">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
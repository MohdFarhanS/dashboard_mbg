@extends('layouts.app')
@section('title', isset($harga) ? 'Edit Harga Bahan' : 'Tambah Harga Bahan')

@section('content')
<div class="container py-4" style="max-width:600px">
    <div class="d-flex align-items-center mb-4 gap-2">
        <a href="{{ route('biaya.harga.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left"></i>
        </a>
        <h5 class="fw-bold mb-0" style="color:#1a6b3a">
            {{ isset($harga) ? 'Edit Harga Bahan' : 'Tambah Harga Bahan' }}
        </h5>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ isset($harga) ? route('biaya.harga.update', $harga) : route('biaya.harga.store') }}"
                  method="POST">
                @csrf
                @if(isset($harga)) @method('PUT') @endif

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Bahan <span class="text-danger">*</span></label>
                    <select name="bahan_pangan_id" class="form-select @error('bahan_pangan_id') is-invalid @enderror"
                            required>
                        <option value="">-- Pilih Bahan --</option>
                        @foreach($bahans as $b)
                            <option value="{{ $b->id }}"
                                {{ old('bahan_pangan_id', $harga->bahan_pangan_id ?? '') == $b->id ? 'selected' : '' }}>
                                {{ $b->nama_bahan }}
                            </option>
                        @endforeach
                    </select>
                    @error('bahan_pangan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Harga per 100g (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="harga_per_100g" step="1" min="0"
                           value="{{ old('harga_per_100g', $harga->harga_per_100g ?? '') }}"
                           class="form-control @error('harga_per_100g') is-invalid @enderror"
                           placeholder="Contoh: 5000" required>
                    <div class="form-text">Satuan: Rupiah per 100 gram bahan mentah</div>
                    @error('harga_per_100g')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col">
                        <label class="form-label fw-semibold">Berlaku Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="berlaku_mulai"
                               value="{{ old('berlaku_mulai', isset($harga) ? $harga->berlaku_mulai->format('Y-m-d') : today()->format('Y-m-d')) }}"
                               class="form-control @error('berlaku_mulai') is-invalid @enderror" required>
                        @error('berlaku_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col">
                        <label class="form-label fw-semibold">Berlaku Sampai</label>
                        <input type="date" name="berlaku_sampai"
                               value="{{ old('berlaku_sampai', isset($harga) && $harga->berlaku_sampai ? $harga->berlaku_sampai->format('Y-m-d') : '') }}"
                               class="form-control @error('berlaku_sampai') is-invalid @enderror">
                        <div class="form-text">Kosongkan = berlaku terus</div>
                        @error('berlaku_sampai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Keterangan</label>
                    <input type="text" name="keterangan"
                           value="{{ old('keterangan', $harga->keterangan ?? '') }}"
                           class="form-control" placeholder="Contoh: Harga pasar Agustus 2025">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save me-1"></i>{{ isset($harga) ? 'Perbarui' : 'Simpan' }}
                    </button>
                    <a href="{{ route('biaya.harga.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
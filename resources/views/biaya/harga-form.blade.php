{{-- resources/views/biaya/harga-form.blade.php --}}
@extends('layouts.app')

@section('title', isset($harga) ? 'Edit Harga Bahan' : 'Tambah Harga Bahan')

@section('content')
<div class="container py-4" style="max-width:600px">

    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('biaya.harga.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left"></i>
        </a>
        <h4 class="fw-semibold mb-0">
            {{ isset($harga) ? 'Edit Harga Bahan' : 'Tambah Harga Bahan' }}
        </h4>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <form method="POST"
                  action="{{ isset($harga)
                             ? route('biaya.harga.update', $harga)
                             : route('biaya.harga.store') }}">
                @csrf
                @if(isset($harga)) @method('PUT') @endif

                {{-- FIX: Dropdown unit_sppg hanya untuk admin; pengelola memakai unit sendiri --}}
                @if(auth()->user()->role === 'admin')
                <div class="mb-3">
                    <label class="form-label fw-medium">Unit SPPG <span class="text-danger">*</span></label>
                    @php
                        $unitList = \App\Models\MenuHarian::distinct()->pluck('unit_sppg')->sort()->values();
                    @endphp
                    <select name="unit_sppg" class="form-select @error('unit_sppg') is-invalid @enderror" required>
                        <option value="">— Pilih Unit —</option>
                        @foreach($unitList as $u)
                            <option value="{{ $u }}"
                                @selected(old('unit_sppg', $harga->unit_sppg ?? '') === $u)>{{ $u }}</option>
                        @endforeach
                    </select>
                    @error('unit_sppg')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                @else
                <div class="mb-3">
                    <label class="form-label fw-medium">Unit SPPG</label>
                    <input type="text" class="form-control" value="{{ auth()->user()->unit_sppg }}" disabled>
                    <div class="form-text">Harga akan disimpan untuk unit Anda.</div>
                </div>
                @endif

                <div class="mb-3">
                    <label class="form-label fw-medium">Bahan Pangan <span class="text-danger">*</span></label>
                    <select name="bahan_pangan_id"
                            class="form-select @error('bahan_pangan_id') is-invalid @enderror"
                            required>
                        <option value="">— Pilih Bahan —</option>
                        @foreach($bahans as $b)
                            <option value="{{ $b->id }}"
                                @selected(old('bahan_pangan_id', $harga->bahan_pangan_id ?? '') == $b->id)>
                                {{ $b->nama_bahan }}
                            </option>
                        @endforeach
                    </select>
                    @error('bahan_pangan_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium">Harga per 100g (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="harga_per_100g" step="0.01" min="0"
                           value="{{ old('harga_per_100g', $harga->harga_per_100g ?? '') }}"
                           class="form-control @error('harga_per_100g') is-invalid @enderror"
                           required>
                    @error('harga_per_100g')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-medium">Berlaku Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="berlaku_mulai"
                               value="{{ old('berlaku_mulai', isset($harga) ? $harga->berlaku_mulai->format('Y-m-d') : '') }}"
                               class="form-control @error('berlaku_mulai') is-invalid @enderror"
                               required>
                        @error('berlaku_mulai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-medium">Berlaku Sampai</label>
                        <input type="date" name="berlaku_sampai"
                               value="{{ old('berlaku_sampai', isset($harga) && $harga->berlaku_sampai ? $harga->berlaku_sampai->format('Y-m-d') : '') }}"
                               class="form-control @error('berlaku_sampai') is-invalid @enderror">
                        @error('berlaku_sampai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-medium">Keterangan</label>
                    <input type="text" name="keterangan" maxlength="200"
                           value="{{ old('keterangan', $harga->keterangan ?? '') }}"
                           class="form-control @error('keterangan') is-invalid @enderror"
                           placeholder="Opsional...">
                    @error('keterangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save me-1"></i>Simpan
                    </button>
                    <a href="{{ route('biaya.harga.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection
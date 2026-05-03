@extends('layouts.app')

@section('title', isset($anggaran) ? 'Edit Anggaran' : 'Tetapkan Anggaran Baru')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">

            <div class="d-flex align-items-center mb-4 gap-3">
                <a href="{{ route('anggaran.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h4 class="fw-bold mb-0" style="color:var(--primary)">
                    {{ isset($anggaran) ? 'Edit Anggaran' : 'Tetapkan Anggaran Baru' }}
                </h4>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ isset($anggaran) ? route('anggaran.update', $anggaran) : route('anggaran.store') }}"
                          method="POST">
                        @csrf
                        @if(isset($anggaran)) @method('PUT') @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Anggaran Per Porsi (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="anggaran_per_porsi"
                                   class="form-control @error('anggaran_per_porsi') is-invalid @enderror"
                                   value="{{ old('anggaran_per_porsi', $anggaran->anggaran_per_porsi ?? 15000) }}"
                                   min="1000" step="500" required>
                            @error('anggaran_per_porsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Berlaku Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="berlaku_mulai"
                                   class="form-control @error('berlaku_mulai') is-invalid @enderror"
                                   value="{{ old('berlaku_mulai', isset($anggaran) ? $anggaran->berlaku_mulai->format('Y-m-d') : '') }}"
                                   required>
                            <div class="form-text">Anggaran berlaku mulai tanggal ini hingga ditetapkan anggaran baru.</div>
                            @error('berlaku_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Keterangan</label>
                            <input type="text" name="keterangan"
                                   class="form-control"
                                   value="{{ old('keterangan', $anggaran->keterangan ?? '') }}"
                                   placeholder="Contoh: Kenaikan anggaran per Januari 2025">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Simpan
                            </button>
                            <a href="{{ route('anggaran.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
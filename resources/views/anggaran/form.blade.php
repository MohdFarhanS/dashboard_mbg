@extends('layouts.app')

@section('title', 'Tetapkan Anggaran Baru')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">

            <div class="d-flex align-items-center mb-4 gap-3">
                <a href="{{ route('anggaran.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h4 class="fw-bold mb-0" style="color:var(--primary)">
                    Tetapkan Anggaran Baru
                </h4>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('anggaran.store') }}" method="POST">
                        @csrf

                        {{-- Kelompok 1 --}}
                        <div class="mb-1">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-child me-1 text-primary"></i>
                                Anggaran Per Porsi — Balita s/d Kelas 3 SD (Rp)
                                <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="anggaran_balita_sd3"
                                   class="form-control @error('anggaran_balita_sd3') is-invalid @enderror"
                                   value="{{ old('anggaran_balita_sd3', 15000) }}"
                                   min="1000" step="500" required>
                            @error('anggaran_balita_sd3')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">Mencakup: Balita (0–5 th), TK, Kelas 1–3 SD</small>
                        </div>

                        {{-- Kelompok 2 --}}
                        <div class="mb-1">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user-graduate me-1 text-success"></i>
                                Anggaran Per Porsi — Kelas 4 SD s/d Ibu Menyusui (Rp)
                                <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="anggaran_sd4_ibu_menyusui"
                                   class="form-control @error('anggaran_sd4_ibu_menyusui') is-invalid @enderror"
                                   value="{{ old('anggaran_sd4_ibu_menyusui', 15000) }}"
                                   min="1000" step="500" required>
                            @error('anggaran_sd4_ibu_menyusui')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">Mencakup: Kelas 4–6 SD, SMP, SMA, Ibu Hamil, Ibu Menyusui</small>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Berlaku Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="berlaku_mulai"
                                   class="form-control @error('berlaku_mulai') is-invalid @enderror"
                                   value="{{ old('berlaku_mulai') }}"
                                   required>
                            <div class="form-text">Anggaran berlaku mulai tanggal ini hingga ditetapkan anggaran baru.</div>
                            @error('berlaku_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Keterangan</label>
                            <input type="text" name="keterangan"
                                   class="form-control"
                                   value="{{ old('keterangan') }}"
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

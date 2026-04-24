@extends('layouts.app')
@section('title', isset($anggaran) ? 'Edit Anggaran' : 'Tetapkan Anggaran Baru')

@section('content')
<div class="container py-4" style="max-width:580px">

    <div class="d-flex align-items-center mb-4 gap-2">
        <a href="{{ route('anggaran.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h5 class="fw-bold mb-0" style="color:#1a6b3a">
                {{ isset($anggaran) ? 'Edit Anggaran' : 'Tetapkan Anggaran Baru' }}
            </h5>
            <small class="text-muted">
                {{ isset($anggaran) ? 'Ubah detail penetapan anggaran' : 'Anggaran lama tetap tersimpan sebagai riwayat' }}
            </small>
        </div>
    </div>

    {{-- Info penting --}}
    @if(!isset($anggaran))
    <div class="alert alert-info mb-4" style="background:#e8f4fd; border-color:#b8daff; color:#0c5460">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Catatan:</strong> Anggaran sebelumnya tetap tersimpan dan digunakan untuk menghitung
        rekap menu pada periode berlakunya. Perubahan hanya berlaku untuk tanggal menu sesuai
        rentang yang ditetapkan.
    </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            @if($errors->any())
            <div class="alert alert-danger py-2 mb-3">
                <ul class="mb-0 small">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ isset($anggaran) ? route('anggaran.update', $anggaran) : route('anggaran.store') }}"
                  method="POST">
                @csrf
                @if(isset($anggaran)) @method('PUT') @endif

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Unit SPPG <span class="text-danger">*</span>
                    </label>
                    <select name="unit_sppg"
                            class="form-select @error('unit_sppg') is-invalid @enderror"
                            required>
                        <option value="">— Pilih Unit —</option>
                        @foreach($unitList as $unit)
                        <option value="{{ $unit }}"
                            {{ old('unit_sppg', $anggaran->unit_sppg ?? '') === $unit ? 'selected' : '' }}>
                            {{ $unit }}
                        </option>
                        @endforeach
                    </select>
                    @error('unit_sppg')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Anggaran Per Porsi (Rp) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="anggaran_per_porsi"
                               step="500" min="1000" max="999999"
                               value="{{ old('anggaran_per_porsi', $anggaran->anggaran_per_porsi ?? 15000) }}"
                               class="form-control form-control-lg @error('anggaran_per_porsi') is-invalid @enderror"
                               required>
                    </div>
                    <div class="form-text">Standar MBG: Rp 15.000 per porsi</div>
                    @error('anggaran_per_porsi')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col">
                        <label class="form-label fw-semibold">
                            Berlaku Mulai <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="berlaku_mulai"
                               value="{{ old('berlaku_mulai', isset($anggaran) ? $anggaran->berlaku_mulai->format('Y-m-d') : today()->format('Y-m-d')) }}"
                               class="form-control @error('berlaku_mulai') is-invalid @enderror"
                               required>
                        @error('berlaku_mulai')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col">
                        <label class="form-label fw-semibold">Berlaku Sampai</label>
                        <input type="date" name="berlaku_sampai"
                               value="{{ old('berlaku_sampai', isset($anggaran) && $anggaran->berlaku_sampai ? $anggaran->berlaku_sampai->format('Y-m-d') : '') }}"
                               class="form-control @error('berlaku_sampai') is-invalid @enderror">
                        <div class="form-text">Kosongkan = berlaku tanpa batas</div>
                        @error('berlaku_sampai')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Keterangan</label>
                    <input type="text" name="keterangan"
                           value="{{ old('keterangan', $anggaran->keterangan ?? '') }}"
                           class="form-control"
                           placeholder="Contoh: Sesuai SK Dinas No. 123/2026">
                </div>

                {{-- Preview anggaran aktif saat ini --}}
                @if(!isset($anggaran))
                <div class="bg-light rounded p-3 mb-4">
                    <div class="small text-muted fw-semibold mb-1">
                        <i class="fas fa-info-circle me-1"></i>Anggaran Aktif Saat Ini
                    </div>
                    <div id="previewAktif" class="text-muted small">
                        Pilih unit untuk melihat anggaran aktif
                    </div>
                </div>
                @endif

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-save me-1"></i>
                        {{ isset($anggaran) ? 'Simpan Perubahan' : 'Tetapkan Anggaran' }}
                    </button>
                    <a href="{{ route('anggaran.index') }}" class="btn btn-outline-secondary px-4">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Preview anggaran aktif per unit saat ini
const anggaranAktif = @json(
    $unitList->mapWithKeys(fn($u) => [$u => \App\Models\AnggaranPorsi::aktif($u)])
);

const selectUnit    = document.querySelector('[name=unit_sppg]');
const previewAktif  = document.getElementById('previewAktif');

if (selectUnit && previewAktif) {
    selectUnit.addEventListener('change', function () {
        const unit = this.value;
        if (!unit || anggaranAktif[unit] === undefined) {
            previewAktif.textContent = 'Pilih unit untuk melihat anggaran aktif';
            return;
        }
        const nominal = anggaranAktif[unit].toLocaleString('id-ID');
        previewAktif.innerHTML =
            `<span class="fw-semibold" style="color:#1a6b3a">Rp ${nominal}</span> / porsi`;
    });
}
</script>
@endpush
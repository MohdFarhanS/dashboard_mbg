@extends('layouts.app')
@section('title', 'Set Anggaran Menu')

@section('content')
<div class="container py-4" style="max-width:560px">

    <div class="d-flex align-items-center mb-4 gap-2">
        <a href="{{ route('biaya.dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left"></i>
        </a>
        <div>
            <h5 class="fw-bold mb-0" style="color:#1a6b3a">Set Anggaran per Porsi</h5>
            <small class="text-muted">{{ $menu->nama_menu }} — {{ $menu->tanggal->format('d/m/Y') }}</small>
        </div>
    </div>

    {{-- Info cost aktual --}}
    @php $biaya = $menu->totalBiaya(); @endphp
    <div class="alert alert-light border mb-4">
        <div class="row text-center g-0">
            <div class="col-6 border-end">
                <div class="text-muted small">Cost Aktual/Porsi</div>
                <div class="fw-bold fs-5" style="color:#1a6b3a">
                    Rp {{ number_format($biaya['cost_per_porsi'], 0, ',', '.') }}
                </div>
                <div class="text-muted" style="font-size:11px">dari {{ $biaya['jumlah_porsi'] }} porsi</div>
            </div>
            <div class="col-6">
                <div class="text-muted small">Anggaran Saat Ini</div>
                <div class="fw-bold fs-5">
                    Rp {{ number_format($menu->anggaran_per_porsi, 0, ',', '.') }}
                </div>
                <div class="text-muted" style="font-size:11px">per porsi</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('biaya.update-anggaran', $menu) }}" method="POST">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Anggaran per Porsi (Rp) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="anggaran_per_porsi" min="1000" max="999999" step="500"
                               value="{{ old('anggaran_per_porsi', $menu->anggaran_per_porsi) }}"
                               class="form-control form-control-lg @error('anggaran_per_porsi') is-invalid @enderror"
                               required>
                    </div>
                    <div class="form-text">Default: Rp 15.000 per porsi (standar MBG)</div>
                    @error('anggaran_per_porsi')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Jumlah Porsi <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="jumlah_porsi" min="1" max="9999"
                           value="{{ old('jumlah_porsi', $menu->jumlah_porsi) }}"
                           class="form-control @error('jumlah_porsi') is-invalid @enderror"
                           required>
                    <div class="form-text">Jumlah siswa/penerima manfaat yang dilayani</div>
                    @error('jumlah_porsi')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Catatan</label>
                    <input type="text" name="catatan_anggaran"
                           value="{{ old('catatan_anggaran', $menu->catatan_anggaran) }}"
                           class="form-control"
                           placeholder="Contoh: Anggaran April 2026 sesuai SK Dinas">
                </div>

                {{-- Preview kalkulasi --}}
                <div class="bg-light rounded p-3 mb-4" id="previewAnggaran">
                    <div class="small text-muted mb-2 fw-semibold">Preview Total Anggaran</div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Total anggaran</span>
                        <span class="fw-bold" id="totalAnggaran">
                            Rp {{ number_format($menu->anggaran_per_porsi * $menu->jumlah_porsi, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span class="text-muted small">Total cost aktual</span>
                        <span>Rp {{ number_format($biaya['total_seluruh'], 0, ',', '.') }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small fw-semibold">Sisa anggaran</span>
                        <span class="fw-bold" id="sisaAnggaran" style="color:#1a6b3a">—</span>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save me-1"></i>Simpan Anggaran
                    </button>
                    <a href="{{ route('biaya.dashboard') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const totalAktual = {{ $biaya['total_seluruh'] }};

function updatePreview() {
    const anggaran = parseFloat(document.querySelector('[name=anggaran_per_porsi]').value) || 0;
    const porsi    = parseInt(document.querySelector('[name=jumlah_porsi]').value) || 1;
    const total    = anggaran * porsi;
    const sisa     = total - totalAktual;

    document.getElementById('totalAnggaran').textContent =
        'Rp ' + total.toLocaleString('id-ID');

    const sisaEl = document.getElementById('sisaAnggaran');
    sisaEl.textContent = (sisa >= 0 ? '+' : '') + 'Rp ' + Math.abs(sisa).toLocaleString('id-ID');
    sisaEl.style.color = sisa >= 0 ? '#1a6b3a' : '#dc3545';
}

document.querySelector('[name=anggaran_per_porsi]').addEventListener('input', updatePreview);
document.querySelector('[name=jumlah_porsi]').addEventListener('input', updatePreview);
updatePreview();
</script>
@endpush
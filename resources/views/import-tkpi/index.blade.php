@extends('layouts.app')
@section('title', 'Import TKPI')

@push('styles')
<style>
    .upload-zone {
        border: 2px dashed #aed6b8;
        border-radius: 14px;
        padding: 2.5rem;
        text-align: center;
        background: #f8fdf9;
        transition: all .2s;
        cursor: pointer;
    }
    .upload-zone:hover, .upload-zone.dragover {
        border-color: var(--primary);
        background: #eaf5ee;
    }
    .upload-zone input[type=file] { display: none; }
    .upload-zone .icon { font-size: 2.5rem; margin-bottom: .5rem; }

    .mapped-tag {
        display: inline-block;
        background: #d1e7dd;
        color: #0a3622;
        border-radius: 5px;
        padding: 1px 8px;
        font-size: .78rem;
        font-weight: 600;
        margin: 1px;
    }
    .unmapped-tag {
        display: inline-block;
        background: #f8d7da;
        color: #842029;
        border-radius: 5px;
        padding: 1px 8px;
        font-size: .78rem;
        margin: 1px;
    }
    .step-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px; height: 26px;
        border-radius: 50%;
        background: var(--primary);
        color: #fff;
        font-size: .75rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    table.preview-table th { background: #1a6b3a; color: #fff; font-size: .8rem; }
    table.preview-table td { font-size: .82rem; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:var(--primary)">
                <i class="fas fa-file-import me-2"></i>Import Data TKPI
            </h4>
            <small class="text-muted">Upload CSV dari Tabel Komposisi Pangan Indonesia</small>
        </div>
        <span class="badge bg-success fs-6">
            {{ number_format($totalBahan) }} bahan tersimpan
        </span>
    </div>

    {{-- Alert sukses --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        @if(session('import_errors'))
        <ul class="mb-0 mt-1 small">
            @foreach(session('import_errors') as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm">
        <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
    </div>
    @endif

    <div class="row g-4">

        {{-- Kolom Kiri: Upload & Preview --}}
        <div class="col-lg-8">

            {{-- Step 1: Upload --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="step-pill">1</span>
                        <span class="fw-semibold">Upload File CSV</span>
                    </div>

                    <form method="POST" action="{{ route('import-tkpi.preview') }}"
                          enctype="multipart/form-data" id="formUpload">
                        @csrf
                        <div class="upload-zone" id="uploadZone">
                            <div class="icon">📂</div>
                            <p class="fw-semibold mb-1" id="uploadLabel">Klik atau drag & drop file CSV di sini</p>
                            <p class="text-muted small mb-2">Format: .csv | Maks 5MB | Delimiter koma atau titik koma</p>
                            <input type="file" id="csvInput" name="csv_file" accept=".csv,.txt"
                                   onchange="handleFileSelect(this)">
                            <button type="submit" id="btnPreview"
                                   onclick="event.stopPropagation()"  {{-- tambahkan ini --}}
                                   class="btn btn-success btn-sm mt-2 d-none" style="pointer-events:auto">
                               <i class="fas fa-eye me-1"></i>Preview Data
                           </button>
                        </div>
                    </form>

                    {{-- Info format kolom --}}
                    <div class="mt-3 p-3 rounded" style="background:#f0faf4; font-size:.82rem">
                        <strong>Kolom yang dikenali otomatis:</strong><br>
                        <span class="mapped-tag">nama_bahan</span>
                        <span class="mapped-tag">energi</span>
                        <span class="mapped-tag">protein</span>
                        <span class="mapped-tag">lemak</span>
                        <span class="mapped-tag">karbohidrat</span>
                        <span class="mapped-tag">serat</span>
                        <span class="mapped-tag">kalsium</span>
                        <span class="mapped-tag">besi / fe</span>
                        <span class="mapped-tag">vit_c</span>
                        <span class="mapped-tag">air</span>
                        <span class="mapped-tag">harga_per_kg</span>
                    </div>
                </div>
            </div>

            {{-- Step 2: Preview --}}
            @isset($preview)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="step-pill">2</span>
                        <span class="fw-semibold">Preview Data (10 baris pertama dari {{ $totalRows }} total)</span>
                    </div>

                    {{-- Status mapping kolom --}}
                    <div class="mb-3 p-3 rounded" style="background:#f8f9fa; font-size:.82rem">
                        <strong>Status Mapping Kolom:</strong><br>
                        @foreach(\App\Http\Controllers\ImportTkpiController::KOLOM_MAP as $dbCol => $aliases)
                            @if($dbCol === 'kode') @continue @endif  {{-- skip kode, digenerate otomatis --}}
                            @if(isset($mapped[$dbCol]))
                                <span class="mapped-tag">✓ {{ $dbCol }}</span>
                            @else
                                <span class="unmapped-tag">✗ {{ $dbCol }}</span>
                            @endif
                        @endforeach
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered preview-table mb-0">
                            <thead>
                                <tr>
                                    @foreach($headers as $h)
                                    <th>{{ $h }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($preview as $row)
                                <tr>
                                    @foreach($row as $cell)
                                    <td>{{ $cell }}</td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Step 3: Konfirmasi Import --}}
            <div class="card border-0 shadow-sm border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="step-pill">3</span>
                        <span class="fw-semibold">Konfirmasi Import</span>
                    </div>

                    <form method="POST" action="{{ route('import-tkpi.import') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Mode Duplikat</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode"
                                           value="skip" id="modeSkip" checked>
                                    <label class="form-check-label small" for="modeSkip">
                                        <strong>Skip</strong> — lewati jika nama bahan sudah ada
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode"
                                           value="update" id="modeUpdate">
                                    <label class="form-check-label small" for="modeUpdate">
                                        <strong>Update</strong> — perbarui data jika sudah ada
                                    </label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success"
                                onclick="return confirm('Import {{ $totalRows }} baris data ke database?')">
                            <i class="fas fa-database me-2"></i>Import {{ $totalRows }} Baris
                        </button>
                    </form>
                </div>
            </div>
            @endisset

        </div>

        {{-- Kolom Kanan: Panduan & Riwayat --}}
        <div class="col-lg-4">

            {{-- Panduan --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header border-0" style="background:var(--primary-pale)">
                    <span class="fw-semibold small" style="color:var(--primary)">
                        <i class="fas fa-book me-2"></i>Panduan Format CSV
                    </span>
                </div>
                <div class="card-body" style="font-size:.83rem">
                    <p class="mb-2">Baris pertama harus berisi <strong>header kolom</strong>. Contoh:</p>
                    <div class="p-2 rounded" style="background:#f0f0f0; font-family:monospace; font-size:.78rem; overflow-x:auto">
                        nama_bahan,energi,protein,lemak,karbohidrat<br>
                        Nasi Putih,175,3.2,0.3,39.8<br>
                        Ayam Goreng,320,18.5,22.1,0
                    </div>
                    <hr class="my-2">
                    <ul class="mb-0 ps-3">
                        <li>Delimiter: <code>,</code> atau <code>;</code> (otomatis terdeteksi)</li>
                        <li>Desimal: titik <code>.</code> atau koma <code>,</code></li>
                        <li>Kolom tidak harus urut</li>
                        <li>Kolom tidak dikenal akan diabaikan</li>
                        <li>Kolom wajib: <strong>nama_bahan</strong></li>
                    </ul>
                </div>
            </div>

            {{-- Riwayat --}}
            @if(!empty($riwayat))
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0" style="background:var(--primary-pale)">
                    <span class="fw-semibold small" style="color:var(--primary)">
                        <i class="fas fa-history me-2"></i>Riwayat Import (Sesi Ini)
                    </span>
                </div>
                <div class="card-body p-0">
                    @foreach($riwayat as $r)
                    <div class="px-3 py-2 border-bottom" style="font-size:.82rem">
                        <div class="text-muted">{{ $r['waktu'] }} · mode: {{ $r['mode'] }}</div>
                        <span class="text-success">+{{ $r['inserted'] }} baru</span> ·
                        <span class="text-primary">↺ {{ $r['updated'] }} update</span> ·
                        <span class="text-secondary">{{ $r['skipped'] }} skip</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function handleFileSelect(input) {
    if (input.files.length > 0) {
        document.getElementById('uploadLabel').textContent = input.files[0].name;
        document.getElementById('btnPreview').classList.remove('d-none');
    }
}

const zone = document.getElementById('uploadZone');
zone.addEventListener('click', function(e) {
    if (e.target.id !== 'btnPreview' && !e.target.closest('#btnPreview')) {
        document.getElementById('csvInput').click();
    }
});
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('dragover');
    const input = document.getElementById('csvInput');
    input.files = e.dataTransfer.files;
    handleFileSelect(input);
});
</script>
@endpush
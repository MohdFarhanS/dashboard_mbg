@extends('layouts.app')

@section('title', 'Pesan Masuk — Dashboard MBG')
@section('page-title', 'Pesan Masuk')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1" style="color:#0d2545;">Pesan Masuk</h4>
        <p class="text-muted mb-0" style="font-size:.85rem;">Pesan dari masyarakat umum melalui halaman publik.</p>
    </div>
    <div>
        <span class="badge rounded-pill px-3 py-2"
              style="background:var(--primary-pale); color:var(--primary); font-size:.8rem; font-weight:600;">
            <i class="fas fa-envelope me-1"></i>
            {{ $pesanList->total() }} total pesan
        </span>
    </div>
</div>

@if($pesanList->isEmpty())
    <div class="card card-mbg">
        <div class="card-body text-center py-5">
            <i class="fas fa-inbox fa-3x mb-3" style="color:#dde8f0;"></i>
            <p class="text-muted mb-0">Belum ada pesan masuk.</p>
        </div>
    </div>
@else
    <div class="card card-mbg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0" style="font-size:.85rem;">
                    <thead>
                        <tr style="background:#f8fafd; border-bottom:2px solid #e8f0f8;">
                            <th class="px-4 py-3" style="color:#6b8ba4; font-weight:600; width:40px;">#</th>
                            <th class="px-3 py-3" style="color:#6b8ba4; font-weight:600;">Pengirim</th>
                            <th class="px-3 py-3" style="color:#6b8ba4; font-weight:600;">No. HP</th>
                            <th class="px-3 py-3" style="color:#6b8ba4; font-weight:600;">Pesan</th>
                            <th class="px-3 py-3" style="color:#6b8ba4; font-weight:600; white-space:nowrap;">Tanggal Masuk</th>
                            <th class="px-3 py-3" style="color:#6b8ba4; font-weight:600;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pesanList as $i => $pesan)
                        <tr style="border-bottom:1px solid #f0f6fb;
                                   {{ !$pesan->is_read ? 'background:#fdfeff;' : '' }}">
                            <td class="px-4 py-3" style="color:#adb5bd;">
                                {{ $pesanList->firstItem() + $i }}
                            </td>
                            <td class="px-3 py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar" style="width:32px; height:32px; font-size:.75rem; flex-shrink:0;">
                                        {{ strtoupper(substr($pesan->nama, 0, 1)) }}
                                    </div>
                                    <span class="fw-{{ !$pesan->is_read ? '600' : '500' }}"
                                          style="color:#0d2545;">
                                        {{ $pesan->nama }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $pesan->no_hp) }}"
                                   target="_blank"
                                   style="color:var(--primary-light); text-decoration:none; font-weight:500;">
                                    <i class="fab fa-whatsapp me-1" style="color:#25D366;"></i>
                                    {{ $pesan->no_hp }}
                                </a>
                            </td>
                            <td class="px-3 py-3" style="max-width:340px;">
                                <div style="white-space:pre-wrap; word-break:break-word; color:#3d5a80; line-height:1.6;">
                                    {{ Str::limit($pesan->pesan, 160) }}
                                </div>
                                @if(strlen($pesan->pesan) > 160)
                                <button class="btn btn-sm p-0 mt-1"
                                        style="font-size:.75rem; color:var(--primary-light); font-weight:600;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalPesan{{ $pesan->id }}">
                                    Baca selengkapnya →
                                </button>
                                @endif
                            </td>
                            <td class="px-3 py-3" style="color:#6b8ba4; white-space:nowrap;">
                                <i class="fas fa-calendar-alt me-1"></i>
                                {{ $pesan->created_at->format('d M Y') }}<br>
                                <small>{{ $pesan->created_at->format('H:i') }} WIB</small>
                            </td>
                            <td class="px-3 py-3">
                                @if(!$pesan->is_read)
                                <span class="badge rounded-pill" style="background:#daeeff; color:#0f4c81; font-size:.72rem;">
                                    <i class="fas fa-circle me-1" style="font-size:.4rem;"></i> Baru
                                </span>
                                @else
                                <span class="badge rounded-pill" style="background:#f0f4f9; color:#adb5bd; font-size:.72rem;">
                                    <i class="fas fa-check me-1"></i> Dibaca
                                </span>
                                @endif
                            </td>
                        </tr>

                        {{-- Modal baca pesan penuh --}}
                        @if(strlen($pesan->pesan) > 160)
                        <div class="modal fade" id="modalPesan{{ $pesan->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content" style="border:none; border-radius:16px; overflow:hidden;">
                                    <div class="modal-header" style="background:var(--primary-pale); border-bottom:1px solid #dde8f0;">
                                        <div>
                                            <h6 class="modal-title fw-bold mb-0" style="color:var(--primary);">
                                                <i class="fas fa-envelope me-2"></i> {{ $pesan->nama }}
                                            </h6>
                                            <small class="text-muted">{{ $pesan->no_hp }} &mdash; {{ $pesan->created_at->format('d M Y, H:i') }} WIB</small>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p style="white-space:pre-wrap; line-height:1.8; color:#3d5a80; margin:0;">{{ $pesan->pesan }}</p>
                                    </div>
                                    <div class="modal-footer" style="border-top:1px solid #f0f6fb;">
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $pesan->no_hp) }}"
                                           target="_blank"
                                           class="btn btn-sm"
                                           style="background:#25D366; color:#fff; border-radius:8px; font-weight:600; font-size:.82rem;">
                                            <i class="fab fa-whatsapp me-1"></i> Balas via WhatsApp
                                        </a>
                                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    @if($pesanList->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $pesanList->links() }}
    </div>
    @endif
@endif
@endsection

@extends('layouts.app')
@section('title', 'Akses Ditolak')

@section('content')
<div class="container py-5 text-center">
    <div class="mb-4">
        <i class="fa fa-lock fa-4x" style="color:#1a6b3a; opacity:.4"></i>
    </div>
    <h3 class="fw-bold text-danger">403 — Akses Ditolak</h3>
    <p class="text-muted mb-4">
        Anda tidak memiliki izin untuk mengakses halaman ini.<br>
        Silakan hubungi administrator jika merasa ini adalah kesalahan.
    </p>
    <a href="{{ url()->previous() }}" class="btn btn-outline-success me-2">
        <i class="fa fa-arrow-left me-1"></i>Kembali
    </a>
    <a href="{{ route('dashboard') }}" class="btn btn-success">
        <i class="fa fa-home me-1"></i>Dashboard
    </a>
</div>
@endsection
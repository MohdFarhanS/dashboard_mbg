@extends('layouts.app')
@section('title', isset($user) ? 'Edit User' : 'Tambah User')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">

            <div class="d-flex align-items-center mb-4 gap-3">
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h4 class="fw-bold mb-0" style="color:var(--primary)">
                    {{ isset($user) ? 'Edit User' : 'Tambah User Baru' }}
                </h4>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ isset($user) ? route('users.update', $user) : route('users.store') }}"
                          method="POST">
                        @csrf
                        @if(isset($user)) @method('PUT') @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name ?? '') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email ?? '') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="pengelola" {{ old('role', $user->role ?? '') === 'pengelola' ? 'selected' : '' }}>
                                    Pengelola (input & kelola menu)
                                </option>
                                <option value="admin" {{ old('role', $user->role ?? '') === 'admin' ? 'selected' : '' }}>
                                    Admin (akses penuh)
                                </option>
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @if(!isset($user))
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Min. 8 karakter" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @endif

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ isset($user) ? 'Simpan Perubahan' : 'Buat User' }}
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
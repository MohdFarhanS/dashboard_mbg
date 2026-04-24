<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Dashboard MBG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a6b3a 0%, #2d9e5f 50%, #4caf50 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            border: none;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #1a6b3a, #2d9e5f);
            padding: 2rem;
            text-align: center;
            color: white;
        }
        .login-header .logo-icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        .btn-login {
            background: linear-gradient(135deg, #1a6b3a, #2d9e5f);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.75rem;
            border-radius: 8px;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-login:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            color: white;
        }
        .form-control:focus {
            border-color: #2d9e5f;
            box-shadow: 0 0 0 0.2rem rgba(45,158,95,.25);
        }
        .input-group-text {
            background: #f0faf3;
            border-color: #dee2e6;
            color: #2d9e5f;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card login-card">
                <div class="login-header">
                    <div class="logo-icon">🍱</div>
                    <h4 class="fw-bold mb-1">Dashboard MBG</h4>
                    <small class="opacity-75">Monitoring Gizi & Biaya Produksi</small>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control"
                                       value="{{ old('email') }}" placeholder="email@sppg.id" required autofocus>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" class="form-control"
                                       placeholder="••••••••" required>
                            </div>
                        </div>
                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label small" for="remember">Ingat saya</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i> Masuk
                        </button>
                    </form>

                    <hr class="my-3">
                    <p class="text-center text-muted small mb-0">
                        Satuan Pelayanan Pemenuhan Gizi (SPPG) <br>
                        Program Makan Bergizi Gratis
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
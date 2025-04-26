<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AkilliAjanda') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
            min-height: 100vh;
        }
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 1rem;
        }
        .auth-card {
            background: #fff;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
        }
        .auth-sidebar {
            background: linear-gradient(135deg, #4158d0 0%, #3b5998 100%);
            color: white;
            padding: 3rem 2rem;
        }
        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 0.75rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
        }
        .form-control:focus {
            border-color: #4158d0;
            box-shadow: 0 0 0 0.2rem rgba(65, 88, 208, 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #4158d0 0%, #3b5998 100%);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(65, 88, 208, 0.2);
        }
        .btn-light {
            background: #fff;
            border: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-light:hover {
            background: #f8fafc;
            transform: translateY(-2px);
        }
        .form-check-input:checked {
            background-color: #4158d0;
            border-color: #4158d0;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="container">
            <div class="auth-card mx-auto" style="max-width: 1000px;">
                <div class="row g-0">
                    <!-- Sol Panel -->
                    <div class="col-lg-5 auth-sidebar d-none d-lg-block">
                        <div class="h-100 d-flex flex-column">
                            <div class="mb-5">
                                <i class="bi bi-calendar-check fs-1 mb-4"></i>
                                <h4 class="fw-bold mb-3">Akıllı Ajanda</h4>
                                <p class="text-white-50">Tüm etkinliklerinizi, görevlerinizi ve notlarınızı tek bir yerde organize edin.</p>
                            </div>

                            <div class="row g-4 mt-auto">
                                <div class="col-12">
                                    <div class="feature-card p-3">
                                        <i class="bi bi-calendar-event mb-2 fs-4"></i>
                                        <h6 class="mb-0">Etkinlikler</h6>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="feature-card p-3">
                                        <i class="bi bi-check2-square mb-2 fs-4"></i>
                                        <h6 class="mb-0">Görevler</h6>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="feature-card p-3">
                                        <i class="bi bi-journal-text mb-2 fs-4"></i>
                                        <h6 class="mb-0">Notlar</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Alanı -->
                    <div class="col-lg-7">
                        <div class="p-4 p-lg-5">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
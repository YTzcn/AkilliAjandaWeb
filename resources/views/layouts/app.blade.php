<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta id="quick-search-endpoint" name="quick-search-endpoint" content="{{ route('search.quick') }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    <meta name="pusher-key" content="{{ config('broadcasting.connections.pusher.key') }}">
    <meta name="pusher-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster') }}">
    @endauth

    <title>{{ config('app.name', 'AkilliAjanda') }}</title>

    <!-- Pusher CDN -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <!-- Sweetalert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Firebase CDN -->
    <script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging-compat.js"></script>
    
    <!-- Firebase initialization script -->
    <script src="{{ asset('js/firebase.js') }}"></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">

    @yield('styles')

    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            background: linear-gradient(135deg, #4158d0 0%, #3b5998 100%);
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.8rem 1rem;
            margin: 0.2rem 0;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        .main-content {
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #4158d0 0%, #3b5998 100%);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 0.5rem 1rem rgba(65, 88, 208, 0.2);
        }
        [x-cloak] { display: none !important; }
    </style>
    @auth
    <script>
        window.createLayoutQuickSearch = function (searchUrl) {
            return {
                q: '',
                open: false,
                loading: false,
                tasks: [],
                events: [],
                _timer: null,
                url: searchUrl,
                get hasAny() {
                    return this.tasks.length > 0 || this.events.length > 0;
                },
                queueSearch() {
                    this.open = true;
                    clearTimeout(this._timer);
                    this._timer = setTimeout(() => this.fetchResults(), 280);
                },
                async fetchResults() {
                    const term = this.q.trim();
                    if (term.length < 1) {
                        this.tasks = [];
                        this.events = [];
                        this.loading = false;
                        return;
                    }
                    this.loading = true;
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const res = await fetch(this.url + '?q=' + encodeURIComponent(term), {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': token,
                            },
                            credentials: 'same-origin',
                        });
                        if (!res.ok) {
                            this.tasks = [];
                            this.events = [];
                            return;
                        }
                        const data = await res.json();
                        this.tasks = data.tasks || [];
                        this.events = data.events || [];
                    } catch (e) {
                        this.tasks = [];
                        this.events = [];
                    } finally {
                        this.loading = false;
                    }
                },
            };
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    @endauth
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 position-fixed sidebar">
                <div class="d-flex flex-column p-3">
                    <a href="{{ route('dashboard') }}" class="d-flex align-items-center mb-4 text-white text-decoration-none">
                        <i class="bi bi-calendar-check fs-4 me-2"></i>
                        <span class="fs-4">AkilliAjanda</span>
                    </a>
                    <hr>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Gösterge Paneli
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('events.index') }}" class="nav-link {{ request()->routeIs('events.*') ? 'active' : '' }}">
                                <i class="bi bi-calendar-event me-2"></i>
                                Etkinlikler
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('tasks.index') }}" class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                                <i class="bi bi-check2-square me-2"></i>
                                Görevler
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                                <i class="bi bi-tags me-2"></i>
                                Kategoriler
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark-bar-graph me-2"></i>
                                Raporlar
                            </a>
                        </li>
                    </ul>
                    <hr>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-4 me-2"></i>
                            <strong>{{ Auth::user()->name }}</strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Çıkış Yap</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4 main-content">
                @auth
                <div
                    class="mb-3 pb-2 border-bottom"
                    x-data="createLayoutQuickSearch((document.getElementById('quick-search-endpoint') && document.getElementById('quick-search-endpoint').getAttribute('content')) || '')"
                    @click.outside="open = false"
                >
                    <label for="layout-global-search" class="form-label small text-muted mb-1">Görev ve etkinlik ara</label>
                    <div class="position-relative" style="max-width: 32rem;">
                        <input
                            id="layout-global-search"
                            type="search"
                            class="form-control form-control-sm"
                            placeholder="Örnek: toplantı, rapor…"
                            autocomplete="off"
                            x-model="q"
                            @input="queueSearch()"
                            @focus="open = true"
                            @keydown.escape.window="open = false"
                        >
                        <div
                            class="position-absolute w-100 mt-1 bg-white border rounded shadow-sm py-2 px-0 small"
                            style="z-index: 1050; max-height: 22rem; overflow-y: auto;"
                            x-show="open && q.trim().length >= 1"
                            x-cloak
                        >
                            <div x-show="loading" class="px-3 py-2 text-muted">Aranıyor…</div>
                            <div x-show="!loading && !hasAny" class="px-3 py-2 text-muted">Sonuç yok</div>
                            <div x-show="!loading && tasks.length > 0">
                                <div class="px-3 text-uppercase text-muted fw-semibold" style="font-size: 0.65rem;">Görevler</div>
                                <template x-for="row in tasks" :key="'t'+row.id">
                                    <a :href="row.url" class="d-block px-3 py-1 text-decoration-none text-dark" @click="open=false">
                                        <span x-text="row.title"></span>
                                        <span class="text-muted" x-show="row.subtitle" x-text="' · '+row.subtitle"></span>
                                    </a>
                                </template>
                            </div>
                            <div x-show="!loading && events.length > 0" class="mt-2">
                                <div class="px-3 text-uppercase text-muted fw-semibold" style="font-size: 0.65rem;">Etkinlikler</div>
                                <template x-for="row in events" :key="'e'+row.id">
                                    <a :href="row.url" class="d-block px-3 py-1 text-decoration-none text-dark" @click="open=false">
                                        <span x-text="row.title"></span>
                                        <span class="text-muted" x-show="row.subtitle" x-text="' · '+row.subtitle"></span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                @endauth
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')
</body>
</html> 
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Farmacia Vitalidad' }}</title>
    
    <!-- Bootstrap 5 CSS (Local) -->
    <link href="{{ asset('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    
    <!-- Estilos obligatorios de Livewire -->
    @livewireStyles
    
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: 700; letter-spacing: -0.5px; }
        .nav-link.active { color: #fff !important; background-color: rgba(255,255,255,0.1); border-radius: 5px; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    
    @auth
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
            <div class="container-fluid">
                <a class="navbar-brand d-flex align-items-center" href="{{ route('pos') }}">
                    <span class="bg-white text-primary rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-weight: bold;">+</span>
                    Vitalidad POS
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        @if(auth()->user()->rol === 'administrador')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('inventario') ? 'active' : '' }}" href="{{ route('inventario') }}">Inventario</a>
                            </li>
                            <li class="nav-item">
                              <a class="nav-link {{ request()->routeIs('clientes') ? 'active' : '' }}" href="{{ route('clientes') }}">Clientes</a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('pos') ? 'active' : '' }}" href="{{ route('pos') }}">Caja Registradora</a>
                        </li>
                    </ul>
                    
                    <div class="d-flex align-items-center text-white">
                        <span class="me-3 small">
                            <strong>{{ auth()->user()->nombre_usuario }}</strong> 
                            <span class="badge bg-light text-primary ms-1">{{ strtoupper(auth()->user()->rol) }}</span>
                        </span>
                        
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-light">Cerrar Sesión</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
    @endauth

    <main class="flex-grow-1 container">
        {{ $slot }}
    </main>

    <!-- Bootstrap 5 JS -->
    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    
    <!-- Scripts obligatorios de Livewire -->
    @livewireScripts
</body>
</html>
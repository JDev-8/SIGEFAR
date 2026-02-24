<div>
    <div class="container-fluid py-4">
        <div class="row g-4">
            
            <!-- SECCIÓN IZQUIERDA: Búsqueda y Catálogo de Productos -->
            <div class="col-md-7 col-lg-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h4 class="fw-bold text-dark mb-3">Buscador de Medicamentos</h4>
                        <div class="input-group input-group-lg mb-3">
                            <span class="input-group-text bg-light border-end-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-muted" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                            </span>
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="busqueda" 
                                class="form-control border-start-0 ps-0 shadow-none" 
                                placeholder="Escanea código o escribe nombre del producto..." 
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="card-body bg-light rounded-bottom overflow-auto" style="max-height: 70vh;">
                        <div class="row g-3">
                            @forelse($productosBusqueda as $producto)
                                <div class="col-sm-6 col-md-4 col-xl-3">
                                    <div 
                                        class="card h-100 border-0 shadow-sm text-center card-hover" 
                                        style="cursor: pointer; transition: all 0.2s;" 
                                        wire:click="agregarAlCarrito({{ $producto->id }})"
                                    >
                                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="text-muted small mb-1">{{ $producto->codigo }}</div>
                                                <h6 class="fw-bold text-dark mb-2 text-truncate" title="{{ $producto->nombre }}">
                                                    {{ $producto->nombre }}
                                                </h6>
                                            </div>
                                            <div>
                                                <div class="text-primary fw-bold fs-5">${{ number_format($producto->precio, 2) }}</div>
                                                @if($producto->requiere_receta)
                                                    <div class="badge bg-danger-subtle text-danger mt-2 w-100">⚠️ Requiere Récipe</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5">
                                    <div class="text-muted">
                                        <p class="fs-5">No se encontraron productos coincidentes.</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN DERECHA: Resumen de Venta y Cobro -->
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-sm border-0 h-100 d-flex flex-column">
                    <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Ticket de Venta</h5>
                        <span class="badge bg-white text-primary">{{ count($carrito) }} Items</span>
                    </div>

                    <div class="card-body p-0 flex-grow-1 overflow-auto" style="max-height: 45vh;">
                        @if(empty($carrito))
                            <div class="text-center py-5 px-3">
                                <p class="text-muted mb-0">Escanea productos para comenzar la venta.</p>
                            </div>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach($carrito as $index => $item)
                                    <li class="list-group-item py-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="me-2 overflow-hidden">
                                                <h6 class="mb-0 fw-bold text-truncate">{{ $item['nombre'] }}</h6>
                                                <small class="text-muted">${{ number_format($item['precio'], 2) }} c/u</small>
                                            </div>
                                            <div class="text-end fw-bold text-nowrap">
                                                ${{ number_format($item['subtotal'], 2) }}
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-secondary border-end-0" wire:click="decrementarCantidad({{ $index }})">-</button>
                                                <button type="button" class="btn btn-light disabled text-dark fw-bold px-3 border-start-0 border-end-0">{{ $item['cantidad'] }}</button>
                                                <button type="button" class="btn btn-outline-secondary border-start-0" wire:click="incrementarCantidad({{ $index }})">+</button>
                                            </div>
                                            <button class="btn btn-sm btn-link text-danger p-0 text-decoration-none" wire:click="eliminarDelCarrito({{ $index }})">Quitar</button>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="card-footer bg-white p-4 border-top">
                        
                        @if (session()->has('mensaje_exito'))
                            <div class="alert alert-success py-2 mb-3 small">
                                {{ session('mensaje_exito') }}
                            </div>
                        @endif

                        @error('error_venta')
                            <div class="alert alert-danger py-2 mb-3 small">
                                {{ $message }}
                            </div>
                        @enderror

                        <!-- BÚSQUEDA DINÁMICA DE CLIENTE -->
                        <div class="mb-3 position-relative">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-muted small fw-bold mb-0">CLIENTE</label>
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#modalNuevoClientePos">
                                    + Registrar Nuevo
                                </button>
                            </div>

                            @if($cliente_id && $clienteSeleccionado)
                                <!-- Tarjeta del Cliente Seleccionado -->
                                <div class="d-flex justify-content-between align-items-center border rounded p-2 bg-light shadow-sm">
                                    <div>
                                        <div class="fw-bold text-primary">{{ $clienteSeleccionado->persona->nombre }} {{ $clienteSeleccionado->persona->apellido }}</div>
                                        <div class="small text-muted">C.I: {{ $clienteSeleccionado->persona->cedula }} | Pts: {{ $clienteSeleccionado->puntos }}</div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger border-0" wire:click="removerCliente" title="Quitar Cliente">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <!-- Input de Búsqueda -->
                                <input 
                                    type="text" 
                                    class="form-control form-control-sm shadow-none" 
                                    wire:model.live.debounce.300ms="busquedaCliente" 
                                    placeholder="Buscar Cédula o Nombre (Dejar vacío para Consumidor Final)"
                                >
                                
                                <!-- Lista Desplegable de Resultados -->
                                @if(!empty($busquedaCliente))
                                    <ul class="list-group position-absolute w-100 mt-1 shadow" style="z-index: 1050; max-height: 200px; overflow-y: auto;">
                                        @forelse($clientesFiltrados as $cliente)
                                            <li class="list-group-item list-group-item-action py-2" style="cursor: pointer;" wire:click="seleccionarCliente({{ $cliente->persona_id }})">
                                                <div class="fw-bold small text-primary">{{ $cliente->persona->cedula }}</div>
                                                <div class="small">{{ $cliente->persona->nombre }} {{ $cliente->persona->apellido }}</div>
                                            </li>
                                        @empty
                                            <li class="list-group-item text-muted small py-2 text-center bg-light">
                                                No se encontraron clientes.
                                            </li>
                                        @endforelse
                                    </ul>
                                @endif
                            @endif
                        </div>

                        <!-- Método de Pago -->
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold mb-1">MÉTODO DE PAGO</label>
                            <select class="form-select form-select-sm shadow-none" wire:model.live="metodo_pago">
                                <option value="efectivo">Efectivo</option>
                                <option value="puntoVenta">Tarjeta / Punto de Venta</option>
                                <option value="pagoMovil">Pago Móvil</option>
                                @if(!empty($cliente_id))
                                    <option value="puntos">Canje de Puntos de Fidelidad</option>
                                @endif
                            </select>
                        </div>

                        <hr class="my-3 opacity-10">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fs-5 text-muted">Total a Pagar:</span>
                            <span class="fs-2 fw-bold text-dark">${{ number_format($this->total, 2) }}</span>
                        </div>

                        <button class="btn btn-success btn-lg w-100 fw-bold py-3 shadow-sm border-0" wire:click="cobrar" wire:loading.attr="disabled" @if(empty($carrito)) disabled @endif>
                            <span wire:loading.remove wire:target="cobrar">COMPLETAR VENTA</span>
                            <span wire:loading wire:target="cobrar">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                PROCESANDO...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL PARA REGISTRAR CLIENTE EN CALIENTE -->
        <div class="modal fade" id="modalNuevoClientePos" tabindex="-1" wire:ignore.self>
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold text-dark">Registro Rápido de Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form wire:submit.prevent="guardarCliente">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Nombres</label>
                                <input type="text" class="form-control" wire:model="cliente_nombre" placeholder="Ej: María" required>
                                @error('cliente_nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Apellidos</label>
                                <input type="text" class="form-control" wire:model="cliente_apellido" placeholder="Ej: González" required>
                                @error('cliente_apellido') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-7 mb-3">
                                    <label class="form-label small fw-bold text-muted">Cédula o Documento</label>
                                    <input type="text" class="form-control" wire:model="cliente_cedula" placeholder="Ej: V-12345678" required>
                                    @error('cliente_cedula') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label class="form-label small fw-bold text-muted">Puntos Iniciales</label>
                                    <input type="number" class="form-control" wire:model="cliente_puntos" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary px-4" wire:loading.attr="disabled" wire:target="guardarCliente">
                                <span wire:loading.remove wire:target="guardarCliente">Guardar y Seleccionar</span>
                                <span wire:loading wire:target="guardarCliente">Guardando...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Script para manejar el cierre del modal automáticamente -->
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('cerrar-modal-cliente', () => {
                    const modalElement = document.getElementById('modalNuevoClientePos');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                });
            });
        </script>

        <!-- Estilos para mejorar la visualización -->
        <style>
            .card-hover:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
            ::-webkit-scrollbar { width: 6px; }
            ::-webkit-scrollbar-track { background: #f8f9fa; }
            ::-webkit-scrollbar-thumb { background: #ced4da; border-radius: 10px; }
            ::-webkit-scrollbar-thumb:hover { background: #adb5bd; }
        </style>
    </div>
</div>
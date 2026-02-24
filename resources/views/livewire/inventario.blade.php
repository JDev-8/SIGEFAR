<div class="container py-4">
    <!-- Encabezado y Alertas -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 text-dark fw-bold">Gestión de Inventario (Almacén)</h2>
        <div>
            <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#modalNuevoLote">
                + Ingresar Lote (Mercancía)
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoProducto">
                + Nuevo Producto
            </button>
        </div>
    </div>

    @if (session()->has('mensaje'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('mensaje') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tabla de Productos -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Precio Base</th>
                            <th>Receta</th>
                            <th>Stock Real (Cajas)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productos as $producto)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $producto->codigo }}</span></td>
                                <td class="fw-bold">{{ $producto->nombre }}</td>
                                <td>{{ $producto->tipo }}</td>
                                <td>${{ number_format($producto->precio, 2) }}</td>
                                <td>
                                    @if($producto->requiere_receta)
                                        <span class="badge bg-danger">Sí</span>
                                    @else
                                        <span class="text-muted">No</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $stock = $producto->lotes->sum('cantidad_disponible');
                                    @endphp
                                    
                                    @if($stock == 0)
                                        <span class="text-danger fw-bold">Agotado</span>
                                    @elseif($stock <= 10)
                                        <span class="text-warning fw-bold">{{ $stock }} (Bajo)</span>
                                    @else
                                        <span class="text-success fw-bold">{{ $stock }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No hay productos registrados en el catálogo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL: Nuevo Producto -->
    <div class="modal fade" id="modalNuevoProducto" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Registrar Nuevo Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="guardarProducto">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Medicamento</label>
                            <input type="text" class="form-control" wire:model="nombre" required>
                            @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Código de Barras</label>
                                <input type="text" class="form-control" wire:model="codigo" required>
                                @error('codigo') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo (Ej: Analgésico)</label>
                                <input type="text" class="form-control" wire:model="tipo" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Precio de Venta ($)</label>
                                <input type="number" step="0.01" class="form-control" wire:model="precio" required>
                            </div>
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" wire:model="requiere_receta" id="checkReceta">
                                    <label class="form-check-label text-danger" for="checkReceta">
                                        Requiere Récipe Médico
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: Nuevo Lote -->
    <div class="modal fade" id="modalNuevoLote" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Ingresar Mercancía (Lote)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="guardarLote">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Seleccionar Producto</label>
                            <select class="form-select" wire:model="producto_id_lote" required>
                                <option value="">-- Seleccione un producto --</option>
                                @foreach($productos as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->codigo }} - {{ $prod->nombre }}</option>
                                @endforeach
                            </select>
                            @error('producto_id_lote') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Número de Lote (Caja)</label>
                            <input type="text" class="form-control" wire:model="numero_lote" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha de Vencimiento</label>
                                <input type="date" class="form-control" wire:model="fecha_vencimiento" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cantidad Físicas (Cajas)</label>
                                <input type="number" class="form-control" wire:model="cantidad" required min="1">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" wire:loading.attr="disabled">Registrar Entrada</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script para cerrar modales automáticamente tras guardar -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('cerrar-modal', () => {
                var modales = document.querySelectorAll('.modal.show');
                modales.forEach(modal => {
                    var modalInstance = bootstrap.Modal.getInstance(modal);
                    if(modalInstance) {
                        modalInstance.hide();
                    }
                });
                document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            });
        });
    </script>
</div>
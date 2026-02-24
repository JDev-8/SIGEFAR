<div>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 fw-bold text-dark">Inventario y Catálogo</h2>
            <div>
                <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#modalLote" wire:click="resetCampos">
                    + Entrada Mercancía
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProducto" wire:click="resetCampos">
                    + Nuevo Producto
                </button>
            </div>
        </div>

        @if (session()->has('mensaje'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                {{ session('mensaje') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @foreach($productos as $producto)
            <div class="card shadow-sm border-0 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-middle">
                    <div>
                        <span class="badge bg-secondary mb-1">{{ $producto->codigo }}</span>
                        <h5 class="mb-0 fw-bold">{{ $producto->nombre }} <small class="text-muted fs-6">({{ $producto->tipo }})</small></h5>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold fs-5 text-primary">${{ number_format($producto->precio, 2) }}</div>
                        <button class="btn btn-sm btn-link text-decoration-none p-0" wire:click="editarProducto({{ $producto->id }})" data-bs-toggle="modal" data-bs-target="#modalProducto">Editar Catálogo</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light small text-muted">
                            <tr>
                                <th class="ps-4">NÚMERO DE LOTE</th>
                                <th>FECHA VENCIMIENTO</th>
                                <th>STOCK DISPONIBLE</th>
                                <th class="text-end pe-4">GESTIÓN LOTE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($producto->lotes as $lote)
                                <tr class="align-middle">
                                    <td class="ps-4 fw-medium">{{ $lote->numero_lote }}</td>
                                    <td>{{ $lote->fecha_vencimiento }}</td>
                                    <td>
                                        <span class="fw-bold {{ $lote->cantidad_disponible <= 5 ? 'text-danger' : 'text-success' }}">
                                            {{ $lote->cantidad_disponible }} uds
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-secondary border-0" wire:click="editarLote({{ $lote->id }})" data-bs-toggle="modal" data-bs-target="#modalLote">Ajustar</button>
                                        <button class="btn btn-sm btn-outline-danger border-0" onclick="confirm('¿Eliminar este lote permanentemente?') || event.stopImmediatePropagation()" wire:click="eliminarLote({{ $lote->id }})">Borrar</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-3 text-muted small">Sin stock disponible para este producto.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    <!-- MODAL: CREAR / EDITAR PRODUCTO -->
    <div class="modal fade" id="modalProducto" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark">{{ $editandoProducto ? 'Editar Producto' : 'Registrar Nuevo Producto' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <form wire:submit.prevent="guardarProducto">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Nombre del Medicamento</label>
                            <input type="text" class="form-control" wire:model="nombre" required>
                            @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">Código de Barras</label>
                                <input type="text" class="form-control" wire:model="codigo" required>
                                @error('codigo') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">Tipo (Ej: Analgésico)</label>
                                <input type="text" class="form-control" wire:model="tipo" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">Precio de Venta ($)</label>
                                <input type="number" step="0.01" class="form-control" wire:model="precio" required>
                                @error('precio') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" wire:model="requiere_receta" id="checkReceta">
                                    <label class="form-check-label text-danger" for="checkReceta">
                                        Requiere Récipe
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetCampos">Cancelar</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            {{ $editandoProducto ? 'Actualizar Producto' : 'Guardar Producto' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: INGRESAR / AJUSTAR LOTE (MERCANCÍA) -->
    <div class="modal fade" id="modalLote" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark">{{ $editandoLote ? 'Ajustar Stock del Lote' : 'Ingresar Nueva Mercancía' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <!-- Si estamos editando, llamamos a actualizarLote, sino a guardarLote -->
                <form wire:submit.prevent="{{ $editandoLote ? 'actualizarLote' : 'guardarLote' }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Seleccionar Producto</label>
                            <select class="form-select" wire:model="producto_id_lote" required @if($editandoLote) disabled @endif>
                                <option value="">-- Seleccione un producto --</option>
                                @foreach($productos as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->codigo }} - {{ $prod->nombre }}</option>
                                @endforeach
                            </select>
                            @error('producto_id_lote') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Número de Lote</label>
                            <input type="text" class="form-control" wire:model="numero_lote" required @if($editandoLote) disabled @endif>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">Fecha de Vencimiento</label>
                                <input type="date" class="form-control" wire:model="fecha_vencimiento" required @if($editandoLote) disabled @endif>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">Cantidad (Stock)</label>
                                <input type="number" class="form-control" wire:model="cantidad" required min="0">
                            </div>
                        </div>
                        @if($editandoLote)
                            <div class="alert alert-warning small py-2 mb-0">
                                <strong>Nota:</strong> Solo se permite modificar la cantidad física (ajuste de stock). Para otros cambios, elimine y cree un nuevo lote.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetCampos">Cancelar</button>
                        <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
                            {{ $editandoLote ? 'Guardar Ajuste' : 'Registrar Entrada' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('cerrar-modal', () => {
                var modales = document.querySelectorAll('.modal.show');
                modales.forEach(m => bootstrap.Modal.getInstance(m).hide());
            });
        });
    </script>
</div>
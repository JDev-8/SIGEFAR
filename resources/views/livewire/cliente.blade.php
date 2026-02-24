<div>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 fw-bold text-dark">Gestión de Clientes</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCliente" wire:click="resetCampos">
                + Nuevo Cliente
            </button>
        </div>

        @if (session()->has('mensaje'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                {{ session('mensaje') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th class="ps-4">CÉDULA</th>
                            <th>NOMBRE COMPLETO</th>
                            <th>PUNTOS ACUMULADOS</th>
                            <th class="text-end pe-4">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clientes as $cliente)
                            <tr class="align-middle">
                                <td class="ps-4 fw-medium text-muted">{{ $cliente->persona->cedula }}</td>
                                <td class="fw-bold">{{ $cliente->persona->nombre }} {{ $cliente->persona->apellido }}</td>
                                <td>
                                    <span class="badge bg-info-subtle text-info px-3">{{ $cliente->puntos }} Pts</span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-secondary border-0" wire:click="editar({{ $cliente->persona_id }})" data-bs-toggle="modal" data-bs-target="#modalCliente">
                                        Editar
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger border-0" onclick="confirm('¿Eliminar cliente?') || event.stopImmediatePropagation()" wire:click="eliminar({{ $cliente->persona_id }})">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-5 text-muted">No hay clientes registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Cliente -->
    <div class="modal fade" id="modalCliente" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0 bg-light">
                    <h5 class="fw-bold">{{ $editando ? 'Editar Cliente' : 'Nuevo Cliente' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit="guardar">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nombre</label>
                            <input type="text" class="form-control" wire:model="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Apellido</label>
                            <input type="text" class="form-control" wire:model="apellido" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Cédula</label>
                                <input type="text" class="form-control" wire:model="cedula" required>
                                @error('cedula') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Puntos Iniciales</label>
                                <input type="number" class="form-control" wire:model="puntos">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('cerrar-modal', () => {
                var modal = bootstrap.Modal.getInstance(document.getElementById('modalCliente'));
                if(modal) modal.hide();
            });
        });
    </script>
</div>
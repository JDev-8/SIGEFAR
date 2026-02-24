<div>
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">
            
            <!-- Encabezado / Logo Farmacia -->
            <div class="text-center mb-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 64px; height: 64px;">
                    <span class="fs-1 fw-bold">+</span>
                </div>
                <h2 class="h4 fw-bold text-dark mb-1">Farmacia Vitalidad</h2>
                <p class="text-muted small">Sistema de Gestión y POS</p>
            </div>

            <!-- Formulario Livewire Tradicional -->
            <form wire:submit.prevent="authenticate">
                
                @error('auth_failed')
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <div>{{ $message }}</div>
                    </div>
                @enderror

                <div class="form-floating mb-3">
                    <input 
                        wire:model="nombre_usuario" 
                        type="text" 
                        class="form-control @error('nombre_usuario') is-invalid @enderror" 
                        id="nombre_usuario" 
                        placeholder="Ej: admin"
                        required 
                        autofocus
                    >
                    <label for="nombre_usuario">Usuario</label>
                    @error('nombre_usuario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-floating mb-4">
                    <input 
                        wire:model="contrasenia" 
                        type="password" 
                        class="form-control @error('contrasenia') is-invalid @enderror" 
                        id="contrasenia" 
                        placeholder="Contraseña"
                        required
                    >
                    <label for="contrasenia">Contraseña</label>
                    @error('contrasenia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button class="btn btn-primary w-100 py-2 fs-5 fw-medium" type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="authenticate">Ingresar al Sistema</span>
                    <span wire:loading wire:target="authenticate">
                        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Verificando...
                    </span>
                </button>

            </form>
        </div>
    </div>
</div>
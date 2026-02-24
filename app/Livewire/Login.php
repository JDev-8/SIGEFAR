<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\AuthService;
use Livewire\Attributes\Layout;

#[Layout('layouts.guest')] 
class Login extends Component
{
    public string $nombre_usuario = '';
    public string $contrasenia = '';

    protected array $rules = [
        'nombre_usuario' => 'required|string',
        'contrasenia'    => 'required|string',
    ];

    protected array $messages = [
        'nombre_usuario.required' => 'El nombre de usuario es obligatorio.',
        'contrasenia.required'    => 'La contraseña es obligatoria.',
    ];

    public function authenticate(AuthService $authService)
    {
        $this->validate();

        $loginExitoso = $authService->login($this->nombre_usuario, $this->contrasenia);

        if ($loginExitoso) {
            session()->regenerate();
            
            $rol = auth()->user()->rol;
            if ($rol === 'administrador') {
                return redirect()->intended('/inventario');
            } else {
                return redirect()->intended('/pos');
            }
        }

        $this->addError('auth_failed', 'Las credenciales proporcionadas son incorrectas.');
        $this->reset('contrasenia');
    }

    public function render()
    {
        return view('livewire.login');
    }
}
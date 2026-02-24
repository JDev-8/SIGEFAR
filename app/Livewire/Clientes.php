<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Cliente;
use App\Services\ClienteService;
use Livewire\Attributes\Layout;

#[Layout('layouts.guest')] 
class Clientes extends Component
{
    public $clientes = [];
    public $clienteId, $nombre, $apellido, $cedula, $puntos = 0;
    public bool $editando = false;

    public function mount()
    {
        $this->cargarClientes();
    }

    public function cargarClientes()
    {
        $this->clientes = Cliente::with('persona')->get();
    }

    public function resetCampos()
    {
        $this->reset(['clienteId', 'nombre', 'apellido', 'cedula', 'puntos', 'editando']);
    }

    public function guardar(ClienteService $service)
    {
        $rules = [
            'nombre'   => 'required|string',
            'apellido' => 'required|string',
            'cedula'   => 'required|string|unique:personas,cedula,' . ($this->editando ? Cliente::find($this->clienteId)->persona_id : 'NULL'),
            'puntos'   => 'numeric|min:0',
        ];

        $this->validate($rules);

        if ($this->editando) {
            $cliente = Cliente::findOrFail($this->clienteId);
            $service->actualizarCliente($cliente, 
                ['nombre' => $this->nombre, 'apellido' => $this->apellido, 'cedula' => $this->cedula],
                ['puntos' => $this->puntos]
            );
            session()->flash('mensaje', 'Cliente actualizado correctamente.');
        } else {
            $service->registrarCliente(
                ['nombre' => $this->nombre, 'apellido' => $this->apellido, 'cedula' => $this->cedula],
                ['puntos' => $this->puntos]
            );
            session()->flash('mensaje', 'Cliente registrado correctamente.');
        }

        $this->resetCampos();
        $this->cargarClientes();
        $this->dispatch('cerrar-modal');
    }

    public function editar($id)
    {
        $cliente = Cliente::with('persona')->findOrFail($id);
        $this->clienteId = $cliente->persona_id;
        $this->nombre = $cliente->persona->nombre;
        $this->apellido = $cliente->persona->apellido;
        $this->cedula = $cliente->persona->cedula;
        $this->puntos = $cliente->puntos;
        $this->editando = true;
    }

    public function eliminar($id, ClienteService $service)
    {
        $cliente = Cliente::findOrFail($id);
        $service->eliminarCliente($cliente);
        $this->cargarClientes();
        session()->flash('mensaje', 'Cliente eliminado del sistema.');
    }

    public function render()
    {
        return view('livewire.cliente');
    }
}
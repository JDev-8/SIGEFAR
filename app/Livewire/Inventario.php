<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Producto;
use App\Services\InventarioService;
use Livewire\Attributes\Layout;

#[Layout('layouts.guest')] 
class Inventario extends Component
{
    public $productos = [];

    public string $nombre = '';
    public string $codigo = '';
    public string $tipo = '';
    public string $precio = '';
    public bool $requiere_receta = false;

    public string $producto_id_lote = '';
    public string $numero_lote = '';
    public string $fecha_vencimiento = '';
    public string $cantidad = '';

    public function mount()
    {
        $this->cargarProductos();
    }

    public function cargarProductos()
    {
        $this->productos = Producto::with(['lotes' => function($query) {
            $query->where('fecha_vencimiento', '>=', now()->toDateString())
                  ->where('cantidad_disponible', '>', 0);
        }])->get();
    }

    public function guardarProducto(InventarioService $service)
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|unique:productos,codigo',
            'tipo'   => 'required|string',
            'precio' => 'required|numeric|min:0',
        ]);

        $service->registrarProducto([
            'nombre' => $this->nombre,
            'codigo' => $this->codigo,
            'tipo'   => $this->tipo,
            'precio' => $this->precio,
            'requiere_receta' => $this->requiere_receta ? 1 : 0,
        ]);

        $this->reset(['nombre', 'codigo', 'tipo', 'precio', 'requiere_receta']);
        $this->cargarProductos();
        
        session()->flash('mensaje', 'Producto registrado en el catálogo con éxito.');
        $this->dispatch('cerrar-modal');
    }

    public function guardarLote(InventarioService $service)
    {
        $this->validate([
            'producto_id_lote'  => 'required|exists:productos,id',
            'numero_lote'       => 'required|string|max:50',
            'fecha_vencimiento' => 'required|date|after:today',
            'cantidad'          => 'required|integer|min:1',
        ]);

        $producto = Producto::find($this->producto_id_lote);
        $adminId = auth()->user()->persona_id;

        $service->registrarLote($producto, [
            'numero_lote'       => $this->numero_lote,
            'fecha_vencimiento' => $this->fecha_vencimiento,
            'cantidad'          => $this->cantidad,
        ], $adminId);

        $this->reset(['producto_id_lote', 'numero_lote', 'fecha_vencimiento', 'cantidad']);
        $this->cargarProductos();

        session()->flash('mensaje', 'Lote de mercancía ingresado y auditado correctamente.');
        $this->dispatch('cerrar-modal');
    }

    public function render()
    {
        return view('livewire.inventario');
    }
}
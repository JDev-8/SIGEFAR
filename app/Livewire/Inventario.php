<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Producto;
use App\Models\Lote;
use App\Services\InventarioService;
use Livewire\Attributes\Layout;

#[Layout('layouts.guest')] 
class Inventario extends Component
{
    public $productos = [];
    public bool $editandoProducto = false;
    public bool $editandoLote = false;

    // Campos Producto
    public $productoId, $nombre, $codigo, $tipo, $precio, $requiere_receta = false;
    
    // Campos Lote
    public $loteId, $producto_id_lote, $numero_lote, $fecha_vencimiento, $cantidad;

    public function mount() { $this->cargarProductos(); }

    public function cargarProductos() {
        $this->productos = Producto::with(['lotes' => function($q) {
            $q->where('fecha_vencimiento', '>=', now()->toDateString());
        }])->get();
    }

    public function resetCampos() {
        $this->reset(['productoId', 'nombre', 'codigo', 'tipo', 'precio', 'requiere_receta', 'editandoProducto']);
        $this->reset(['loteId', 'producto_id_lote', 'numero_lote', 'fecha_vencimiento', 'cantidad', 'editandoLote']);
    }

    public function editarProducto($id) {
        $p = Producto::findOrFail($id);
        $this->productoId = $p->id;
        $this->nombre = $p->nombre; 
        $this->codigo = $p->codigo; 
        $this->tipo = $p->tipo;
        $this->precio = $p->precio; 
        $this->requiere_receta = (bool)$p->requiere_receta;
        $this->editandoProducto = true;
    }

    public function guardarProducto(InventarioService $service) {
        $this->validate([
            'nombre' => 'required', 
            'codigo' => 'required|unique:productos,codigo,' . ($this->editandoProducto ? $this->productoId : 'NULL'), 
            'precio' => 'required|numeric'
        ]);
        
        $datos = [
            'nombre' => $this->nombre, 
            'codigo' => $this->codigo, 
            'tipo' => $this->tipo, 
            'precio' => $this->precio, 
            'requiere_receta' => $this->requiere_receta
        ];
        
        if ($this->editandoProducto) {
            $service->actualizarProducto(Producto::find($this->productoId), $datos);
            session()->flash('mensaje', 'Producto actualizado.');
        } else {
            $service->registrarProducto($datos);
            session()->flash('mensaje', 'Producto registrado.');
        }

        $this->resetCampos(); 
        $this->cargarProductos(); 
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

    public function editarLote($id) {
        $l = Lote::findOrFail($id);
        $this->loteId = $l->id;
        $this->producto_id_lote = $l->producto_id;
        $this->numero_lote = $l->numero_lote;
        $this->fecha_vencimiento = $l->fecha_vencimiento;
        $this->cantidad = $l->cantidad_disponible;
        $this->editandoLote = true;
    }

    public function actualizarLote(InventarioService $service) {
        $lote = Lote::findOrFail($this->loteId);
        $service->ajustarLote($lote, $this->cantidad, auth()->user()->persona_id, "Ajuste manual desde panel");
        
        $this->resetCampos(); 
        $this->cargarProductos(); 
        $this->dispatch('cerrar-modal');
        session()->flash('mensaje', 'Stock de lote actualizado.');
    }

    public function eliminarLote($id) {
        Lote::findOrFail($id)->delete();
        $this->cargarProductos();
        session()->flash('mensaje', 'Lote eliminado del sistema.');
    }

    public function render() { return view('livewire.inventario'); }
}
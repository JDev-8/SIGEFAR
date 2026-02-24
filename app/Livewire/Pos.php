<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Producto;
use App\Models\Cliente;
use App\Services\VentaService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;

#[Layout('layouts.guest')] 
class Pos extends Component
{
    public string $busqueda = '';
    public Collection $productosBusqueda;

    public array $carrito = [];
    
    public string $metodo_pago = 'efectivo';
    public string $cliente_id = '';
    public Collection $clientesDisponibles;

    public function mount()
    {
        $this->productosBusqueda = collect();
        $this->clientesDisponibles = Cliente::with('persona')->get();
        $this->buscarProductos();
    }

    public function updatedBusqueda()
    {
        $this->buscarProductos();
    }

    public function buscarProductos()
    {
        if (empty($this->busqueda)) {
            $this->productosBusqueda = Producto::take(10)->get();
            return;
        }

        $this->productosBusqueda = Producto::where('nombre', 'like', '%' . $this->busqueda . '%')
            ->orWhere('codigo', 'like', '%' . $this->busqueda . '%')
            ->take(10)
            ->get();
    }

    public function agregarAlCarrito(int $productoId)
    {
        $producto = Producto::find($productoId);
        
        if (!$producto) return;

        $index = collect($this->carrito)->search(fn($item) => $item['producto_id'] === $productoId);

        if ($index !== false) {
            $this->carrito[$index]['cantidad']++;
            $this->carrito[$index]['subtotal'] = $this->carrito[$index]['cantidad'] * $this->carrito[$index]['precio'];
        } else {
            $this->carrito[] = [
                'producto_id' => $producto->id,
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
                'precio' => (float) $producto->precio,
                'cantidad' => 1,
                'subtotal' => (float) $producto->precio,
                'requiere_receta' => $producto->requiere_receta
            ];
        }
        
        $this->busqueda = '';
        $this->buscarProductos();
    }

    public function incrementarCantidad(int $index)
    {
        $this->carrito[$index]['cantidad']++;
        $this->carrito[$index]['subtotal'] = $this->carrito[$index]['cantidad'] * $this->carrito[$index]['precio'];
    }

    public function decrementarCantidad(int $index)
    {
        if ($this->carrito[$index]['cantidad'] > 1) {
            $this->carrito[$index]['cantidad']--;
            $this->carrito[$index]['subtotal'] = $this->carrito[$index]['cantidad'] * $this->carrito[$index]['precio'];
        } else {
            $this->eliminarDelCarrito($index);
        }
    }

    public function eliminarDelCarrito(int $index)
    {
        unset($this->carrito[$index]);
        $this->carrito = array_values($this->carrito);
    }

    public function getTotalProperty(): float
    {
        return collect($this->carrito)->sum('subtotal');
    }

    public function cobrar(VentaService $saleService)
    {
        if (empty($this->carrito)) {
            $this->addError('error_venta', 'El carrito está vacío.');
            return;
        }

        try {
            $vendedorId = auth()->user()->persona_id;
            $idDelCliente = empty($this->cliente_id) ? null : (int) $this->cliente_id;
            $pagarConPuntos = ($this->metodo_pago === 'puntos');

            $venta = $saleService->procesarVenta(
                $vendedorId,
                $this->carrito,
                $this->metodo_pago,
                $idDelCliente,
                $pagarConPuntos
            );

            $this->reset(['carrito', 'metodo_pago', 'cliente_id']);
            session()->flash('mensaje_exito', 'Venta #'.$venta->id.' procesada correctamente.');
            
        } catch (\Exception $e) {
            $this->addError('error_venta', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pos');
    }
}
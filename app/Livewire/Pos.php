<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Producto;
use App\Models\Cliente;
use App\Services\VentaService;
use App\Services\ClienteService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;

#[Layout('layouts.guest')] 
class Pos extends Component
{
    // Buscador de Productos
    public string $busqueda = '';
    public Collection $productosBusqueda;

    // Carrito de compras
    public array $carrito = [];
    
    // Datos de la Venta actual
    public string $metodo_pago = 'efectivo';
    
    // ----- NUEVO: Búsqueda dinámica de Clientes -----
    public string $cliente_id = '';
    public string $busquedaCliente = '';
    public $clientesFiltrados = [];
    public ?Cliente $clienteSeleccionado = null;

    // Campos para registrar cliente en caliente
    public string $cliente_nombre = '';
    public string $cliente_apellido = '';
    public string $cliente_cedula = '';
    public int $cliente_puntos = 0;

    public function mount()
    {
        $this->productosBusqueda = collect();
        $this->clientesFiltrados = collect();
        $this->buscarProductos();
    }

    // Se ejecuta al escribir en el buscador de productos
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

    // ----- NUEVO: Lógica del Buscador de Clientes -----
    public function updatedBusquedaCliente()
    {
        if (empty($this->busquedaCliente)) {
            $this->clientesFiltrados = collect();
            return;
        }

        // Buscamos por cédula o nombre usando la relación de persona
        $this->clientesFiltrados = Cliente::with('persona')
            ->whereHas('persona', function($q) {
                $q->where('cedula', 'like', '%' . $this->busquedaCliente . '%')
                  ->orWhere('nombre', 'like', '%' . $this->busquedaCliente . '%');
            })
            ->take(5) // Solo traemos los 5 mejores resultados para no saturar la vista
            ->get();
    }

    public function seleccionarCliente($personaId)
    {
        $this->clienteSeleccionado = Cliente::with('persona')->where('persona_id', $personaId)->first();
        $this->cliente_id = $personaId;
        $this->busquedaCliente = ''; // Limpiamos el buscador
        $this->clientesFiltrados = collect(); // Ocultamos la lista
    }

    public function removerCliente()
    {
        $this->cliente_id = '';
        $this->clienteSeleccionado = null;
        $this->busquedaCliente = '';
    }

    // ---------------------------------------------------

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

    public function guardarCliente(ClienteService $service)
    {
        $this->validate([
            'cliente_nombre'   => 'required|string|max:100',
            'cliente_apellido' => 'required|string|max:100',
            'cliente_cedula'   => 'required|string|unique:personas,cedula',
            'cliente_puntos'   => 'numeric|min:0',
        ]);

        $nuevoCliente = $service->registrarCliente(
            [
                'nombre' => $this->cliente_nombre, 
                'apellido' => $this->cliente_apellido, 
                'cedula' => $this->cliente_cedula
            ],
            ['puntos' => $this->cliente_puntos]
        );

        // Seleccionamos automáticamente al nuevo cliente usando el nuevo método
        $this->seleccionarCliente($nuevoCliente->persona_id);

        // Limpiamos los campos del modal
        $this->reset(['cliente_nombre', 'cliente_apellido', 'cliente_cedula', 'cliente_puntos']);
        
        session()->flash('mensaje_exito', 'Cliente registrado y añadido a la venta.');
        $this->dispatch('cerrar-modal-cliente');
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

            // Reseteamos el carrito y el cliente seleccionado para la próxima venta
            $this->reset(['carrito', 'metodo_pago']);
            $this->removerCliente();
            
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
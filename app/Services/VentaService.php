<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Transaccion;
use Illuminate\Support\Facades\DB;
use Exception;

class VentaService
{
  protected InventarioService $inventarioService;

  public function __construct(InventarioService $inventarioService)
  {
    $this->inventarioService = $inventarioService;
  }

  public function procesarVenta(int $vendedorId, array $carrito, string $metodoPago, ?int $clienteId = null, bool $pagarConPuntos = false): Venta
  {
    if(empty($carrito)) throw new Exception("El carrito no puede estar vacío.");

    return DB::transaction(function () use ($vendedorId, $carrito, $metodoPago, $clienteId, $pagarConPuntos){
      $totalVenta = 0;
      $itemValidados = [];

      foreach($carrito as $item){
        $producto = Producto::findOrFail($item['producto_id']);
        $subtotal = $producto->precio * $item['cantidad'];
        $totalVenta += $subtotal;

        $itemValidados[] = [
          'producto' => $producto,
          'cantidad' => $item['cantidad']
        ];
      }

      $cliente = null;
      if($clienteId){
        $cliente = Cliente::findOrFail($clienteId);

        if($pagarConPuntos && $cliente->puntos < $totalVenta) throw new Exception("El cliente no tiene puntos suficientes. Total: {$totalVenta}, Puntos: {$cliente->puntos}");
      }

      $venta = Venta::create([
        'vendedor_id' => $vendedorId,
        'cliente_id'  => $clienteId,
        'metodo_pago' => $pagarConPuntos ? 'puntos' : $metodoPago,
        'total'       => $totalVenta,
        'status'      => 'pagado',
      ]);

      foreach($itemValidados as $item){
        $this->inventarioService->procesarSalidaFEFO(
          $item['producto'],
          $item['cantidad'],
          $vendedorId,
          $venta->id
        );
      }

      if($cliente) {
        if($pagarConPuntos) $this->descontarPuntos($cliente, $totalVenta, $venta->id);
        else $this->otorgarPuntos($cliente, $totalVenta, $venta->id);
      }

      return $venta;
    });
  }

  private function otorgarPuntos(Cliente $cliente, float $totalVenta, int $ventaId): void
  {
    $puntosGanados = floor($totalVenta / 10);

    if($puntosGanados > 0){
      $cliente->increment('puntos', $puntosGanados);

      Transaccion::create([
        'cliente_id' => $cliente->persona_id,
        'monto'      => $puntosGanados,
        'tipo'       => 'ganados',
        'venta_id'   => $ventaId,
      ]);
    }
  }

  private function descontarPuntos(Cliente $cliente, float $totalVenta, int $ventaId): void
  {
    $puntosAGastar = ceil($totalVenta);

    $cliente->decrement('puntos', $puntosAGastar);

    Transaccion::create([
      'cliente_id' => $cliente->persona_id,
      'monto'      => -$puntosAGastar,
      'tipo'       => 'canjeados',
      'venta_id'   => $ventaId,
    ]);
  }
}
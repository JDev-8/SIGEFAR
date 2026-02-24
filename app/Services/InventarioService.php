<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Lote;
use App\Models\Auditoria;
use App\Models\VentaDetalle;
use Illuminate\Support\Facades\DB;
use Exception;

class InventarioService
{
  public function registrarProducto(array $datosProducto): Producto
  {
    return Producto::create($datosProducto);
  }

  public function actualizarProducto(Producto $producto, array $datosNuevos): Producto
  {
    $producto->update($datosNuevos);
        
    return $producto;
  }

  public function registrarLote(Producto $producto, array $datosLote, int $usuarioAdminId): Lote
  {
    return DB::transaction(function () use ($producto, $datosLote, $usuarioAdminId){
      $lote = $producto->lotes()->create([
        'numero_lote' => $datosLote['numero_lote'],
        'fecha_vencimiento' => $datosLote['fecha_vencimiento'],
        'cantidad_disponible' => $datosLote['cantidad'],
      ]);

      Auditoria::create([
        'producto_id' => $producto->id,
        'usuario_id'  => $usuarioAdminId,
        'tipo'        => 'crear',
        'cantidad'    => $datosLote['cantidad'],
        'comentarios' => "Ingreso de nuevo lote: {$datosLote['numero_lote']}. Vence: {$datosLote['fecha_vencimiento']}",
      ]);

      return $lote;
    });
  }

  public function obtenerStockReal(Producto $producto): int
  {
    return $producto->lotes()
                    ->where('fecha_vencimiento', '>=', now()->toDateString())
                    ->sum('cantidad_disponible');
  }

  public function procesarSalidaFEFO(Producto $producto, int $cantidadSolicitada, int $usuarioVendedorId, int $ventaId): array
  {
    $stockReal = $this->obtenerStockReal($producto);
    if($stockReal < $cantidadSolicitada) throw new Exception("Stock insuficiente para el producto: {$producto->nombre}. Solicitado: {$cantidadSolicitada}, Disponible: {$stockReal}");

    return DB::transaction(function () use ($producto, $cantidadSolicitada, $usuarioVendedorId, $ventaId){
      $lotesDisponibles = $producto->lotes()
            ->where('cantidad_disponible', '>', 0)
            ->where('fecha_vencimiento', '>=', now()->toDateString())
            ->orderBy('fecha_vencimiento', 'asc')
            ->lockForUpdate()
            ->get();
      $cantidadRestantePorDescontar = $cantidadSolicitada;
      $detallesGenerados = [];

      foreach($lotesDisponibles as $lote){
        if($cantidadRestantePorDescontar <= 0){
          break;
        }

        $cantidadADescontarDeEsteLote = min($lote->cantidad_disponible, $cantidadRestantePorDescontar);

        $lote->cantidad_diponible -= $cantidadADescontarDeEsteLote;
        $lote->save();

        Auditoria::create([
          'producto_id' => $producto->id,
          'usuario_id'  => $usuarioVendedorId,
          'tipo'        => 'venta',
          'cantidad'    => $cantidadADescontarDeEsteLote,
          'comentarios' => "Salida por venta #{$ventaId}. Lote usado: {$lote->numero_lote}",
        ]);

        $detallesGenerados[] = [
          'venta_id' => $ventaId,
          'lote_id' => $lote->id,
          'cantidad' => $cantidadADescontarDeEsteLote,
          'precio_al_momento' => $producto->precio,
        ];

        $cantidadRestantePorDescontar -= $cantidadADescontarDeEsteLote;
      }

      if($cantidadRestantePorDescontar > 0) throw new Exception("Error crítico: No se pudo completar la extracción de lotes para {$producto->nombre}.");

      VentaDetalle::insert($detallesGenerados);

      return $detallesGenerados;
    });
  }

  public function ajustarLote(Lote $lote, int $nuevaCantidad, int $usuarioAdminId, string $motivo): void
  {
    DB::transaction(function () use ($lote, $nuevaCantidad, $usuarioAdminId, $motivo) {
            
      $cantidadAnterior = $lote->cantidad_disponible;
      $diferencia = $nuevaCantidad - $cantidadAnterior;
            
      if ($diferencia === 0) return;

      $lote->cantidad_disponible = $nuevaCantidad;
      $lote->save();

      $tipoAuditoria = $diferencia > 0 ? 'crear' : 'eliminar';

      Auditoria::create([
        'producto_id' => $lote->producto_id,
        'usuario_id'  => $usuarioAdminId,
        'tipo'        => $tipoAuditoria,
        'cantidad'    => abs($diferencia),
        'comentarios' => "Ajuste manual de Lote {$lote->numero_lote}. Motivo: {$motivo}. Cambio: {$cantidadAnterior} -> {$nuevaCantidad}",
      ]);
    });
  }
}
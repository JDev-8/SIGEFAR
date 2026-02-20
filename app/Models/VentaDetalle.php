<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
      'venta_id',
      'lote_id',
      'cantidad',
      'precio_al_momento'
    ];

    public function lote(){
      return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function venta(){
      return $this->belongsTo(Venta::class, 'venta_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    use HasFactory;

    protected $fillable = [
      'producto_id',
      'numero_lote',
      'fecha_vencimiento',
      'cantidad_disponible'
    ];

    public function producto(){
      return $this->belongsTo(Producto::class, 'producto_id');
    }
}

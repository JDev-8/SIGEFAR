<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaccion extends Model
{
    use HasFactory;

    protected $fillable = [
      'cliente_id',
      'monto',
      'tipo',
      'venta_id'
    ];

    public function cliente(){
      return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function venta(){
      return $this->belongsTo(Venta::class, 'venta_id');
    }
}

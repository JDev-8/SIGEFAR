<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
      'vendedor_id',
      'cliente_id',
      'metodo_pago',
      'total',
      'status'
    ];

    public function vendedor(){
      return $this->belongsTo(Usuario::class, 'vendedor_id');
    }

    public function clientes(){
      return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    use HasFactory;

    protected $fillable = [
      'producto_id',
      'usuario_id',
      'tipo',
      'cantidad',
      'comentarios'
    ];

    public function producto(){
      return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function usuario(){
      return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}

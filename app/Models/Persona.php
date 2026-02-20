<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $fillable = [
      'nombre',
      'apellido',
      'cedula'
    ];

    public function usuarios(){
      return $this->hasOne(Usuario::class, 'persona_id');
    }

    public function clientes(){
      return $this->hasOne(Cliente::class, 'persona_id');
    } 
}

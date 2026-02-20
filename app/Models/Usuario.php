<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'persona_id';
    public $incrementing = false;

    protected $fillable = [
      'persona_id',
      'nombre_usuario',
      'contrasenia',
      'telefono',
      'direccion',
      'rol'
    ];

    protected $hidden = [
      'contrasenia',
    ];

    public function getAuthPassword()
    {
      return $this->contrasenia;
    }

    public function persona(){
      return $this->belongsTo(Persona::class, 'persona_id');
    }
}

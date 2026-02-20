<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $primaryKey = 'persona_id';
    
    protected $fillable = [
      'persona_id',
      'puntos'
    ];

    public function persona(){
      return $this->belongsTo(Persona::class, 'persona_id');
    }
}

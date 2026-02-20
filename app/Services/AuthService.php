<?php

namespace App\Services;

use App\Models\Persona;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthService
{
  public function registrarUsuario(array $datosPersona, array $datosUsuario): Usuario
  {
    return DB::transaction(function () use ($datosPersona, $datosUsuario){
      $persona = Persona::create([
        'nombre'   => $datosPersona['nombre'],
        'apellido' => $datosPersona['apellido'],
        'cedula'   => $datosPersona['cedula'],
      ]);

      $usuario = Usuario::create([
        'persona_id'     => $persona->id, 
        'nombre_usuario' => $datosUsuario['nombre_usuario'],
        'contrasenia'    => Hash::make($datosUsuario['contrasenia']), 
        'telefono'       => $datosUsuario['telefono'],
        'direccion'      => $datosUsuario['direccion'],
        'rol'            => $datosUsuario['rol'],
      ]);

      return $usuario;
    });
  }

  public function login(string $nombreUsuario, string $contraseniaTextoPlano): bool
  {
    $usuario = Usuario::where('nombre_usuario', $nombreUsuario)->first();

    if($usuario && Hash::check($contraseniaTextoPlano, $usuario->contrasenia)){
      Auth::login($usuario);
      return true;
    }

    return false;
  }

  public function logout(): void
  {
    Auth::logout();
  }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Persona;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class Usuarios extends Seeder
{
    public function run(): void
    {
        $personaAdmin = Persona::create([
          'nombre'   => 'Carlos',
          'apellido' => 'Gerente',
          'cedula'   => 'V-10000000',
        ]);

        Usuario::create([
          'persona_id'     => $personaAdmin->id,
          'nombre_usuario' => 'admin',
          'contrasenia'    => Hash::make('admin123'),
          'telefono'       => '0414-0000000',
          'direccion'      => 'Farmacia Principal',
          'rol'            => 'administrador',
        ]);

        $personaVendedor = Persona::create([
          'nombre'   => 'María',
          'apellido' => 'Cajera',
          'cedula'   => 'V-20000000',
        ]);

        Usuario::create([
          'persona_id'     => $personaVendedor->id,
          'nombre_usuario' => 'vendedor',
          'contrasenia'    => Hash::make('vendedor123'),
          'telefono'       => '0412-0000000',
          'direccion'      => 'Farmacia Principal',
          'rol'            => 'vendedor',
        ]);
    }
}

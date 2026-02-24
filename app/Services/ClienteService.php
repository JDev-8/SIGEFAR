<?php

namespace App\Services;

use App\Models\Persona;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class ClienteService
{
    public function registrarCliente(array $datosPersona, array $datosCliente): Cliente
    {
        return DB::transaction(function () use ($datosPersona, $datosCliente) {
            $persona = Persona::create($datosPersona);
            
            return $persona->clientes()->create([
                'puntos' => $datosCliente['puntos'] ?? 0,
            ]);
        });
    }

    public function actualizarCliente(Cliente $cliente, array $datosPersona, array $datosCliente): Cliente
    {
        return DB::transaction(function () use ($cliente, $datosPersona, $datosCliente) {
            $cliente->persona->update($datosPersona);
            $cliente->update($datosCliente);
            return $cliente;
        });
    }

    public function eliminarCliente(Cliente $cliente): void
    {
        DB::transaction(function () use ($cliente) {
            $persona = $cliente->persona;
            $cliente->delete();
            $persona->delete();
        });
    }
}
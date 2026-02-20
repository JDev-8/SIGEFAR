<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendedor_id');
            $table->unsignedBigInteger('cliente_id');
            $table->enum('metodo_pago', ['efectivo', 'puntoVenta', 'pagoMovil', 'puntos', 'mixta']);
            $table->decimal('total', 10, 2);
            $table->enum('status', ['pagado', 'Espera']);
            $table->timestamps();

            $table->foreign('vendedor_id')->references('id')->on('personas')->onDelete('cascade')->onUpdate('cascade'); 
            $table->foreign('cliente_id')->references('id')->on('personas')->onDelete('cascade')->onUpdate('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};

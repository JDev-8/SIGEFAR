<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Login;
use App\Livewire\Inventario;
use App\Livewire\Pos;
use App\Livewire\Clientes;
use App\Services\AuthService;

Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->rol === 'administrador' 
            ? redirect()->route('inventario') 
            : redirect()->route('pos');
    }
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::middleware('auth')->group(function () {
    
    Route::get('/inventario', Inventario::class)->name('inventario');
    
    Route::get('/pos', Pos::class)->name('pos');

    Route::get('/clientes', Clientes::class)->name('clientes');

    Route::post('/logout', function (AuthService $authService) {
        $authService->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});
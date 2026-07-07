<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportacaoController;
use App\Http\Controllers\RevisaoController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/exportar/exatos', [DashboardController::class, 'exportarExatos'])->name('exportar.exatos');

Route::get('/importar', [ImportacaoController::class, 'index'])->name('importacao.index');
Route::post('/importar/base', [ImportacaoController::class, 'importarBase'])->name('importacao.base');
Route::post('/importar/aprovados', [ImportacaoController::class, 'importarAprovados'])->name('importacao.aprovados');

Route::get('/revisao', [RevisaoController::class, 'index'])->name('revisao.index');
Route::post('/revisao/{match}/confirmar', [RevisaoController::class, 'confirmar'])->name('revisao.confirmar');
Route::post('/revisao/{match}/descartar', [RevisaoController::class, 'descartar'])->name('revisao.descartar');

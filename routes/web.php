<?php

use App\Http\Controllers\GraphController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/graph');
});

Route::prefix('graph')->name('graph.')->group(function () {
    Route::get('/', [GraphController::class, 'index'])->name('index');

    // Neuron routes
    Route::get('/neurons', [GraphController::class, 'getNeurons'])->name('neurons.index');
    Route::post('/neurons', [GraphController::class, 'createNeuron'])->name('neurons.store');
    Route::put('/neurons/{id}', [GraphController::class, 'updateNeuron'])->name('neurons.update');
    Route::delete('/neurons/{id}', [GraphController::class, 'deleteNeuron'])->name('neurons.destroy');

    // Relationship routes
    Route::get('/neurons/{id}/relationships', [GraphController::class, 'getRelationships'])->name('relationships.index');
    Route::post('/relationships', [GraphController::class, 'createRelationship'])->name('relationships.store');
    Route::delete('/relationships/{id}', [GraphController::class, 'deleteRelationship'])->name('relationships.destroy');
});

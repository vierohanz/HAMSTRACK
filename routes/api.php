<?php

use App\Http\Controllers\ArtificialController;
use App\Http\Controllers\CollectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//collect table
Route::get('/allCollect', [CollectController::class, 'allCollect'])->name('allCollect');
Route::get('/latestCollect', [CollectController::class, 'latestCollect'])->name('latestCollect');

//collect table
Route::get('/allTable', [ArtificialController::class, 'allTable'])->name('allTable');
Route::get('/halfTable', [ArtificialController::class, 'halfTable'])->name('halfTable');
Route::post('/postTable', [ArtificialController::class, 'postTable'])->name('postTable');

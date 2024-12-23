<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PokemonController;

Route::get('/{any}', function () {
    return view('home');
})->where('any', '.*');

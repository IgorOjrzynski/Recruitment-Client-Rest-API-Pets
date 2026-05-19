<?php

use App\Http\Controllers\PetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('pets.index');
});

Route::resource('pets', PetController::class)->whereNumber('pet');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;

Route::get('/', function () {
    return view('products.index');
});

Route::resource('products', ProductsController::class);

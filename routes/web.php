<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()
        ->route('document.index');
});

Route::resource('/document', DocumentController::class)
    ->names('document');

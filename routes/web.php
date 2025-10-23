<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()
        ->route('document.index');
});

Route::post('/pdf_signer', [PdfController::class, 'signDocument'])
    ->name('pdf_signer');


Route::resource('/document', DocumentController::class)
    ->names('document');

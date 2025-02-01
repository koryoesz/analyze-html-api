<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnalyzeHtmlController;

Route::prefix('html')->group(function() {

    Route::post('analyze', [AnalyzeHtmlController::class, 'analyze']);

});

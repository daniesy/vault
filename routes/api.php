<?php

use App\Http\Controllers\Api\FolderController;
use App\Http\Controllers\Api\FileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post("documents/{document}", fn() => ["message" => "Hello World!"]);

Route::middleware('auth.api')->group(function() {

    Route::get("/folders/search", [FolderController::class, 'search'])->name('folders.search');
    Route::apiResource('folders', FolderController::class);
    Route::apiResource('folders.files', FileController::class)->except(['index', 'show']);

    Route::post('/files/import', [FileController::class, 'import'])->name('files.import');
});

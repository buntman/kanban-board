<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectCardController;
use App\Http\Controllers\TaskController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::controller(ProjectController::class)->group(function () {
    Route::get('/projects', 'index');
    Route::post('/projects', 'store');
    Route::get('/projects/{project}', 'show');
});

Route::controller(ProjectCardController::class)->group(function () {
    Route::get('/project-cards', 'index');
    Route::post('/project-cards', 'store');
});

Route::controller(TaskController::class)->group(function () {
    Route::post('/tasks', 'store');
});

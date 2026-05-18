<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test', function () {
    return response()->json([
        'time' => now()
    ]);
});
Route::get('/test', function () {
    
    return response()->json([
        'message' => 'Response from Laravel Server',
        //'port' => env('APP_PORT'),
        'port' => request()->server('SERVER_PORT')
    ]);
});
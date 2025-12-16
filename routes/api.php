<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AskController;

Route::post('/ask', [AskController::class, 'ask']);
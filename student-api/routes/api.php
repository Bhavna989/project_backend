<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

/*
 | Route::apiResource creates ALL 5 routes in one line:
 |   GET    /api/students        → index()
 |   POST   /api/students        → store()
 |   GET    /api/students/{id}   → show()
 |   PUT    /api/students/{id}   → update()
 |   DELETE /api/students/{id}   → destroy()
*/
Route::apiResource('students', StudentController::class);
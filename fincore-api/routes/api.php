<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/health', function () {
    return response()->json(['status' => 'API is healthy', 'service' => 'Laravel']);
});
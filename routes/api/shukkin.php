<?php
use App\Http\Controllers\Shukkin\ShukkinController;

Route::middleware([])->post('/shukkin', ShukkinController::class.'@handle');

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgentController;

Route::post('/agent/report', [AgentController::class, 'receive'])
    ->middleware('agent.signature')
    ->name('api.agent.report');

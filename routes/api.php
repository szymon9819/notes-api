<?php

declare(strict_types=1);

use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn (): JsonResponse => response()->json([
    'status' => 'ok',
]))->name('system.ping');

Route::get('/docs', fn (): RedirectResponse => to_route('scramble.docs.ui'))->name('docs.show');

Route::apiResource('notes', NoteController::class);

Route::get('/tags', [TagController::class, 'index'])->name('tags.index');

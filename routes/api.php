<?php

declare(strict_types=1);

use App\Infrastructure\Http\Controllers\Api\AuthTokenController;
use App\Infrastructure\Http\Controllers\Api\NoteController;
use App\Infrastructure\Http\Controllers\Api\TagController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn (): JsonResponse => response()->json([
    'status' => 'ok',
]))->name('system.ping');

Route::get('/docs', fn (): RedirectResponse => to_route('scramble.docs.ui'))->name('docs.show');

Route::post('/login', [AuthTokenController::class, 'login'])->name('auth.login');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::prefix('notes')->name('notes.')->group(function (): void {
        Route::get('/', [NoteController::class, 'index'])->name('index');
        Route::post('/', [NoteController::class, 'store'])->name('store');
        Route::get('/{note}', [NoteController::class, 'show'])->name('show');
        Route::put('/{note}', [NoteController::class, 'update'])->name('update');
        Route::delete('/{note}', [NoteController::class, 'destroy'])->name('destroy');
    });

    Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
});

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'published_at', 'id']);
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'is_pinned']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};

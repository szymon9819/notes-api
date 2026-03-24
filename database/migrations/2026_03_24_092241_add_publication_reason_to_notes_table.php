<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table): void {
            $table->string('publication_reason_type', 20)->nullable()->after('published_at');
            $table->string('publication_reason_message', 80)->nullable()->after('publication_reason_type');
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table): void {
            $table->dropColumn([
                'publication_reason_type',
                'publication_reason_message',
            ]);
        });
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        $firstUserId = DB::table('users')->orderBy('id')->value('id');

        if (is_numeric($firstUserId)) {
            DB::table('notes')
                ->whereNull('user_id')
                ->update(['user_id' => (int) $firstUserId]);
        }
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};

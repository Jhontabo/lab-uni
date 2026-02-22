<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        return DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]) !== [];
    }

    public function up(): void
    {
        // Users - agregar índice en status si no existe
        Schema::table('users', function (Blueprint $table) {
            if (! $this->indexExists('users', 'users_status_index')) {
                $table->index('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if ($this->indexExists('users', 'users_status_index')) {
                $table->dropIndex('users_status_index');
            }
        });
    }
};

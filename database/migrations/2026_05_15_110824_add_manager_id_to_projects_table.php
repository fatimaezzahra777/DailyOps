<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('manager_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->nullOnDelete();
        });

        $fallbackManagerId = DB::table('users')
            ->where('role', 'member')
            ->value('id') ?? DB::table('users')->value('id');

        if ($fallbackManagerId) {
            DB::table('projects')
                ->whereNull('manager_id')
                ->update(['manager_id' => $fallbackManagerId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manager_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('creadations')) {
            DB::table('creadations')
                ->where('slug', 'videos')
                ->update(['name' => 'Videos']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('creadations')) {
            DB::table('creadations')
                ->where('slug', 'videos')
                ->update(['name' => 'Videos']);
        }
    }
};

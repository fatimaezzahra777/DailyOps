<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('client_email')->nullable()->after('logo_path');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE projects MODIFY status ENUM('pending', 'in_progress', 'testing', 'completed') DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE projects SET status = 'in_progress' WHERE status = 'testing'");
            DB::statement("ALTER TABLE projects MODIFY status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending'");
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('client_email');
        });
    }
};

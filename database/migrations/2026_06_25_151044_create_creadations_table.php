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
        Schema::create('creadations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->default('folder');
            $table->string('color')->default('#c50064');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        DB::table('creadations')->insert([
            [
                'name' => 'PDFs',
                'slug' => 'pdfs',
                'icon' => 'picture_as_pdf',
                'color' => '#c50064',
                'position' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Images',
                'slug' => 'images',
                'icon' => 'image',
                'color' => '#c50064',
                'position' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Vidéos',
                'slug' => 'videos',
                'icon' => 'smart_display',
                'color' => '#c50064',
                'position' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Documents',
                'slug' => 'documents',
                'icon' => 'description',
                'color' => '#c50064',
                'position' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Archives',
                'slug' => 'archives',
                'icon' => 'folder_zip',
                'color' => '#c50064',
                'position' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creadations');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ocr_files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->nullable();
            $table->longText('pdf_file_path')->nullable();
            $table->longText('img_file_path')->nullable();
            $table->enum('status', ['pending_drawing', 'processing', 'processed', 'completed', 'error'])
                ->default('pending_drawing');
            $table->longText('ocr_data')->nullable();
            $table->longText('drawing_info')->nullable();
            $table->integer('parent_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ocr_files');
    }
};

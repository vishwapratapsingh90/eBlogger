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
        Schema::create('blogs_import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('original_file_name');
            $table->string('processed_file_name');
            $table->string('file_path');
            $table->integer('total_records');
            $table->integer('total_processed');
            $table->integer('total_failed');
            $table->enum('status', ['0', '1', '2', '3', '4'])->comment('0 - Not Started, 1 - Inprogress, 2 - Partial Success with errors, 3 - Completed, 4 - Failed');
            $table->string('log_file_path')->default('');
            $table->foreignId('uploaded_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs_import_logs');
    }
};

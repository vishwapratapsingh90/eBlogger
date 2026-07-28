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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('content');
            // Foreign key constraint for the author_id column
            //
            // Option 1. Shorthand method to create a foreign key constraint referencing the "id" column of the "users" table
            // $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            //
            // Option 2. Explicitly define the foreign key constraint referencing the "id" column of the "users" table
            $table->foreignId('author_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};

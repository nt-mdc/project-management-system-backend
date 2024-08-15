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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('project_id')->constrained('projects');
            $table->string('title');
            $table->string('description');
            $table->date('start_at');
            $table->date('end_at');
            $table->enum('priority', ['low', 'medium', 'high']);
            $table->enum('status', ['available-soon','in-progress', 'done']);
            $table->string('assigned_email');
            $table->timestamps();

            $table->foreign('assigned_email')
            ->references('email')
            ->on('users')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

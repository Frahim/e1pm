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
        $table->foreignId('project_id')->constrained()->onDelete('cascade');
        $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
        $table->string('title');
        $table->text('description')->nullable();
        $table->enum('status', ['todo','in_progress','review','done','blocked'])->default('todo');
        $table->enum('priority', ['low','medium','high','urgent'])->default('medium');
        $table->dateTime('due_at')->nullable();
        $table->integer('position')->default(0);
        $table->timestamps();
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

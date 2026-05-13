<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->string('name');
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('job')->nullable();
            $table->text('analysis')->nullable();
            $table->text('medical_history')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->integer('age')->nullable();
            $table->boolean('profile_completed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};

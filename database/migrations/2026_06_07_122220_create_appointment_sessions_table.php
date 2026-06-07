<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')
                ->unique()
                ->constrained('appointments')
                ->cascadeOnDelete();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('patient_complaint')->nullable();
            $table->text('diagnosis')->nullable();
            $table->enum('status', [
                'in_progress',
                'completed',
                'cancelled',
            ])->default('in_progress');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_sessions');
    }
};

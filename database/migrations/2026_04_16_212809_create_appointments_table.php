<?php

use App\Enums\AppointmentStatus;
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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->dateTime('scheduled_at');
//            $table->enum('status', ['pending', 'confirmed', 'completed', 'canceled', 'no_show'])
//                ->default('pending');
            $table->enum('status', array_column(AppointmentStatus::cases(), 'value'))
                ->default(AppointmentStatus::CONFIRMED->value);
            $table->text('notes')->nullable();
            $table->unique(['doctor_id', 'scheduled_at']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

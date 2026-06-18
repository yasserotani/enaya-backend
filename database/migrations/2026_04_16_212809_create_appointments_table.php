<?php

use App\Enums\AppointmentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->restrictOnDelete();
            $table->dateTime('scheduled_at');
            $table->enum('status', array_column(AppointmentStatus::cases(), 'value'))
                ->default(AppointmentStatus::Scheduled->value);
            $table->text('visit_reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('cancelled_by')->nullable()->after('status'); // 'doctor' | 'patient' | 'receptionist'
            $table->unique(['doctor_id', 'scheduled_at']); // one doctor cannot have two appointments at the same time.

            $table->index('scheduled_at');
            $table->index('status');
            $table->index(['patient_id', 'status']);
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

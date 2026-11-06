
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('specialization')->default('Dokter Umum');
            $table->timestamps();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('gender', 10);
            $table->date('birth_date')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('inpatient_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number')->unique();
            $table->enum('room_class', ['I', 'II', 'III']);
            $table->enum('status', ['available', 'occupied'])->default('available');
            $table->timestamps();
        });

        Schema::create('operating_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_name')->unique();
            $table->enum('status', ['available', 'occupied'])->default('available');
            $table->timestamps();
        });

        // MASTER: pendaftaran pasien
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->restrictOnDelete();
            $table->date('registration_date');
            $table->enum('service_type', ['outpatient', 'inpatient', 'surgery']);
            $table->enum('status', ['registered', 'completed', 'cancelled'])->default('registered');
            $table->timestamps();

            $table->index(['doctor_id', 'registration_date']);
        });

        // DETAIL: layanan pada setiap pendaftaran
        Schema::create('registration_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->string('service_name');
            $table->foreignId('inpatient_room_id')->nullable()
                ->constrained('inpatient_rooms')->nullOnDelete();
            $table->foreignId('operating_room_id')->nullable()
                ->constrained('operating_rooms')->nullOnDelete();
            $table->dateTime('scheduled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_details');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('operating_rooms');
        Schema::dropIfExists('inpatient_rooms');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('doctors');
    }
};

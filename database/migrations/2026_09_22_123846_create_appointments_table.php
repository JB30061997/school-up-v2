<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->string('reference')->unique();

            $table->foreignId('environment_id')
                ->constrained('environments')
                ->cascadeOnDelete();

            $table->foreignId('appointment_type_id')
                ->constrained('appointment_types')
                ->restrictOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            /*
             * On stocke le début et la fin complets.
             * C'est plus propre que date + start_time + end_time
             * pour les recherches calendrier.
             */
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            $table->string('location')->nullable();

            /*
             * REQUESTED
             * CONFIRMED
             * REJECTED
             * CANCELLED
             * COMPLETED
             */
            $table->string('status', 30)->default('REQUESTED');

            $table->text('confirmation_note')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(
                ['environment_id', 'status'],
                'appointments_environment_status_index'
            );

            $table->index(
                ['environment_id', 'starts_at'],
                'appointments_environment_start_index'
            );

            $table->index(
                ['assigned_to', 'starts_at'],
                'appointments_assigned_start_index'
            );

            $table->index(
                ['created_by', 'status'],
                'appointments_creator_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
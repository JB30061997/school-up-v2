<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('appointment_id')
                ->constrained('appointments')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action', 50);

            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();

            $table->text('note')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['appointment_id', 'created_at'],
                'appointment_history_appointment_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_histories');
    }
};
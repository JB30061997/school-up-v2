<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_types', function (Blueprint $table) {
            $table->id();

            $table->foreignId('environment_id')
                ->constrained('environments')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('code', 50);

            $table->text('description')->nullable();

            // Durée par défaut du rendez-vous en minutes
            $table->unsignedInteger('duration_minutes')->default(30);

            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(
                ['environment_id', 'code'],
                'appointment_type_environment_code_unique'
            );

            $table->index(
                ['environment_id', 'active'],
                'appointment_type_environment_active_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_types');
    }
};
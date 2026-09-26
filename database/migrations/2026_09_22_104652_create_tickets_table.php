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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // Référence métier
            $table->string('reference')->unique();

            // Établissement
            $table->foreignId('environment_id')
                ->constrained()
                ->restrictOnDelete();

            // Catégorie
            $table->foreignId('ticket_category_id')
                ->constrained()
                ->restrictOnDelete();

            // Statut
            $table->foreignId('ticket_status_id')
                ->constrained()
                ->restrictOnDelete();

            // Créateur
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            // Équipe responsable
            $table->foreignId('support_team_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Agent responsable
            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('subject');
            $table->longText('description');

            $table->enum('priority', [
                'low',
                'normal',
                'high',
                'urgent',
            ])->default('normal');

            // SLA
            $table->timestamp('due_at')->nullable();

            // Cycle de vie
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            $table->index([
                'environment_id',
                'ticket_status_id',
            ]);

            $table->index([
                'environment_id',
                'assigned_to',
            ]);

            $table->index('due_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};

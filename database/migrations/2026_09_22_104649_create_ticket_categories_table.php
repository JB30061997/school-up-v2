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
        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('environment_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('code');

            $table->text('description')->nullable();

            // Délai maximum de traitement en minutes
            $table->unsignedInteger('sla_minutes')->nullable();

            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(
                ['environment_id', 'code'],
                'environment_ticket_category_unique'
            );

            $table->index(['environment_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_categories');
    }
};

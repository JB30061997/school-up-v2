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
        Schema::create('school_years', function (Blueprint $table) {
            $table->id();

            $table->foreignId('environment_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name'); // 2026-2027

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->boolean('is_current')->default(false);
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->unique(
                ['environment_id', 'name'],
                'environment_school_year_unique'
            );

            $table->index(['environment_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_years');
    }
};

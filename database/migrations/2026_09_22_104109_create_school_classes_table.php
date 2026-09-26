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
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('level_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('school_year_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('code');

            $table->unsignedInteger('capacity')->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->unique(
                ['level_id', 'school_year_id', 'code'],
                'level_year_class_unique'
            );

            $table->index(['school_year_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};

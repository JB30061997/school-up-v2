<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_types', function (Blueprint $table) {
            $table->id();

            $table->foreignId('environment_id')
                ->constrained('environments')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('code', 50);

            $table->text('description')->nullable();

            $table->unsignedInteger('sla_minutes')->nullable();

            $table->boolean('requires_approval')->default(false);

            $table->boolean('active')->default(true);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(
                ['environment_id', 'code'],
                'request_types_environment_code_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_types');
    }
};

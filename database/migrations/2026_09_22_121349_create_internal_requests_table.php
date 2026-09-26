<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_requests', function (Blueprint $table) {
            $table->id();

            $table->string('reference')->unique();

            $table->foreignId('environment_id')
                ->constrained('environments')
                ->restrictOnDelete();

            $table->foreignId('request_type_id')
                ->constrained('request_types')
                ->restrictOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('subject');

            $table->text('description')->nullable();

            $table->string('status', 30)
                ->default('DRAFT');

            $table->string('priority', 20)
                ->default('normal');

            $table->timestamp('submitted_at')->nullable();

            $table->timestamp('assigned_at')->nullable();

            $table->timestamp('started_at')->nullable();

            $table->timestamp('due_at')->nullable();

            $table->timestamp('approved_at')->nullable();

            $table->timestamp('rejected_at')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index([
                'environment_id',
                'status',
            ]);

            $table->index([
                'assigned_to',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_requests');
    }
};

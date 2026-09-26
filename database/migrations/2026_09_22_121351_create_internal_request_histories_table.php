<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_request_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('internal_request_id')
                ->constrained('internal_requests')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action', 50);

            $table->string('from_status', 30)
                ->nullable();

            $table->string('to_status', 30)
                ->nullable();

            $table->text('note')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamp('created_at')
                ->useCurrent();

            $table->index([
                'internal_request_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_request_histories');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_request_comments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('internal_request_id')
                ->constrained('internal_requests')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->text('message');

            $table->boolean('is_internal')
                ->default(false);

            $table->timestamps();

            $table->index([
                'internal_request_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_request_comments');
    }
};

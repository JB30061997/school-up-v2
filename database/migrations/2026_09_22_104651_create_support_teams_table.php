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
        Schema::create('support_teams', function (Blueprint $table) {
            $table->id();

            $table->foreignId('environment_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('code');

            $table->text('description')->nullable();

            $table->foreignId('leader_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->unique(
                ['environment_id', 'code'],
                'environment_support_team_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_teams');
    }
};

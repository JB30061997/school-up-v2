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
        Schema::table('support_team_user', function (Blueprint $table) {

            $table->foreignId('support_team_id')
                ->after('id')
                ->constrained('support_teams')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->after('support_team_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->unique(
                ['support_team_id', 'user_id'],
                'support_team_user_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_team_user', function (Blueprint $table) {

            $table->dropUnique('support_team_user_unique');

            $table->dropForeign([
                'support_team_id',
            ]);

            $table->dropForeign([
                'user_id',
            ]);

            $table->dropColumn([
                'support_team_id',
                'user_id',
            ]);
        });
    }
};
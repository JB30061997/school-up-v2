<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('environments', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('url');
            $table->string('logo_dark')->nullable()->after('logo');

            $table->string('primary_color', 20)
                ->default('#2563EB')
                ->after('logo_dark');

            $table->string('secondary_color', 20)
                ->default('#1E40AF')
                ->after('primary_color');

            $table->string('sidebar_color', 20)
                ->default('#0F172A')
                ->after('secondary_color');

            $table->string('sidebar_text_color', 20)
                ->default('#FFFFFF')
                ->after('sidebar_color');

            $table->string('accent_color', 20)
                ->default('#3B82F6')
                ->after('sidebar_text_color');

            $table->string('favicon')->nullable()->after('accent_color');
        });
    }

    public function down(): void
    {
        Schema::table('environments', function (Blueprint $table) {
            $table->dropColumn([
                'logo',
                'logo_dark',
                'primary_color',
                'secondary_color',
                'sidebar_color',
                'sidebar_text_color',
                'accent_color',
                'favicon',
            ]);
        });
    }
};

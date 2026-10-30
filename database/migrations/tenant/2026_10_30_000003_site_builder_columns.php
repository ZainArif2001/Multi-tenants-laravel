<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('description');
            $table->string('hero_title')->nullable()->after('logo_path');
            $table->string('hero_subtitle', 500)->nullable()->after('hero_title');
            $table->boolean('show_hero')->default(true)->after('hero_subtitle');
            $table->boolean('show_posts')->default(true)->after('show_hero');
            $table->boolean('show_contact')->default(true)->after('show_posts');
            $table->string('footer_text', 300)->nullable()->after('linkedin_url');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'logo_path', 'hero_title', 'hero_subtitle',
                'show_hero', 'show_posts', 'show_contact', 'footer_text',
            ]);
        });
    }
};

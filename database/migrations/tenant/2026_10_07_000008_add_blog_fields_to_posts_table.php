<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('slug')->nullable()->after('title');
            $table->string('excerpt', 500)->nullable()->after('body');
            $table->string('featured_image')->nullable()->after('excerpt');
            $table->unsignedBigInteger('views')->default(0)->after('status');
            $table->boolean('featured')->default(false)->after('views');
        });

        // Backfill slugs + excerpts for posts created before this migration
        foreach (DB::table('posts')->whereNull('slug')->get() as $row) {
            $slug = \Illuminate\Support\Str::slug($row->title);
            $base = $slug;
            $i = 1;
            while (DB::table('posts')->where('slug', $slug)->where('id', '!=', $row->id)->exists()) {
                $slug = $base.'-'.$i++;
            }
            DB::table('posts')->where('id', $row->id)->update([
                'slug' => $slug,
                'excerpt' => $row->excerpt ?? \Illuminate\Support\Str::limit($row->body, 160),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn(['slug', 'excerpt', 'featured_image', 'views', 'featured']);
        });
    }
};

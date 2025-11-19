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
        if (! Schema::hasColumn('reviews', 'reviewed_at')) {
            Schema::table('reviews', function (Blueprint $table): void {
                $table->timestamp('reviewed_at')->nullable()->after('comment');
            });
        }

        if (Schema::hasColumn('reviews', 'image')) {
            Schema::table('reviews', function (Blueprint $table): void {
                $table->dropColumn('image');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('reviews', 'image')) {
            Schema::table('reviews', function (Blueprint $table): void {
                $table->string('image')->nullable()->after('comment');
            });
        }

        if (Schema::hasColumn('reviews', 'reviewed_at')) {
            Schema::table('reviews', function (Blueprint $table): void {
                $table->dropColumn('reviewed_at');
            });
        }
    }
};

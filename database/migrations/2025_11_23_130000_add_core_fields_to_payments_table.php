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
        Schema::table('payments', function (Blueprint $table) {
            $table->json('payload')->nullable()->after('snap_redirect_url');
            $table->text('qr_string')->nullable()->after('payload');
            $table->string('payment_link')->nullable()->after('qr_string');
            $table->string('payment_code')->nullable()->after('payment_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payload', 'qr_string', 'payment_link', 'payment_code']);
        });
    }
};

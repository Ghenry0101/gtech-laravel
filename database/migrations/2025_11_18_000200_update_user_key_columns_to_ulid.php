<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $this->dropForeignIfExists('addresses', 'addresses_user_id_foreign');
        $this->dropForeignIfExists('carts', 'carts_user_id_foreign');
        $this->dropForeignIfExists('orders', 'orders_user_id_foreign');
        $this->dropIndexIfExists('sessions', 'sessions_user_id_index');

        DB::statement('ALTER TABLE `users` MODIFY `id` CHAR(26) NOT NULL');
        DB::statement('ALTER TABLE `addresses` MODIFY `user_id` CHAR(26) NOT NULL');
        DB::statement('ALTER TABLE `carts` MODIFY `user_id` CHAR(26) NOT NULL');
        DB::statement('ALTER TABLE `orders` MODIFY `user_id` CHAR(26) NOT NULL');
        DB::statement('ALTER TABLE `sessions` MODIFY `user_id` CHAR(26) NULL');

        Schema::table('addresses', function (Blueprint $table): void {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        Schema::table('carts', function (Blueprint $table): void {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        Schema::table('sessions', function (Blueprint $table): void {
            $table->index('user_id', 'sessions_user_id_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $this->dropForeignIfExists('addresses', 'addresses_user_id_foreign');
        $this->dropForeignIfExists('carts', 'carts_user_id_foreign');
        $this->dropForeignIfExists('orders', 'orders_user_id_foreign');
        $this->dropIndexIfExists('sessions', 'sessions_user_id_index');

        DB::statement('ALTER TABLE `users` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE `addresses` MODIFY `user_id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `carts` MODIFY `user_id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `orders` MODIFY `user_id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `sessions` MODIFY `user_id` BIGINT UNSIGNED NULL');

        Schema::table('addresses', function (Blueprint $table): void {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        Schema::table('carts', function (Blueprint $table): void {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        Schema::table('sessions', function (Blueprint $table): void {
            $table->index('user_id', 'sessions_user_id_index');
        });
    }

    protected function dropForeignIfExists(string $table, string $constraint): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $database = DB::getDatabaseName();

        $exists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->exists();

        if ($exists) {
            DB::statement(sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $table, $constraint));
        }
    }

    protected function dropIndexIfExists(string $table, string $index): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $database = DB::getDatabaseName();

        $exists = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $index)
            ->exists();

        if ($exists) {
            DB::statement(sprintf('ALTER TABLE `%s` DROP INDEX `%s`', $table, $index));
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_verification_tokens', function (Blueprint $table) {
            $table->dropUnique(['token']);
        });

        Schema::table('email_verification_tokens', function (Blueprint $table) {
            $table->dropColumn('token');
            $table->string('code', 6);
            $table->index(['user_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('email_verification_tokens', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'code']);
            $table->dropColumn('code');
            $table->string('token', 64)->unique();
        });
    }
};

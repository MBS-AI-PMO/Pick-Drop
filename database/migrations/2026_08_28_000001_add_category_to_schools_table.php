<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('schools') || Schema::hasColumn('schools', 'category')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->string('category')->default('school')->after('name');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('schools') || ! Schema::hasColumn('schools', 'category')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};

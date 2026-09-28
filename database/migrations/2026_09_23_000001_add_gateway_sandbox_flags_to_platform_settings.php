<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('platform_settings', 'jazzcash_sandbox')) {
                $table->boolean('jazzcash_sandbox')->default(true)->after('jazzcash_return_url');
            }
            if (! Schema::hasColumn('platform_settings', 'easypaisa_sandbox')) {
                $table->boolean('easypaisa_sandbox')->default(true)->after('easypaisa_return_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            if (Schema::hasColumn('platform_settings', 'jazzcash_sandbox')) {
                $table->dropColumn('jazzcash_sandbox');
            }
            if (Schema::hasColumn('platform_settings', 'easypaisa_sandbox')) {
                $table->dropColumn('easypaisa_sandbox');
            }
        });
    }
};

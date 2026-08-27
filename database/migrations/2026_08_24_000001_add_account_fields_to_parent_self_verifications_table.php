<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parent_self_verifications', function (Blueprint $table) {
            if (!Schema::hasColumn('parent_self_verifications', 'account_type')) {
                $table->string('account_type', 20)->default('parent')->after('user_id');
                $table->index('account_type');
            }
            if (!Schema::hasColumn('parent_self_verifications', 'father_name')) {
                $table->string('father_name')->nullable()->after('full_name');
            }
            if (!Schema::hasColumn('parent_self_verifications', 'terms_accepted')) {
                $table->boolean('terms_accepted')->default(false)->after('selfie_photo');
            }
            if (!Schema::hasColumn('parent_self_verifications', 'terms_accepted_at')) {
                $table->timestamp('terms_accepted_at')->nullable()->after('terms_accepted');
            }
        });

        $rows = DB::table('parent_self_verifications')->select('id', 'user_id')->get();
        foreach ($rows as $row) {
            $role = strtolower(trim((string) DB::table('users')->where('id', $row->user_id)->value('role')));
            DB::table('parent_self_verifications')->where('id', $row->id)->update([
                'account_type' => $role === 'self' ? 'self' : 'parent',
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('parent_self_verifications', function (Blueprint $table) {
            if (Schema::hasColumn('parent_self_verifications', 'account_type')) {
                $table->dropIndex(['account_type']);
                $table->dropColumn('account_type');
            }
            if (Schema::hasColumn('parent_self_verifications', 'father_name')) {
                $table->dropColumn('father_name');
            }
            if (Schema::hasColumn('parent_self_verifications', 'terms_accepted_at')) {
                $table->dropColumn('terms_accepted_at');
            }
            if (Schema::hasColumn('parent_self_verifications', 'terms_accepted')) {
                $table->dropColumn('terms_accepted');
            }
        });
    }
};

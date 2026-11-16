<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('location_points', function (Blueprint $table) {
            if (! Schema::hasColumn('location_points', 'pickup_address')) {
                $table->string('pickup_address', 500)->nullable()->after('address');
            }
            if (! Schema::hasColumn('location_points', 'drop_address')) {
                $table->string('drop_address', 500)->nullable()->after('pickup_address');
            }
        });

        DB::table('location_points')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $pickup = in_array($row->type, ['pickup', 'both'], true) ? $row->address : null;
                $drop = in_array($row->type, ['drop', 'both'], true) ? $row->address : null;

                DB::table('location_points')->where('id', $row->id)->update([
                    'pickup_address' => $row->pickup_address ?: $pickup,
                    'drop_address' => $row->drop_address ?: $drop,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('location_points', function (Blueprint $table) {
            if (Schema::hasColumn('location_points', 'drop_address')) {
                $table->dropColumn('drop_address');
            }
            if (Schema::hasColumn('location_points', 'pickup_address')) {
                $table->dropColumn('pickup_address');
            }
        });
    }
};

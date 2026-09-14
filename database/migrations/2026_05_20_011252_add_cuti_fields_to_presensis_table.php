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
        Schema::table('presensis', function (Blueprint $table) {
            $table->string('cuti_group_id', 50)->nullable()->index()->after('status_izin');
            $table->date('cuti_start')->nullable()->after('cuti_group_id');
            $table->date('cuti_end')->nullable()->after('cuti_start');
        });
    }

    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropColumn(['cuti_group_id', 'cuti_start', 'cuti_end']);
        });
    }
};

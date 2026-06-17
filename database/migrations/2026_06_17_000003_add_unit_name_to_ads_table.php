<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->string('unit_name', 100)->nullable()->after('current_branch_id');
        });

        $rows = DB::table('ads')
            ->join('military_branches', 'ads.current_branch_id', '=', 'military_branches.id')
            ->select('ads.id', 'military_branches.name')
            ->get();

        foreach ($rows as $row) {
            DB::table('ads')->where('id', $row->id)->update(['unit_name' => $row->name]);
        }
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn('unit_name');
        });
    }
};

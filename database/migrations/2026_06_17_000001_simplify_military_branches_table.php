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
            $table->dropForeign(['current_branch_id']);
        });

        Schema::table('military_branches', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });

        Schema::dropIfExists('military_organizations');
        Schema::dropIfExists('military_branches');

        Schema::create('military_branches', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['army', 'sepah', 'police']);
            $table->string('name');
            $table->timestamps();
        });

        foreach (DB::table('ads')->get() as $ad) {
            $branchId = DB::table('military_branches')->insertGetId([
                'type' => 'army',
                'name' => 'نامشخص',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('ads')->where('id', $ad->id)->update(['current_branch_id' => $branchId]);
        }

        Schema::table('ads', function (Blueprint $table) {
            $table->foreign('current_branch_id')->references('id')->on('military_branches');
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropForeign(['current_branch_id']);
        });

        Schema::dropIfExists('military_branches');

        Schema::create('military_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('military_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('military_organizations')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('ads', function (Blueprint $table) {
            $table->foreign('current_branch_id')->references('id')->on('military_branches');
        });
    }
};

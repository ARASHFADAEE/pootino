<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('military_branches', 'organization_id')) {
            return;
        }

        $this->dropForeignKeyIfExists('ads', 'current_branch_id');
        $this->dropForeignKeyIfExists('military_branches', 'organization_id');

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
        if (Schema::hasColumn('military_branches', 'organization_id')) {
            return;
        }

        $this->dropForeignKeyIfExists('ads', 'current_branch_id');

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

    private function dropForeignKeyIfExists(string $table, string $column): void
    {
        $database = Schema::getConnection()->getDatabaseName();

        $constraints = DB::select(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, $table, $column]
        );

        foreach ($constraints as $constraint) {
            Schema::table($table, function (Blueprint $table) use ($constraint) {
                $table->dropForeign($constraint->CONSTRAINT_NAME);
            });
        }
    }
};

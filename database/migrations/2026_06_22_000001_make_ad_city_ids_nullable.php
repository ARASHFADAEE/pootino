<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->foreignId('current_city_id')->nullable()->change();
            $table->foreignId('desired_city_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->foreignId('current_city_id')->nullable(false)->change();
            $table->foreignId('desired_city_id')->nullable(false)->change();
        });
    }
};

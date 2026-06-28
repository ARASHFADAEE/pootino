<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // جدول سازمان‌ها در نسخه جدید حذف شده؛ این migration فقط برای سازگاری تاریخچه باقی مانده.
    }

    public function down(): void
    {
        if (Schema::hasTable('military_organizations')) {
            return;
        }

        Schema::create('military_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }
};

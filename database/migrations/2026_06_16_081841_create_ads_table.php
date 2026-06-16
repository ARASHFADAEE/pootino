<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->foreignId('current_province_id')->constrained('provinces');
            $table->foreignId('current_city_id')->constrained('cities');
            $table->foreignId('current_branch_id')->constrained('military_branches');
            $table->foreignId('desired_province_id')->constrained('provinces');
            $table->foreignId('desired_city_id')->constrained('cities');
            $table->foreignId('rank_id')->constrained('ranks');
            $table->foreignId('education_level_id')->constrained('education_levels');
            $table->string('phone', 11);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('views')->default(0);
            $table->boolean('edited_after_approval')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};

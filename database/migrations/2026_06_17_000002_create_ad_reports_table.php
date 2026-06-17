<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip', 45);
            $table->enum('reason', [
                'fake',
                'duplicate',
                'spam',
                'other',
            ]);
            $table->string('description', 300)->nullable();
            $table->timestamps();
            $table->unique(['ad_id', 'ip']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_reports');
    }
};

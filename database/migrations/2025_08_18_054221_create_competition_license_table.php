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
Schema::create('competition_license', function (Blueprint $table) {
    $table->id(); // 主キー（あった方が便利）
    $table->foreignId('competition_id')->constrained()->onDelete('cascade');
    $table->foreignId('license_id')->constrained()->onDelete('cascade');
    $table->timestamps();

    $table->unique(['competition_id', 'license_id']); // 重複登録を防ぐ
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_license');
    }
};

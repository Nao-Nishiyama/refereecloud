<?php

// database/migrations/2025_10_03_000100_create_competition_applications_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('competition_applications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $t->foreignId('nomination_id')->constrained('nominations')->cascadeOnDelete(); // セル（official×day）
            $t->foreignId('referee_id')->constrained('referees')->cascadeOnDelete();       // 申込者
            $t->enum('status', ['applied','withdrawn','accepted','rejected'])->default('applied');
            $t->json('meta_json')->nullable(); // メモ等
            $t->timestamps();

            $t->unique(['nomination_id','referee_id'], 'uniq_nomination_referee_app'); // 同じセルに重複申込禁止
            $t->index(['competition_id','referee_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('competition_applications');
    }
};

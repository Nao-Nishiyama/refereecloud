<?php

// database/migrations/2025_10_03_000003_create_nomination_referee_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nomination_referee', function (Blueprint $t) {
            $t->id(); // 履歴を取りやすくするためPK付与（無くても可）

            $t->foreignId('nomination_id')->constrained('nominations')->cascadeOnDelete();
            $t->foreignId('referee_id')->constrained()->cascadeOnDelete();

            // 招待/応募/割当などの状態（自由に拡張）
            $t->enum('status', ['invited','applied','assigned','declined','cancelled'])
              ->default('invited');

            // 任意のメタ（希望ポジション等）
            $t->json('meta_json')->nullable();

            $t->timestamps();

            // 同じセルに同じレフェリーは一度だけ
            $t->unique(['nomination_id', 'referee_id'], 'uniq_nom_referee');

            // よく使う検索
            $t->index('referee_id');
            $t->index('nomination_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomination_referee');
    }
};

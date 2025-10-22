<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nominations', function (Blueprint $t) {
            $t->id();

            // セルのキー（大会×日×役職）
            $t->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $t->foreignId('day_id')->constrained('competition_days')->cascadeOnDelete();
            $t->foreignId('official_id')->constrained()->cascadeOnDelete();

            // 募集枠（任意）：必要人数や最大枠など
            $t->unsignedInteger('capacity')->nullable();

            // 募集ルール（任意）：license/categoryのID配列などを保持
            // 例: {"license_ids":[1,3], "category_ids":[2,8]}
            $t->json('filters_json')->nullable();

            // 任意メモ
            $t->string('note', 255)->nullable();

            $t->timestamps();

            // 同一セルは一意
            $t->unique(['competition_id', 'day_id', 'official_id'], 'uniq_nom_cell');

            // よく使う並び（検索・JOIN最適化）
            $t->index(['competition_id', 'day_id', 'official_id'], 'idx_nom_cell');

            // 将来の問い合わせで official 起点に見るなら
            $t->index(['official_id', 'day_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nominations');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('applications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('nomination_id')->constrained()->cascadeOnDelete();

            // ここを「挿入」
            $t->string('status', 32)->default('applied'); // 文字列ステータス

            $t->timestamps();

            // 重複応募防止
            $t->unique(['user_id','nomination_id']);
            $t->index('nomination_id');
        });

        // （任意）MySQL 8+ なら CHECK 制約を追加
        // 古いMySQLやMariaDBではスキップしてください
        try {
            DB::statement("
                ALTER TABLE applications
                ADD CONSTRAINT chk_app_status
                CHECK (status IN (
                    'applied','under_review','waitlisted','invited','accepted',
                    'declined','assigned','withdrawn','rejected','expired','canceled_by_org'
                ))
            ");
        } catch (\Throwable $e) {
            // 環境が未対応でも落ちないように握りつぶす
        }
    }

    public function down(): void {
        // CHECK 制約を付けた場合は先に落とす（DB方言により不要なら削除）
        try { DB::statement("ALTER TABLE applications DROP CONSTRAINT chk_app_status"); } catch (\Throwable $e) {}

        Schema::dropIfExists('applications');
    }
};
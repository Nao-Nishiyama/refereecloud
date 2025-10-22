<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('competitions', function (Blueprint $t) {
            $t->softDeletes(); // deleted_at
        });
        Schema::table('competition_days', function (Blueprint $t) {
            $t->softDeletes();
        });
        Schema::table('nominations', function (Blueprint $t) {
            $t->softDeletes();
        });
        Schema::table('nomination_capacities', function (Blueprint $t) {
            $t->softDeletes();
        });
        // 必要に応じて他の子テーブルも
    }
    public function down(): void {
        Schema::table('nomination_capacities', fn(Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('nominations', fn(Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('competition_days', fn(Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('competitions', fn(Blueprint $t) => $t->dropSoftDeletes());
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referees', function (Blueprint $table) {

            // 性別
            $table->string('gender', 10)
                ->nullable()
                ->after('birth_date');

            // 初回登録年
            $table->unsignedSmallInteger('registered_year')
                ->nullable()
                ->after('gender');

        });
    }

    public function down(): void
    {
        Schema::table('referees', function (Blueprint $table) {

            $table->dropColumn([
                'gender',
                'registered_year',
            ]);

        });
    }
};
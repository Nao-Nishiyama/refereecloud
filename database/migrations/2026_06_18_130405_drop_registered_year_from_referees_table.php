<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referees', function (Blueprint $table) {
            $table->dropColumn('registered_year');
        });
    }

    public function down(): void
    {
        Schema::table('referees', function (Blueprint $table) {
            $table->unsignedSmallInteger('registered_year')
                ->nullable()
                ->after('gender');
        });
    }
};
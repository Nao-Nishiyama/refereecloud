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
        Schema::table('referees', function (Blueprint $table) {
            $table->string('email')
                ->nullable()
                ->after('registration_number');
        });
    }

    public function down(): void
    {
        Schema::table('referees', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }

};

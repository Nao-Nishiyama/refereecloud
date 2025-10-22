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
        Schema::create('annual_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->year('year', 4);
            $table->integer('first_ref_block')->nullable();
            $table->integer('second_ref_block')->nullable();
            $table->integer('first_ref');
            $table->integer('second_ref');
            $table->integer('scorer');
            $table->integer('assistant_scorer');
            $table->integer('linejudge');
            $table->integer('training');
            $table->timestamps();
            $table->unique(['user_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annual_reports');
        Schema::table('annual_reports', function (Blueprint $table) {
            $table->dropColumn([
                'first_ref_block',
                'second_ref_block',
                'first_ref',
                'second_ref',
                'scorer',
                'assistant_scorer',
                'linejudge',
                'training',
            ]);
            $table->dropUnique(['user_id', 'year']);
        });

    }
};

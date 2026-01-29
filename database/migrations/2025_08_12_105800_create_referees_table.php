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
        Schema::create('referees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            $table->string('surname',50)->nullable();
            $table->string('name',50)->nullable();
            $table->string('surname_kanji', 50)->nullable();
            $table->string('name_kanji', 50)->nullable();
            $table->string('surname_kana', 50)->nullable();
            $table->string('name_kana', 50)->nullable();
            $table->string('registration_number', 50)->unique()->nullable();
            $table->foreignId('prefecture_id')->nullable()
                ->constrained('prefectures')->nullOnDelete();
            $table->foreignId('organization_id')->nullable()
                ->constrained('organizations')->nullOnDelete();
            $table->foreignId('license_id')->nullable()
                ->constrained('licenses')->nullOnDelete();
            $table->date('birth_date')->nullable();
            $table->string('mrs_member_id',9)->nullable()->unique();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['surname_kanji','name_kanji'], 'uniq_ref_kanji_set');
            $table->unique(['surname_kana','name_kana'],   'uniq_ref_kana_set');
            $table->unique(['surname','name'],             'uniq_ref_roman_set');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referees');
    }
};

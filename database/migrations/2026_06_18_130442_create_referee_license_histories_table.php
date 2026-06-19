<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referee_license_histories', function (Blueprint $table) {

            $table->id();

            $table->foreignId('referee_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('license_id')
                ->constrained()
                ->restrictOnDelete();

            // 取得年
            $table->unsignedSmallInteger('acquired_year');

            // 備考
            $table->string('note')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referee_license_histories');
    }
};
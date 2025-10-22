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
        Schema::create('referee_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referee_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('year');                       // 対象年度
            $table->tinyInteger('approved')->default(0);        // 1:true, 0:false
            $table->tinyInteger('suspended')->default(0);       // 1:true, 0:false
            $table->timestamps();

            $table->unique(['referee_id', 'year']);
            $table->index(['year', 'suspended']);
            $table->unique('referee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referee_approvals');
    }
};

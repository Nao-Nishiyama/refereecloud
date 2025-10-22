<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('nomination_capacities', function (Blueprint $t) {
            $t->id();
            $t->foreignId('nomination_id')->constrained()->cascadeOnDelete();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('capacity'); // 例: 団体A=2, 団体B=3
            $t->unique(['nomination_id','organization_id']);
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('nomination_capacities');
    }
};

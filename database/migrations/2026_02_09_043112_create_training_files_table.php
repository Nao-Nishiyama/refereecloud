<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('training_files', function (Blueprint $t) {
            $t->id();
            $t->foreignId('training_id')->constrained('trainings')->cascadeOnDelete();
            $t->string('path');                 // storageの相対パス（例 trainings/xxx.pdf）
            $t->string('original_name')->nullable();
            $t->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index('training_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_files');
    }
};

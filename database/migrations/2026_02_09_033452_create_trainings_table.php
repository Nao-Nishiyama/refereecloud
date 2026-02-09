<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('trainings', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->date('event_date')->nullable();
            $t->string('venue')->nullable();
            $t->foreignId('prefecture_id')
                ->nullable()
                ->constrained('prefectures')
                ->nullOnDelete();
            $t->foreignId('organization_id')
                ->nullable()
                ->constrained('organizations')
                ->nullOnDelete();
            $t->text('summary')->nullable();
            $t->longText('body')->nullable();
            $t->date('deadline')->nullable();
            $t->boolean('is_published')->default(true);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('trainings');
    }
};

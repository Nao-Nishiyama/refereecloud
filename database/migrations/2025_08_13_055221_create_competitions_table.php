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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('name',50);
            $table->foreignId('type_id',50)->constrained()->onDelete('cascade');
            $table->string('city',50);
            $table->string('venue',50);
            $table->date('start_day');
            $table->date('end_day');
            $table->date('application_deadline');
            $table->text('organizer_message')->nullable(); // 管理者→ユーザー公開メッセージ
            $table->text('admin_private_note')->nullable(); // 管理者だけの内部メモ
            $table->timestamps();

            $table->unique(['name', 'start_day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};

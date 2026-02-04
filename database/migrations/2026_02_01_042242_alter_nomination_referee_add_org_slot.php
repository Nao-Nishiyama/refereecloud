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
        Schema::table('nomination_referee', function (Blueprint $table) {
            // どの団体枠の割当か（これが無いと上書き事故が起きる）
            $table->foreignId('organization_id')
              ->nullable()
              ->after('referee_id')
              ->constrained()
              ->cascadeOnDelete();

            // 枠順（1枠目/2枠目...） ※将来のため推奨
            $table->unsignedTinyInteger('slot_no')
              ->nullable()
              ->after('organization_id');

            $table->index(['nomination_id', 'organization_id', 'status'], 'idx_nom_org_status');
            $table->index(['nomination_id', 'organization_id', 'slot_no'], 'idx_nom_org_slot');
        });


        // 同じ nomination・同じ団体・同じ枠に複数人入らないようにする
        Schema::table('nomination_referee', function (Blueprint $t) {
            $t->unique(['nomination_id', 'organization_id', 'slot_no'], 'uniq_nom_org_slot');
        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::table('nomination_referee', function (Blueprint $t) {
            $t->dropUnique('uniq_nom_org_slot');
            $t->dropIndex('idx_nom_org_status');
            $t->dropIndex('idx_nom_org_slot');
            $t->dropConstrainedForeignId('organization_id');
            $t->dropColumn('slot_no');
        });
    }
};

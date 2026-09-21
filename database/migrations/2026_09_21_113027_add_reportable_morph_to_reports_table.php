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
        Schema::table('reports', function (Blueprint $table) {
            // Denúncia de conteúdo (post, enquete, comentário) em vez de
            // usuário/mensagem diretos — nullable, aditivo, não quebra as
            // denúncias de usuário/mensagem já suportadas.
            $table->nullableMorphs('reportable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropMorphs('reportable');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aprovados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('importacao_id')->constrained('importacoes')->onDelete('cascade');
            $table->string('nome');
            $table->string('cpf_mascarado')->nullable();
            $table->string('instituicao')->nullable()->index();
            $table->string('curso')->nullable();
            $table->string('modalidade')->nullable();
            
            $table->string('nome_normalizado')->index();
            $table->string('hash_primeiro_nome')->nullable()->index();
            $table->string('hash_ultimo_sobrenome')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aprovados');
    }
};

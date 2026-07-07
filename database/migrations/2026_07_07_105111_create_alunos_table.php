<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alunos', function (Blueprint $table) {
            $table->id();
            $table->string('id_aluno')->unique();
            $table->string('nome_completo');
            $table->string('cpf')->unique();
            $table->string('unidade')->nullable();
            $table->string('turma')->nullable();
            $table->string('ano_matricula')->nullable();
            
            $table->string('nome_normalizado')->index();
            $table->string('hash_primeiro_nome')->nullable()->index();
            $table->string('hash_ultimo_sobrenome')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alunos');
    }
};

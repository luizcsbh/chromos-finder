<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('importacoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome_arquivo');
            $table->string('tipo'); // base ou aprovados
            $table->integer('total_linhas')->default(0);
            $table->integer('linhas_processadas')->default(0);
            $table->string('status')->default('pendente'); // pendente, processando, concluido, erro
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('importacoes');
    }
};

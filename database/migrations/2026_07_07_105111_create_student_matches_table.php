<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->nullable()->constrained('alunos')->onDelete('cascade');
            $table->foreignId('aprovado_id')->constrained('aprovados')->onDelete('cascade');
            $table->decimal('score', 5, 2);
            $table->string('confianca');
            $table->string('algoritmo');
            $table->string('status')->index(); // MATCH_EXATO, MATCH_PROVAVEL, AMBIGUO, SEM_CORRESPONDENCIA, REVISAO_MANUAL
            $table->text('justificativa')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_matches');
    }
};

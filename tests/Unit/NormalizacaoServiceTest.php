<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\NormalizacaoService;

class NormalizacaoServiceTest extends TestCase
{
    public function test_normalizacao_remove_acentos_e_pontuacao()
    {
        $service = new NormalizacaoService();
        
        $this->assertEquals(
            'JOAO PEDRO SILVA', 
            $service->normalizar('João Pedro da Silva')
        );

        $this->assertEquals(
            'JOAO PEDRO D SILVA', 
            $service->normalizar('João Pedrò D\'Silva.')
        );

        $this->assertEquals(
            'MARIA EDUARDA SANTOS', 
            $service->normalizar('Maria Eduarda dos Santos!')
        );
    }
}

<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\ComparacaoService;

class ComparacaoServiceTest extends TestCase
{
    public function test_calculo_score_identico()
    {
        $service = new ComparacaoService();
        
        $score = $service->calcularScore('JOAO SILVA', 'JOAO SILVA', 'Joao Silva', 'Joao Silva');
        $this->assertEquals(100.0, $score);
    }

    public function test_calculo_score_alta_similaridade()
    {
        $service = new ComparacaoService();
        
        // JOAO vs JOÃO (normalizado fica igual, SCORE 99)
        $score = $service->calcularScore('JOAO SILVA', 'JOAO SILVA', 'Joao Silva', 'João Silva');
        $this->assertEquals(99.0, $score);
    }

    public function test_calculo_score_erro_digitacao()
    {
        $service = new ComparacaoService();
        
        $score = $service->calcularScore('JOAO PEDOR SILVA', 'JOAO PEDRO SILVA');
        $this->assertGreaterThan(90.0, $score);
    }
}

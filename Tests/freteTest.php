<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

set_include_path(dirname(__FILE__)
                    .PATH_SEPARATOR.dirname(dirname(__FILE__))
                    .PATH_SEPARATOR.get_include_path()
                );

// Para usar os testes, voce deve ter a biblioteca PEAR PHPUnit
require_once 'config.php';
require_once 'Pagseguro.php';
require_once 'PHPUnit/Framework.php';

class FreteTest extends PHPUnit_Framework_TestCase
{
    public function testInstancia()
    {
        $frete = Pagseguro::frete();
        $this->assertEquals('Pagseguro_Frete', get_class($frete));
    }

    public function testSource()
    {
        $frete = Pagseguro::frete();
        $this->assertEquals(array('pagseguro', 'correios'), $frete->source);
        $frete->source('pagseguro');
        $this->assertEquals(array('pagseguro'), $frete->source, 'setou pagseguro');
        $frete->source('correios');
        $this->assertEquals(array('correios'), $frete->source, 'setou correios');
        $frete->source('correios', 'pagseguro');
        $this->assertEquals(array('correios', 'pagseguro'), $frete->source, 'setou correios, pagseguro');
        $frete->source(array('pagseguro', 'correios'));
        $this->assertEquals(array('pagseguro', 'correios'), $frete->source, 'setou pagseguro, correios');
    }

    public function filterSource()
    {
        $frete = Pagseguro::frete();
        $this->assertEquals(array('pagseguro', 'correios'), $frete->source);
        $frete->source('qualqueroutro');
        $this->assertEquals(array(), $frete->source);
        $frete->source('qualqueroutro', 'maisum');
        $this->assertEquals(array(), $frete->source);
        $frete->source(array('qualqueroutro', 'maisum'));
        $this->assertEquals(array(), $frete->source);
        $frete->source('pagseguro', 'pagseguro');
        $this->assertEquals(array('pagseguro'), $frete->source);
        $frete->source('pagseguro', 'correios', 'pagseguro');
        $this->assertEquals(array('pagseguro', 'correios'), $frete->source);
        $frete->source('coRreios', array('correios'), array('outro', 'pagseguro'), 'correios');
        $this->assertEquals(array('correios', 'pagseguro'), $frete->source);
    }

    public function validOrigens()
    {
        return array(
            array('12345678', '12345678'),
            array('12345-678', '12345678'),
            array('12345.678', '12345678'),
            array('01234 678', '01234678'),
            array('12345L678', '12345678'),
            array(12345678, '12345678'),
        );
    }

    /**
     * @dataProvider validOrigens
     */
    public function testOrigem($origem, $espected)
    {
        $frete = Pagseguro::frete();
        $this->assertEquals('', $frete->origem);
        $frete->origem($origem);
        $this->assertEquals($espected, $frete->origem);
    }

    /**
     * @dataProvider validOrigens
     */
    public function testDestino($destino, $espected)
    {
        $frete = Pagseguro::frete();
        $this->assertEquals('', $frete->destino);
        $frete->destino($destino);
        $this->assertEquals($espected, $frete->destino);
    }

    public function invalidOrigens()
    {
        return array(
            array(new stdClass()),
            array(array()),
            array(array('12345678')),
            array('1234'),
            array('123456789'),
            array('        '),
            array('Apenas letras'),
            array(new SimpleXMLElement('<data><origem>12345678</origem></data>')),
        );
    }

    /**
     * @dataProvider invalidOrigens
     * @expectedException Exception
     */
    public function testInvalidDataForOrigem($origem)
    {
        $frete = new Pagseguro_Frete;
        $frete->origem($origem);
    }

    /**
     * @dataProvider invalidOrigens
     * @expectedException Exception
     */
    public function testInvalidDataForDestino($destino)
    {
        $frete = new Pagseguro_Frete;
        $frete->destino($destino);
    }

    public function testMontaURLCorreios()
    {
        $frete    = new Pagseguro_Frete;
        $frete->origem  = '02021030';
        $frete->destino = '28030120';
        $frete->peso    = '0.300';

        $url      = $frete->montaURLCorreios('pac');
        $espected = 'http://shopping.correios.com.br/wbm/shopping/script/CalcPrecoPrazo.aspx?'
                  . 'StrRetorno=xml'
                  . '&nCdServico=41106'
                  . '&nVlPeso=0%2C300' // Escapamento de URL
                  . '&sCepOrigem=02021030'
                  . '&sCepDestino=28030120'
                  . '&nCdFormato=1'
                  . '&sCdMaoPropria=N'
                  . '&sCdAvisoRecebimento=N'
                  . '&nVlValorDeclarado=0';
        $this->assertEquals($url, $espected);
    }

    public function testParseCorreios()
    {
        $frete = new Pagseguro_Frete;
        $data = $frete->parseCorreios('<data><teste>1</teste><valor>2</valor></data>');
        $this->assertEquals($data, array('teste'=>1, 'valor'=>2));
    }
}

// Fazendo o sistema rodar sozinho
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    rodaTest('FreteTest');
}
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
define ('TOKEN', 'APENASPARATESTE');

global $file;
$file = dirname(__FILE__).DIRECTORY_SEPARATOR.'retortou';

function retorna($string='vazio')
{
	global $file;
	file_put_contents($file, var_export($string, true));
}

function retorna_cliente_nome($data) {
    retorna($data->cliente->nome);
}

function retorna_total($data) {
    retorna($data->total);
}

class RetornoTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		global $file;
        if (file_exists($file)) {
        	unlink($file);
        }
    }

    public function tearDown()
    {
        $this->setUp();
    }

	public function testInstance()
	{
        $retorno = Pagseguro::Retorno('retorna');
        $this->assertEquals($retorno->token, 'APENASPARATESTE');
        $this->assertEquals($retorno->funcao, 'retorna');
        $this->assertEquals($retorno->url, 'https://pagseguro.uol.com.br/pagseguro-ws/checkout/NPI.jhtml');
    }

	public function testFormataValores()
	{
        $retorno = Pagseguro::Retorno('retorna');
        $data = $retorno->prepara(array('email_cobranca' => 'mike@visie.com.br'));
        $this->assertEquals($data, array('email_cobranca' => 'mike@visie.com.br', 'Token' => 'APENASPARATESTE', 'Comando' => 'validar'));
    }

    public function testCurlIt()
    {
        $retorno = Pagseguro::Retorno('retorna');
        $retorno->url = 'http://localhost/GETPOST.php';
        $data = $retorno->go();
        $this->assertEquals('GET', $data);
        $data = $retorno->go(array('nome' => 'Michael'));
        $this->assertEquals('POST', $data);
    }

    public function testRun()
    {
    	global $file;
        $retorno = Pagseguro::Retorno('retorna');
        $retorno->url = 'http://localhost/GETPOST.php?msg=FALSO';
        $retorno->run();
        $this->assertFalse(file_exists($file), 'retorno FALSO');

        $retorno->url = 'http://localhost/GETPOST.php?msg=VERIFICADO';
        $retorno->run();
        $this->assertTrue(file_exists($file), 'retorno VERIFICADO');
    }

    public function testNomeCliente()
    {
    	global $file;
    	$_POST = array('CliNome' => 'Michael Granados');

        $retorno = Pagseguro::Retorno('retorna_cliente_nome');
        $retorno->url = 'http://localhost/GETPOST.php?msg=FALSO';
        $retorno->url = 'http://localhost/GETPOST.php?msg=VERIFICADO';
        $retorno->run();
        $this->assertEquals(file_get_contents($file), "'Michael Granados'");
    }

    public function testTotal()
    {
    	global $file;
    	$_POST = array(
    	    'CliNome' => 'Michael Granados',
    	    'ProdID_1' => '1',
    	    'ProdDescricao_1' => 'Imagem Amarela',
    	    'ProdValor_1' => '20,05',
    	    'ProdQuantidade_1' => '1',

    	    'ProdID_2' => '2',
    	    'ProdDescricao_2' => 'Quantidade ao extremo',
    	    'ProdValor_2' => '5,55',
    	    'ProdQuantidade_2' => '1',
    	);

        $retorno = Pagseguro::Retorno('retorna_total');
        $retorno->url = 'http://localhost/GETPOST.php?msg=FALSO';
        $retorno->url = 'http://localhost/GETPOST.php?msg=VERIFICADO';
        $retorno->run();
        $this->assertEquals(file_get_contents($file), "25.6");
    }
}

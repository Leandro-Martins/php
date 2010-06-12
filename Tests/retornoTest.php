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
}

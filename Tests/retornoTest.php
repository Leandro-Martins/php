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
    }
}

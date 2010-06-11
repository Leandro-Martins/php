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

class PagseguroTest extends PHPUnit_Framework_TestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists('Pagseguro'));
    }

    public function testGetValidProprieties()
    {
        $p = new Pagseguro;
        foreach (array('carrinho', 'frete', 'retorno', 'doacao') as $item) {
            $saida = $p->$item;
            $this->assertTrue((bool) $saida);
            $this->assertEquals('Pagseguro_'.ucfirst($item), get_class($saida));
        }
    }

    public function testGetInvalidProprieties()
    {
        $p = new Pagseguro;
        $this->setExpectedException('Exception');
        $random = $p->invalidAttribute;
    }

    public function testCallValidStaticMethods()
    {
        foreach (array('carrinho', 'frete', 'retorno', 'doacao') as $item) {
            $element = PagSeguro::$item();
            $this->assertTrue((bool) $element);
            $this->assertEquals('Pagseguro_'.ucfirst($item), get_class($element));

            $element = PagSeguro::getModule($item);
            $this->assertTrue((bool) $element);
            $this->assertEquals('Pagseguro_'.ucfirst($item), get_class($element));
        }
    }

    public function testCallWithArguments()
    {
        $carrinho = PagSeguro::carrinho('mike@visie.com.br');
        $this->assertEquals('mike@visie.com.br', $carrinho->email_cobranca, "Passou o valor para o e-mail no construct");
        $carrinho = PagSeguro::getModule('carrinho', 'michael@uol.com.br');
        $this->assertEquals('michael@uol.com.br', $carrinho->email_cobranca, "Usou getModule e passou e-mail de cobranca");
    }
}

// Fazendo o sistema rodar sozinho
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    rodaTest('PagseguroTest');
}

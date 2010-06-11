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

class DoacaoTest extends PHPUnit_Framework_TestCase
{
    public function testSimples()
    {
        $doacao  = Pagseguro::Doacao(array('email_cobranca' => 'mike@visie.com.br', 'print' => false));
        $content = $doacao->mostra();

        $expected = '<form action="https://pagseguro.uol.com.br/checkout/doacao.jhtml" method="post" target="pagseguro"><input type="hidden" name="moeda" value="BRL" /><input type="hidden" name="email_cobranca" value="mike@visie.com.br" /><input type="submit" value="Finalizar!" /></form>';
        $this->assertEquals($expected, $content);

    }
}
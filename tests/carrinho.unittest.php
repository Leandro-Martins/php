<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

set_include_path(dirname(__FILE__)
                    .PATH_SEPARATOR.dirname(dirname(__FILE__))
                    .PATH_SEPARATOR.get_include_path()
                );

// Para usar os testes, voce deve ter a biblioteca PEAR PHPUnit
require_once 'config.php';
require_once 'Pagseguro.php';
require_once 'Carrinho.php';
require_once 'PHPUnit/Framework.php';

class Carrinho_Test extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $carrinho = new Pagseguro_Carrinho;
        $this->assertEquals('Pagseguro_Carrinho', get_class($carrinho), 'Instanciou o carrinho puro');
        $carrinho = Pagseguro::carrinho();
        $this->assertEquals('Pagseguro_Carrinho', get_class($carrinho), 'Instanciou o carrinho pelo metodo estatico');
        $pagseguro = new Pagseguro;
        $carrinho = $pagseguro->carrinho();
        $this->assertEquals('Pagseguro_Carrinho', get_class($carrinho), 'Instanciou o carrinho pelo metodo publico');
        $carrinho = $pagseguro->carrinho;
        $this->assertEquals('Pagseguro_Carrinho', get_class($carrinho), 'Instanciou o carrinho pelo __get');
        $carrinho = $pagseguro->getModule('carrinho');
        $this->assertEquals('Pagseguro_Carrinho', get_class($carrinho), 'Instanciou o carrinho pelo getModule');
    }

    public function testCallWithArgument()
    {
        $email    = 'mike@visie.com.br';
        $carrinho = new Pagseguro_Carrinho($email);
        $this->assertEquals('Pagseguro_Carrinho', get_class($carrinho), 'Instanciou o carrinho puro');
        $this->assertEquals($email, $carrinho->email_cobranca);

        $carrinho = Pagseguro::carrinho($email);
        $this->assertEquals('Pagseguro_Carrinho', get_class($carrinho), 'Instanciou o carrinho pelo metodo estatico');
        $this->assertEquals($email, $carrinho->email_cobranca);

        $pagseguro = new Pagseguro;
        $carrinho  = $pagseguro->carrinho($email);
        $this->assertEquals('Pagseguro_Carrinho', get_class($carrinho), 'Instanciou o carrinho pelo metodo publico');
        $this->assertEquals($email, $carrinho->email_cobranca);

        $carrinho = $pagseguro->getModule('carrinho', $email);
        $this->assertEquals('Pagseguro_Carrinho', get_class($carrinho), 'Instanciou o carrinho pelo getModule');
        $this->assertEquals($email, $carrinho->email_cobranca);
    }
}

// Fazendo o sistema rodar sozinho
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    rodaTest('Carrinho_Test');
}

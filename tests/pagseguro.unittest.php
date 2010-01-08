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

class Pagseguro_Test extends PHPUnit_Framework_TestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists('Pagseguro'));
    }
}

// Fazendo o sistema rodar sozinho
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    rodaTest('Pagseguro_Test');
}

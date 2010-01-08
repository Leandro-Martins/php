<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

$path = realpath('..');
set_include_path(get_include_path().PATH_SEPARATOR.$path);

// Para usar os testes, voce deve ter a biblioteca PEAR PHPUnit
require_once 'Pagseguro.php';
require_once 'PHPUnit/Framework.php';

class Pagseguro_Test extends PHPUnit_Framework_TestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists('Pagseguro'));
    }
}

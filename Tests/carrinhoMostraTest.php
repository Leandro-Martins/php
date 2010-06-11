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

class CarrinhoMostraTest extends PHPUnit_Framework_TestCase
{
    public function testMostraCarrinhoSimples()
    {
        $carrinho = Pagseguro::Carrinho('mike@visie.com.br');
        $carrinho->produto(array('id' => '1', 'desc' => 'Carrinho', 'valor' => 24.7, 'quantidade' => 2));

        ob_start();
        $carrinho->mostra();
        $content = ob_get_contents();
        ob_end_clean();
        $saida = '<form action="https://pagseguro.uol.com.br/checkout/checkout.jhtml" id="form_pagseguro" method="post" target="pagseguro"><input type="hidden" name="tipo" value="CP" /><input type="hidden" name="moeda" value="BRL" /><input type="hidden" name="email_cobranca" value="mike@visie.com.br" /><input type="hidden" name="item_id_1" value="1" /><input type="hidden" name="item_descr_1" value="Carrinho" /><input type="hidden" name="item_valor_1" value="2470" /><input type="hidden" name="item_quant_1" value="2" /><input type="submit" value="Finalizar!" /></form>';
        $this->assertEquals($content, $saida);
    }

    public function testMostraCarrinhoPagSeguro()
    {
        $carrinho = Pagseguro::Carrinho(array(
            'email_cobranca' => 'fake@visie.com.br',
            'tipo' => 'CBR',
            'id_formulario' => false,
            'target' => '_blank'
        ));
        $carrinho->produto(array('id' => '1', 'desc' => 'Carrinho', 'valor' => 24.7, 'quantidade' => 2));

        ob_start();
        $carrinho->mostra();
        $content = ob_get_contents();
        ob_end_clean();
        $expected = '<form action="https://pagseguro.uol.com.br/checkout/checkout.jhtml" method="post" target="_blank"><input type="hidden" name="tipo" value="CBR" /><input type="hidden" name="moeda" value="BRL" /><input type="hidden" name="email_cobranca" value="fake@visie.com.br" /><input type="hidden" name="item_id_1" value="1" /><input type="hidden" name="item_descr_1" value="Carrinho" /><input type="hidden" name="item_valor_1" value="2470" /><input type="hidden" name="item_quant_1" value="2" /><input type="submit" value="Finalizar!" /></form>';
        $this->assertEquals($expected, $content);
    }

    public function testMostraCarrinhoPagSeguroMaisDeUmProduto()
    {
        $carrinho = Pagseguro::Carrinho(array(
            'email_cobranca' => 'fake@visie.com.br',
            'tipo' => 'CBR',
            'id_formulario' => false,
            'target' => '_blank'
        ));
        $carrinho->produto(array('id' => '1', 'desc' => 'Carrinho', 'valor' => 24.7, 'quantidade' => 2));
        $carrinho->produto(array('id' => '2', 'desc' => 'Boneca', 'valor' => 35, 'quantidade' => 1));

        ob_start();
        $carrinho->mostra();
        $content = ob_get_contents();
        ob_end_clean();
        $expected = '<form action="https://pagseguro.uol.com.br/checkout/checkout.jhtml" method="post" target="_blank"><input type="hidden" name="tipo" value="CBR" /><input type="hidden" name="moeda" value="BRL" /><input type="hidden" name="email_cobranca" value="fake@visie.com.br" />'
        . '<input type="hidden" name="item_id_1" value="1" /><input type="hidden" name="item_descr_1" value="Carrinho" /><input type="hidden" name="item_valor_1" value="2470" /><input type="hidden" name="item_quant_1" value="2" />'
        . '<input type="submit" value="Finalizar!" /></form>';
        $this->assertEquals($expected, $content);
    }
}


// Fazendo o sistema rodar sozinho
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    rodaTest('CarrinhoTest');
}

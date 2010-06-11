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
    public $basic_exit = '<form action="https://pagseguro.uol.com.br/checkout/checkout.jhtml" id="form_pagseguro" method="post" target="pagseguro"><input type="hidden" name="tipo" value="CP" /><input type="hidden" name="moeda" value="BRL" /><input type="hidden" name="email_cobranca" value="mike@visie.com.br" />';

    public function mostra($settings, $produtos)
    {
        $carrinho = Pagseguro::Carrinho($settings);
        $carrinho->produto($produtos);

        ob_start();
        $carrinho->mostra();
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
    public function testMostraCarrinhoSimples()
    {
        $content = $this->mostra('mike@visie.com.br',
                        array('id' => '1', 'desc' => 'Carrinho', 'valor' => 24.7, 'quantidade' => 2));

        $saida = $this->basic_exit.'<input type="hidden" name="item_id_1" value="1" /><input type="hidden" name="item_descr_1" value="Carrinho" /><input type="hidden" name="item_valor_1" value="2470" /><input type="hidden" name="item_quant_1" value="2" /><input type="submit" value="Finalizar!" /></form>';
        $this->assertEquals($content, $saida);
    }

    public function testMostraCarrinhoPagSeguro()
    {
        $content = $this->mostra(array(
            'email_cobranca' => 'fake@visie.com.br',
            'tipo' => 'CBR',
            'id_formulario' => false,
            'target' => '_blank'
        ), array('id' => '1', 'desc' => 'Carrinho', 'valor' => 24.7, 'quantidade' => 2));

        $expected = '<form action="https://pagseguro.uol.com.br/checkout/checkout.jhtml" method="post" target="_blank"><input type="hidden" name="tipo" value="CBR" /><input type="hidden" name="moeda" value="BRL" /><input type="hidden" name="email_cobranca" value="fake@visie.com.br" /><input type="hidden" name="item_id_1" value="1" /><input type="hidden" name="item_descr_1" value="Carrinho" /><input type="hidden" name="item_valor_1" value="2470" /><input type="hidden" name="item_quant_1" value="2" /><input type="submit" value="Finalizar!" /></form>';
        $this->assertEquals($expected, $content);
    }

    public function testMostraCarrinhoPagSeguroMaisDeUmProduto()
    {
        $content = $this->mostra(array(
            'email_cobranca' => 'fake@visie.com.br',
            'tipo' => 'CBR',
            'id_formulario' => false,
            'target' => '_blank'
        ), array(
            array('id' => '1', 'desc' => 'Carrinho', 'valor' => 24.7, 'quantidade' => 2),
            array('id' => '2', 'desc' => 'Boneca', 'valor' => 35, 'quantidade' => 1)
        ));

        $expected = '<form action="https://pagseguro.uol.com.br/checkout/checkout.jhtml" method="post" target="_blank"><input type="hidden" name="tipo" value="CBR" /><input type="hidden" name="moeda" value="BRL" /><input type="hidden" name="email_cobranca" value="fake@visie.com.br" />'
        . '<input type="hidden" name="item_id_1" value="1" /><input type="hidden" name="item_descr_1" value="Carrinho" /><input type="hidden" name="item_valor_1" value="2470" /><input type="hidden" name="item_quant_1" value="2" />'
        . '<input type="submit" value="Finalizar!" /></form>';
        $this->assertEquals($expected, $content);
    }

    public function testMostraProdutosFretePeso()
    {
        $content = $this->mostra('mike@visie.com.br', array('id'=>'AREA', 'desc'=>'Area 51', 'qtd'=>5, 'valor'=>10, 'peso'=>170, 'frete' => 20));

        $expected = $this->basic_exit
        . '<input type="hidden" name="item_id_1" value="AREA" /><input type="hidden" name="item_descr_1" value="Area 51" /><input type="hidden" name="item_valor_1" value="1000" /><input type="hidden" name="item_quant_1" value="5" /><input type="hidden" name="item_frete_1" value="2000" /><input type="hidden" name="item_peso_1" value="170" />'
        . '<input type="submit" value="Finalizar!" /></form>';
        $this->assertEquals($expected, $content);
    }

    public function testMostraFretePesoUnico()
    {
        $content = $this->mostra(array('email_cobranca' => 'mike@visie.com.br', 'frete' => 30, 'peso' => 2000), array(
            array('id' => '1', 'desc' => 'Carrinho', 'valor' => 24.7, 'quantidade' => 2, 'frete'=>10),
            array('id' => '2', 'desc' => 'Boneca', 'valor' => 35, 'quantidade' => 1, 'frete'=>50, 'peso' => 1000)
        ));
        $expected = $this->basic_exit
        . '<input type="hidden" name="item_frete_1" value="3000" />'
        . '<input type="hidden" name="item_peso_1" value="2000" />'
        . '<input type="hidden" name="item_id_1" value="1" /><input type="hidden" name="item_descr_1" value="Carrinho" /><input type="hidden" name="item_valor_1" value="2470" /><input type="hidden" name="item_quant_1" value="2" />'
        . '<input type="hidden" name="item_id_2" value="2" /><input type="hidden" name="item_descr_2" value="Boneca" /><input type="hidden" name="item_valor_2" value="3500" /><input type="hidden" name="item_quant_2" value="1" />'
        . '<input type="submit" value="Finalizar!" /></form>';
        $this->assertEquals($expected, $content);
    }
}


// Fazendo o sistema rodar sozinho
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    rodaTest('CarrinhoTest');
}

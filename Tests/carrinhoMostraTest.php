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

    public $basic_product = '<input type="hidden" name="item_id_1" value="1" /><input type="hidden" name="item_descr_1" value="Carrinho" /><input type="hidden" name="item_valor_1" value="2470" /><input type="hidden" name="item_quant_1" value="2" />';

    public function mostra($settings, $produtos=null, $cliente=null)
    {
        $carrinho = Pagseguro::Carrinho($settings);
        if ($produtos) {
            $carrinho->produto($produtos);
        }
        $carrinho->cliente($cliente);

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

        $saida = $this->basic_exit.$this->basic_product.'<input type="submit" value="Finalizar!" /></form>';
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

        $expected = '<form action="https://pagseguro.uol.com.br/checkout/checkout.jhtml" method="post" target="_blank"><input type="hidden" name="tipo" value="CBR" /><input type="hidden" name="moeda" value="BRL" /><input type="hidden" name="email_cobranca" value="fake@visie.com.br" /><input type="hidden" name="item_id" value="1" /><input type="hidden" name="item_descr" value="Carrinho" /><input type="hidden" name="item_valor" value="2470" /><input type="hidden" name="item_quant" value="2" /><input type="submit" value="Finalizar!" /></form>';
        $this->assertEquals($expected, $content);
    }

    public function testMostraCarrinhoPagSeguroComPeso()
    {
        $content = $this->mostra(array(
            'email_cobranca' => 'fake@visie.com.br',
            'tipo' => 'CBR',
            'id_formulario' => false,
            'target' => '_blank'
        ), array('id' => '1', 'desc' => 'Carrinho', 'valor' => 24.7, 'quantidade' => 2, 'peso' => 200));

        $expected = '<form action="https://pagseguro.uol.com.br/checkout/checkout.jhtml" method="post" target="_blank"><input type="hidden" name="tipo" value="CBR" /><input type="hidden" name="moeda" value="BRL" /><input type="hidden" name="email_cobranca" value="fake@visie.com.br" /><input type="hidden" name="item_id" value="1" /><input type="hidden" name="item_descr" value="Carrinho" /><input type="hidden" name="item_valor" value="2470" /><input type="hidden" name="item_quant" value="2" /><input type="hidden" name="peso" value="200" /><input type="submit" value="Finalizar!" /></form>';
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
        . '<input type="hidden" name="item_id" value="1" /><input type="hidden" name="item_descr" value="Carrinho" /><input type="hidden" name="item_valor" value="2470" /><input type="hidden" name="item_quant" value="2" />'
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
        . $this->basic_product
        . '<input type="hidden" name="item_id_2" value="2" /><input type="hidden" name="item_descr_2" value="Boneca" /><input type="hidden" name="item_valor_2" value="3500" /><input type="hidden" name="item_quant_2" value="1" />'
        . '<input type="submit" value="Finalizar!" /></form>';
        $this->assertEquals($expected, $content);
    }

    public function testMostraCarrinhoCliente()
    {
        $content = $this->mostra(array('email_cobranca' => 'mike@visie.com.br', 'encoding' => 'UTF-8'),
                        array('id' => '1', 'desc' => 'Carrinho', 'valor' => 24.7, 'quantidade' => 2),
                        array('nome' => 'Michael Castillo', 'mail' => 'fake@visie.com.br')
            );
        $expected = $this->basic_exit.'<input type="hidden" name="encoding" value="UTF-8" />'.$this->basic_product
        . '<input type="hidden" name="cliente_nome" value="Michael Castillo" /><input type="hidden" name="cliente_email" value="fake@visie.com.br" />'
        . '<input type="submit" value="Finalizar!" /></form>';
        $this->assertEquals($expected, $content);
    }

    public function testDoacao()
    {
        // Precisa fazer este modulo urgente!
        $content = $this->mostra(array('email_cobranca' => 'mike@visie.com.br', 'tipo' => false, 'url' => 'https://pagseguro.uol.com.br/checkout/doacao.jhtml', 'id_formulario' => false));

        $expected = '<form action="https://pagseguro.uol.com.br/checkout/doacao.jhtml" method="post" target="pagseguro"><input type="hidden" name="moeda" value="BRL" /><input type="hidden" name="email_cobranca" value="mike@visie.com.br" /><input type="submit" value="Finalizar!" /></form>';
        $this->assertEquals($expected, $content);
    }

    public function testMostraBotao3()
    {
        $content = $this->mostra(array('email_cobranca' => 'mike@visie.com.br', 'button' => 3),
                        array('id' => '1', 'desc' => 'Carrinho', 'valor' => 24.7, 'quantidade' => 2));

        $saida = $this->basic_exit.$this->basic_product.'<input type="image" src="https://p.simg.uol.com.br/out/pagseguro/i/botoes/pagamento/btnPagarBR.jpg" name="submit" alt="Pague com PagSeguro - é rápido, grátis e seguro!" /></form>';
        $this->assertEquals($content, $saida);
    }

    public function testMostraBotaoHtml()
    {
        $content = $this->mostra(array('email_cobranca' => 'mike@visie.com.br', 'button' => '<button type="submit">Comprar</button>'),
                        array('id' => '1', 'desc' => 'Carrinho', 'valor' => 24.7, 'quantidade' => 2));

        $saida = $this->basic_exit.$this->basic_product.'<button type="submit">Comprar</button></form>';
        $this->assertEquals($content, $saida);
    }

    public function testMostraSemBotao()
    {
        $content = $this->mostra(array('email_cobranca' => 'mike@visie.com.br', 'button' => false),
                        array('id' => '1', 'desc' => 'Carrinho', 'valor' => 24.7, 'quantidade' => 2));

        $saida = $this->basic_exit.$this->basic_product.'</form>';
        $this->assertEquals($content, $saida);
    }

    public function testMostraJavaScript()
    {
        $content = $this->mostra(array('email_cobranca' => 'mike@visie.com.br', 'button' => false, 'javascript' => true),
                        array('id' => '1', 'desc' => 'Carrinho', 'valor' => 24.7, 'quantidade' => 2));

        $saida = $this->basic_exit.$this->basic_product.'</form><script type="text/javascript>document.getElementById(\'form_pagseguro\').submit()</script>';
        $this->assertEquals($content, $saida);
    }

    public function testMostraSemOpenECloseForm()
    {
        $content = $this->mostra(array('email_cobranca' => 'mike@visie.com.br', 'open_form' => false, 'close_form' => false),
                        array('id' => '1', 'desc' => 'Carrinho', 'valor' => 24.7, 'quantidade' => 2));

        $saida = '<input type="hidden" name="tipo" value="CP" /><input type="hidden" name="moeda" value="BRL" /><input type="hidden" name="email_cobranca" value="mike@visie.com.br" />'.$this->basic_product.'<input type="submit" value="Finalizar!" />';
        $this->assertEquals($content, $saida);
    }

    public function testMostraCarrinhoNotPrint()
    {
        $content = $this->mostra(array('email_cobranca' => 'mike@visie.com.br', 'button' => '', 'print' => false), array('id' => '1', 'desc' => 'Carrinho', 'valor' => 24.7, 'quantidade' => 2));
        $this->assertEquals($content, '');
    }

    public function testMostraCarrinhoReturn()
    {
        $carrinho = Pagseguro::Carrinho(array('email_cobranca' => 'mike@visie.com.br', 'button' => '', 'print' => false));
        $content = $carrinho->mostra();

        $saida = $this->basic_exit.'</form>';
        $this->assertEquals($content, $saida);
    }


    public function testMostraCarrinhoConfigLocal()
    {
        $carrinho = Pagseguro::Carrinho(array('email_cobranca' => 'mike@visie.com.br', 'button' => '', 'print' => false));

        // Sem alterar
        $content = $carrinho->mostra();
        $saida = $this->basic_exit.'</form>';
        $this->assertEquals($content, $saida);

        // Alterado só para esta exibição
        $content = $carrinho->mostra(array('button' => '<input type="submit" />'));
        $saida = $this->basic_exit.'<input type="submit" /></form>';
        $this->assertEquals($content, $saida);

        // De volta ao original
        $content = $carrinho->mostra();
        $saida = $this->basic_exit.'</form>';
        $this->assertEquals($content, $saida);
    }
}


// Fazendo o sistema rodar sozinho
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    rodaTest('CarrinhoTest');
}

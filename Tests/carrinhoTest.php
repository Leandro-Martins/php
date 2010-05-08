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

class WorngCarrinho
{
    private $email_cobranca = 'fake@visie.com.br';
}

class CarrinhoTest extends PHPUnit_Framework_TestCase
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

    public function testPassObjetOrArray()
    {
        $data = array(
            'email_cobranca' => 'mike@visie.com.br',
            'id_formulario' => 'formulario_pagseguro',
        );
        $carrinho = new Pagseguro_Carrinho($data);
        $this->assertEquals($data['email_cobranca'], $carrinho->email_cobranca);
        $this->assertEquals($data['id_formulario'], $carrinho->id_formulario);

        $data = (object) $data;
        $carrinho = new Pagseguro_Carrinho($data);
        $this->assertEquals($data->email_cobranca, $carrinho->email_cobranca);
        $this->assertEquals($data->id_formulario, $carrinho->id_formulario);

        $data_xml = new SimpleXMLElement('<data>'
            . '<email_cobranca>mike@visie.com.br</email_cobranca>'
            . '<id_formulario>formulario_pagseguro</id_formulario>'
            . '</data>');
        $carrinho = new Pagseguro_Carrinho($data_xml);
        $this->assertEquals($data->email_cobranca, $carrinho->email_cobranca);
        $this->assertEquals($data->id_formulario, $carrinho->id_formulario);
    }

    public function testNotAcceptWorngCartArgs()
    {
        $data = new WorngCarrinho;
        $carrinho = new Pagseguro_Carrinho($data);
        $this->assertEquals($carrinho->email_cobranca, null);
    }

    public function testSetBasico()
    {
        $email    = 'mike@visie.com.br';
        $carrinho = new Pagseguro_Carrinho;
        $carrinho->set('email_cobranca', $email);
        $this->assertEquals($email, $carrinho->email_cobranca);
    }

    public function testSetInvalid()
    {
        $carrinho = new Pagseguro_Carrinho;
        $this->setExpectedException('Exception');
        $carrinho->set('invalido', '');
    }

    public function testSetAcceptsObjectsAndArrays()
    {
        $data = array(
            'email_cobranca' => 'mike@visie.com.br',
        );
        $carrinho = new Pagseguro_Carrinho;
        $carrinho->set($data);
        $this->assertEquals($data['email_cobranca'], $carrinho->email_cobranca);
        settype($data, 'object');
        $carrinho = new Pagseguro_Carrinho;
        $carrinho->set($data);
        $this->assertEquals($data->email_cobranca, $carrinho->email_cobranca);
    }

    /**
     * @dataProvider valid_args
     */
    public function testSetValidArgs($key=null, $value=null, $return)
    {
        $carrinho = new Pagseguro_Carrinho;
        $carrinho->set($key, $value);
        $this->assertEquals($carrinho->{$key}, $return);
    }

    public function valid_args()
    {
        return array(
            array('email_cobranca', 'mike@visie.com.br', 'mike@visie.com.br'),
            array('id_formulario' , 'meu_formulario'   , 'meu_formulario'   ),
            array('tipo'          , 'CP'               , 'CP'               ),
            array('moeda'         , 'BRL'              , 'BRL'              ),
            array('frete'         , 2.6                , 260                ),
        );
    }

    /**
     * @dataProvider numbers
     */
    public function testConvertToNumber($entrada, $saida)
    {
        $carrinho = new Pagseguro_Carrinho;
        $this->assertEquals($saida, $carrinho->numero($entrada));
    }

    public function numbers()
    {
        return array(
            array(1     , 100),
            array(12    , 1200),
            array(3.4   , 340),
            array(1.35  , 135),
            array(1.254 , 125),
            array(1.246 , 125),
            array('12'  , 1200),
            array('12,3', 1230),
            array('12,3,8', 1230),
        );
    }

    /**
     * @dataProvider invalid_numbers
     */
    public function testInvalidValuesForConvertToNumbers($value)
    {
        $carrinho = new Pagseguro_Carrinho;
        $this->setExpectedException('Exception');
        $carrinho->numero($value);
    }

    public function invalid_numbers()
    {
        return array(
            array(array()),
            array(new stdClass),
        );
    }
    
    /**
     * @dataProvider validProducts
     */
    public function testSetValidProducts($produto, $valor)
    {
        $carrinho = Pagseguro::carrinho('mike@visie.com.br');
        $carrinho->produto($produto);
        settype($produto, 'array');
        $this->assertEquals($carrinho->produtos[0]->id, $produto['id']);
        $this->assertEquals($carrinho->produtos[0]->descr, $produto['descr']);
        $this->assertEquals($carrinho->produtos[0]->valor, $valor);
        $this->assertEquals($carrinho->produtos[0]->quant, $produto['quant']);
    }

    public function validProducts()
    {
        return array(
            array(array( 'id' => '0001', 'descr' => 'Veu de noiva', 'valor' => 89.9, 'quant' => 1), 8990),
            array((object) array( 'id' => '0001', 'descr' => 'Veu de noiva', 'valor' => '85,9', 'quant' => 1), 8590),
            array(new SimpleXMLElement('<data><id>201</id><descr>Amigos para sempre</descr><valor>2,90</valor><quant>3</quant></data>'), 290),
            array(new SimpleXMLElement('<data><id>2</id><descr>o Rappa</descr><valor>2.3</valor><quant>12</quant><frete>10,99</frete></data>'), 230, 1099),
            array(array( 'id' => '0032', 'descr' => 'Veu de noiva', 'valor' => '84,9', 'quant' => 1, 'frete' => 20), 8490, 2000),
            array(array( 'id' => '002', 'descr' => 'Caneta', 'valor' => '4', 'quant' => 3, 'frete' => '3,20', 'peso' => '200'), 400, 320),
        );
    }

    /**
     * @dataProvider invalidProdutcs
     * @expectedException Exception
     */
    public function testInvalidProducts($produto)
    {
        $carrinho = new Pagseguro_Carrinho;
        $carrinho->produto($produto);
    }

    public function invalidProdutcs()
    {
        // keys obrigatorias
        return array(
            array(array('id' => '1', 'descr' => 'Meu título', 'valor' => '10,9')),
            array(array('id' => '1', 'descr' => 'Meu título', 'quant' => '2')),
            array(array('id' => '1', 'valor' => '10,9', 'quant' => '2')),
            array(array('descr' => 'Meu título', 'valor' => '10,9', 'quant' => '2')),
            // array(array('id' => '1', 'descr' => 'Meu título', 'valor' => '10,9', 'quant' => '2'))
        );
    }

    /**
     * @dataProvider validProducts
     */
    public function testProdutoFretePeso($produto, $valor, $frete=null)
    {
        $carrinho = Pagseguro::carrinho('mike@visie.com.br');
        $carrinho->produto($produto);
        settype($produto, 'array');
        if ($frete===null) {
            $this->assertFalse(isset($carrinho->produtos[0]->frete));
        } else {
            $this->assertEquals($carrinho->produtos[0]->frete, $frete);
        }
        if (isset($produto['peso'])) {
            $this->assertEquals($carrinho->produtos[0]->peso, $produto['peso']);
        } else {
            $this->assertFalse(isset($carrinho->produtos[0]->peso));
        }
    }

    public function testIgnorandoNameInvalido()
    {
        $carrinho = Pagseguro::Carrinho('mike@visie.com.br');
        $carrinho->produto(array(
            'id' => '002', 
            'descr' => 'Arame Farpado', 
            'valor'  => .4, 
            'quant' => 3, 
            'frete' => 3, 
            'peso' => '200', 
            'ignore' => 'valor invalido'
        ));
        $this->assertFalse(isset($carrinho->produtos[0]->ignore));
    }
}

// Fazendo o sistema rodar sozinho
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    rodaTest('CarrinhoTest');
}

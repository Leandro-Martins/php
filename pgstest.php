<?php
require_once ('../simpletest/autorun.php');
require_once ('pgs.php');

$item_padrao = array (
  'id'          => '123',
  'descricao'   => 'Descricao de teste',
  'quantidade'  => 1,
  'valor'       => 1.00,
  'frete'       => 0,
  'peso'        => 0,
);

class TestClassPgs extends UnitTestCase {
  #function testClass() {
  #  $this->assertIdentical( get_class_methods('pgs'), array ('pgs', 'adicionar', 'cliente', 'mostra'), 'Os métodos são iguais' );
  #}
  function testPgs() {
    $pgs = new pgs();
    $this->assertIdentical($pgs->_itens, array(), 'Cria os itens do carrinho');
  }
}

class TestPgsAdicionar extends UnitTestCase {
  function testAdicionar () {
    global $item_padrao;
    $pgs = new pgs();
    $r = $pgs->adicionar($item_padrao);
    $this->assertIdentical($r, $pgs, 'Retorna ele mesmo');
    $this->assertIdentical(count($pgs->_itens), 1, 'Adiciona um elemento');
  }

  function testAdicionarElemento() {
    global $item_padrao;
    $pgs = new pgs();
    $pgs->adicionar($item_padrao);
    $this->assertIdentical($pgs->_itens[0], $item_padrao, 'Elemento adicionado');
  }

  function testAdicionarElementoValido() {
    $pgs = new pgs();
    $this->expectError("Item precisa ser um array.");
    $pgs->adicionar('elemento inválido');
    $this->assertIdentical(count($pgs->_itens), 0, 'Não pode String');
    $this->expectError("Item precisa ser um array.");
    $pgs->adicionar(123);
    $this->assertIdentical(count($pgs->_itens), 0, 'Não pode Inteiro');
    $this->expectError("Item precisa ser um array.");
    $pgs->adicionar(123.5);
    $this->assertIdentical(count($pgs->_itens), 0, 'Não pode Float');
    $this->expectError("Item precisa ser um array.");
    $pgs->adicionar(new stdClass());
    $this->assertIdentical(count($pgs->_itens), 0, 'Não pode Objeto');
    $this->expectError("Item precisa ser um array.");
    $pgs->adicionar(new pgs());
    $this->assertIdentical(count($pgs->_itens), 0, 'Não pode Objeto');
  }

  function testVerificaElemento(){
    $pgs = new pgs();
    $this->expectError("O item adicionado precisa conter id");
    $pgs->adicionar(array());
    $this->assertIdentical($pgs->_itens, array(),'Não pode array vazio');
    #foreach (array('id','descricao','quantidade','valor') as $elm) {
    foreach (array('id') as $elm) {
      $item = array (
        'id'          => '123',
        'descricao'   => 'Descricao de teste',
        'quantidade'  => 1,
        'valor'       => 1.00,
        'frete'       => 0,
        'peso'        => 0,
      );
      unset($item[$elm]);
      $this->expectError("O item adicionado precisa conter $elm");
      $pgs->adicionar($item);
      $this->assertIdentical($pgs->_itens, array(), "Não pode sem o $elm");
    }
  }
  function testUnidades () {
    $pgs = new pgs();
    foreach (array('','1.2','teste',array(),new pgs(),2.5) as $item) {
      $this->expectError("Valor invalido passado para quantidade.");
      $pgs->adicionar(array('id'=>'teste','descricao'=>'item de teste','quantidade'=>$item,'valor'=>250));
      $this->assertIdentical($pgs->_itens, array(), 'Quantidade não pode ser '.gettype($item));
    }

    $pgs=new pgs();
    $pgs->adicionar(array('id'=>'teste','descricao'=>'Item de teste','quantidade'=>'1','valor'=>250));
    $this->assertIdentical(count($pgs->_itens), 1, 'Quantidade pode ser string desde que tenha um inteiro como valor');

    $pgs = new pgs();
    foreach (array('','teste',array(),new pgs()) as $item) {
      $this->expectError("Valor invalido passado para valor.");
      $pgs->adicionar(array('id'=>'teste','descricao'=>'item de teste','quantidade'=>1,'valor'=>$item));
      $this->assertIdentical($pgs->_itens, array(), 'Valor não pode ser '.gettype($item));
    }

    $pgs=new pgs();
    $pgs->adicionar(array('id'=>'teste','descricao'=>'Item de teste','quantidade'=>1,'valor'=>250));
    $this->assertIdentical(count($pgs->_itens), 1, 'Valor pode ser inteiro');

    $pgs=new pgs();
    $pgs->adicionar(array('id'=>'teste','descricao'=>'Item de teste','quantidade'=>1,'valor'=>2.5));
    $this->assertIdentical(count($pgs->_itens), 1, 'Valor pode ser float');

    $pgs=new pgs();
    $pgs->adicionar(array('id'=>'teste','descricao'=>'Item de teste','quantidade'=>1,'valor'=>'250'));
    $this->assertIdentical(count($pgs->_itens), 1, 'Valor pode ser string, desde que contenha um inteiro');

    $pgs=new pgs();
    $pgs->adicionar(array('id'=>'teste','descricao'=>'Item de teste','quantidade'=>1,'valor'=>'250.4'));
    $this->assertIdentical(count($pgs->_itens), 1, 'Valor pode ser string, desde que contenha um float');

    $pgs=new pgs();
    $pgs->adicionar(array('id'=>'teste','descricao'=>'Item de teste','quantidade'=>1,'valor'=>'.4'));
    $this->assertIdentical(count($pgs->_itens), 1, 'Valor pode ser string, desde que contenha um float');

    $pgs = new pgs();
    foreach (array(2,array(),new pgs(),1.2,'', ' ') as $item) {
      $this->expectError("Valor invalido passado para descricao.");
      $pgs->adicionar(array('id'=>'teste','descricao'=>$item,'quantidade'=>1,'valor'=>2.5));
      $this->assertIdentical($pgs->_itens, array(), 'Descrição não pode ser '.gettype($item));
      $this->expectError("Valor invalido passado para id.");
      $pgs->adicionar(array('id'=>$item,'descricao'=>'Descrição simples','quantidade'=>1,'valor'=>2.5));
      $this->assertIdentical($pgs->_itens, array(), 'Id não pode ser '.gettype($item));
    }
  }
  function testFretePeso ($tipo='frete') {
    global $item_padrao;
    foreach (array ('', 'string', array (), new stdClass(), 1.5) as $elm) {
      $item = $item_padrao;
      $item[$tipo] = $elm;
      $psg = new pgs();
      $this->expectError("Valor invalido passado para $tipo.");
      $psg->adicionar($item);
      $this->assertIdentical($psg->_itens, array (), "O $tipo não pode ser " . gettype($elm));
    }
    $item = $item_padrao;
    $item[$tipo] = 120;
    $psg = new pgs();
    $psg->adicionar($item);
    $this->assertIdentical($psg->_itens, array ($item), "O $tipo deve ser integer");
  }
  function testPeso () {
    $this->testFretePeso('peso');
  }
}

class TestDados extends UnitTestCase {
  function testClassDados () {
    $pgs = new pgs();
    $this->assertIdentical(array_keys($pgs->_config), array ('email_cobranca', 'tipo', 'moeda'), 'Ao criar, deve possuir um array para o config');
    $this->assertIdentical($pgs->_config['tipo'], 'CP', 'Ao criar um elemento o carrinho deve ser próprio');
    $this->assertIdentical($pgs->_config['moeda'], 'BRL', 'Ao criar um elemento a moeda deve ser BRL');
  }
  function testOutrosDados () {
    $pgs = new pgs(array ('tipo'=>'CL', 'moeda'=>'DLU'));
    $this->assertIdentical(true, in_array('email_cobranca', array_keys($pgs->_config)), 'Ao criar, deve possuir um email_cobranca para o config');
    $this->assertIdentical(true, in_array('tipo', array_keys($pgs->_config)), 'Ao criar, deve possuir um tipo para o config');
    $this->assertIdentical(true, in_array('moeda', array_keys($pgs->_config)), 'Ao criar, deve possuir um moeda para o config');
    $this->assertIdentical($pgs->_config['tipo'], 'CL', 'Ao criar um elemento o carrinho deve ser próprio');
    $this->assertIdentical($pgs->_config['moeda'], 'DLU', 'Ao criar um elemento a moeda deve ser BRL');
  }
  function testValoresInvalidos() {
    foreach (array('', new stdClass(), 1, 1.2) as $item) {
      $pgs = new pgs($item);
      $this->assertIdentical($pgs->_config, array ('email_cobranca'=> '', 'tipo'=> 'CP', 'moeda'=>'BRL'), 'Não há modificações se receber ' . gettype($item));
    }
  }
  function testAlterandoPadrao () {
    $pgs = new pgs(array('email_cobranca' => 'teste@nasa.gov'));
    $this->assertIdentical($pgs->_config['email_cobranca'], 'teste@nasa.gov', 'Alterando o tipo padrão de e-mail');
  }
}

class TestCliente extends UnitTestCase {
  function testInsereCliente () {
    $pgs=new pgs();
    $pgs->cliente(array (
      'nome' => 'Michael'
    ));
    $this->assertIdentical($pgs->_cliente, array ('nome'=>'Michael'), 'Ao definir o cliente, insira-o na variavel _cliente');
  }
  function testNotArray() {
    foreach (array ('', 'invalido', new stdClass(), 11, 1.3) as $item) {
      $pgs = new pgs();
      $pgs->cliente($item);
      $this->assertIdentical($pgs->_cliente, array (), 'Não pode inserir um cliente se ele for um ' . gettype($item));
    }
  }

  function testFormas() {
    $item = array ('id' => 'a120', 'descricao' => 'um simples exemplo', 'quantidade' => '12', 'valor' => '12.12');
    $pgs=new pgs();
    $this->assertIdentical(count($pgs->adicionar($item)->_itens), 1, 'Adiciona um array simples');
    $pgs=new pgs();
    for ($i=0,$itens=array();$i<10;$i++)
      $itens[] = $item;
    $pgs->adicionar($itens);
    $this->assertIdentical(count($pgs->_itens), 10);
  }
}

class TestOutput extends UnitTestCase {
  function cliente(){
   return array (
     'nome'   => 'José de Arruda',
     'cep'    => '12345678',
     'end'    => 'Rua dos Tupiniquins',
     'num'    => 37,
     'compl'  => 'apto 507',
     'bairro' => 'Sto Amaro',
     'cidade' => 'São Camilo',
     'uf'     => 'SC',
     'pais'   => 'Brasil',
     'ddd'    => '48',
     'tel'    => '55554877',
     'email'  => 'josearruda@teste.com',
   );

  }
  function produto(){
    return array (
      'id' => 'abc',
      'descricao' => 'descr',
      'quantidade' => 10,
      'valor' => 100,
    );
  }

  function testOutputsimples () {
    $pgs = new pgs(array('email_cobranca'=>'joao@nasa.gov'));
    $this->assertPattern('/<form/', $pgs->mostra(array('print'=>false)), 'Abrindo o formuário');
    $this->assertPattern('/name="tipo" value="CP"/', $pgs->mostra(array('print'=>false)), 'Inserindo o tipo no formulário');
    $this->assertPattern('/name="moeda" value="BRL"/', $pgs->mostra(array('print'=>false)), 'Inserindo a moeda no formulário');
    $this->assertPattern('@</form@', $pgs->mostra(array('print'=>false)), 'Fecha o formulário');
  }

  function testOutputCliente(){
    $pgs = new pgs();
    $pgs->cliente($this->cliente());
    foreach ($this->cliente() as $key => $value)
      $this->assertPattern('/name="cliente_'.$key.'" value="'.$value.'"/', $pgs->mostra(array('print'=>false)), "O formulário deve possuir o $key do cliente");
  }

  function testOutputProdutos(){
    $pgs = new pgs();
    $pgs->adicionar($this->produto());
    $pgs->adicionar($this->produto());
    $pgs->adicionar($this->produto()+array('peso'=>120));
    $this->assertPattern('/name="item_id_1" value="abc"/',$pgs->mostra(array('print'=>false)),"Deve possuir o id");
    $this->assertPattern('/name="item_descr_1" value="descr"/',$pgs->mostra(array('print'=>false)),"Deve possuir uma descrição");
    $this->assertPattern('/name="item_quant_1" value="10"/',$pgs->mostra(array('print'=>false)),"Deve possuir a quantidade");
    $this->assertPattern('/name="item_valor_1" value="10000"/',$pgs->mostra(array('print'=>false)),"Deve possuir o valor formatado");

    $this->assertPattern('/name="item_id_2" value="abc"/',$pgs->mostra(array('print'=>false)),"Deve possuir o id");
    $this->assertPattern('/name="item_descr_2" value="descr"/',$pgs->mostra(array('print'=>false)),"Deve possuir uma descrição");
    $this->assertPattern('/name="item_quant_2" value="10"/',$pgs->mostra(array('print'=>false)),"Deve possuir a quantidade");
    $this->assertPattern('/name="item_valor_2" value="10000"/',$pgs->mostra(array('print'=>false)),"Deve possuir o valor formatado");

    $this->assertPattern('/name="item_peso_3" value="120"/',$pgs->mostra(array('print'=>false)),"Deve possuir o peso");
    $this->assertPattern('/type="submit"/', $pgs->mostra(array('print'=>false)), 'Deve mostrar o submit');

    ob_start(); $pgs->mostra(); $print = ob_get_contents(); ob_end_clean();
    $this->assertPattern('/form/', $print, 'Deve imprimir por padrão');
    $this->assertNoPattern('/<form/', $pgs->mostra(array('print'=>false,'open_form'=>false)), 'Não deve mostrar o abre form');
    $this->assertNoPattern('@</form@', $pgs->mostra(array('print'=>false,'close_form'=>false)), 'Não deve mostrar o fecha form');
    $this->assertNoPattern('/type="submit"/', $pgs->mostra(array('print'=>false,'show_submit'=>false)), 'Não deve mostrar o submit');
    $this->assertPattern('/type="image" src="imagem.jpg"/', $pgs->mostra(array('print'=>false,'img_button'=>'imagem.jpg')), 'Deve implementar a url da imagem');
    $this->assertPattern('/type="image" src="https:/', $pgs->mostra(array('print'=>false,'btn_submit'=>1)), 'Deve mostrar um dos submits do PagSeguro');

  }
}
?>

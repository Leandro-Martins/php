![PagSeguro](https://p.simg.uol.com.br/pagseguro/i/pagseguro_uol.gif)

# Biblioteca PagSeguro para PHP

A biblioteca é composta de três submódulos: Carrinho, Frete e Retorno.

Para você utilizar os submódulos você pode usar um dos seguintes meios:

## Via método estático

    $carrinho = Pagseguro::Carrinho();
    $frete    = Pagseguro::Frete();
    $retorno  = Pagseguro::Retorno();

## Via chamada de atributos

    $pagseguro = new Pagseguro;
    $carrinho  = $pagseguro->carrinho;
    $frete     = $pagseguro->frete;
    $retorno   = $pagseguro->retorno;

## Via método global getModule

    $pagseguro = new Pagseguro;
    $carrinho  = $pagseguro->getModule('carrinho');
    $frete     = $pagseguro->getModule('frete');
    $retorno   = $pagseguro->getModule('retorno');

# Classe de carrinho

O exemplo mostra como fazer um carrinho de compras básico com o PagSeguro.

    $carrinho = Pagseguro::carrinho('mike@visie.com.br');
    // Você também pode passar um array ou objeto de argumentos
    $checkout = Pagseguro::carrinho(array(
        'email_cobranca' => 'mike@visie.com.br',
    ));
    // Imagine que você tem um método que retorna um objeto
    function dados_loja() {
       $data = new stdClass;
       $data->email_cobranca = 'mike@visie.com.br'
       return $data
    }
    $data = dados_loja();
    $cart = Pagseguro::carrinho($data);
    // Ou mesmo que "puxe" do banco de dados
    function dados_loja_database() {
        $banco = new Banco;
        $result = $banco->query('SELECT * FROM config');
        $data = array();
        while($item = $result->fetch()) {
        	$data[$item->key] = $item->value;
        }
        return $data;
    }
    $data = dados_loja();
    $cart = Pagseguro::carrinho($data);

Você ainda consegue usar um arquivo xml de configuração (que pode ser mais interessante que conectar ao banco de dados. Tomemos como base o arquivo config.xml :

    <data>
        <email_cobranca>mike@visie.com.br</email_cobranca>
    </data>

E o seguinte código php:

    $data = simplexml_load_file('config.xml');
    $carrinho = Pagseguro::carrinho($data);

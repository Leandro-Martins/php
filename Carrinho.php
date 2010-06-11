<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

class Pagseguro_Carrinho
{
    static private $_itens_config  = array('url', 'target', 'id_formulario',
                                           'javascript', 'button',
                                           'email_cobranca', 'ref_transacao',
                                           'tipo', 'moeda', 'tipo_frete',
                                           'encoding', 'frete', 'peso');
    static private $_itens_config_input  = array('tipo', 'moeda',
                                           'email_cobranca', 'ref_transacao',
                                           'tipo_frete', 'encoding');
    static private $_buttons = array(
            0  => '',
            1  => '<input type="submit" value="Finalizar!" />',
            // Carrinho Simples
            2  => 'image:pagamento/btnComprarBR.jpg',
            3  => 'image:pagamento/btnPagarBR.jpg',
            4  => 'image:pagamento/btnPagueComBR.jpg',
            5  => 'image:pagamento/btnComprar.jpg',
            6  => 'image:pagamento/btnPagar.jpg',
            // Carrinho Próprio
            7  => 'image:carrinhoproprio/btnFinalizaBR.jpg',
            8  => 'image:carrinhoproprio/btnConcluirBR.jpg',
            9  => 'image:carrinhoproprio/btnFinalizar.jpg',
            10 => 'image:carrinhoproprio/btnConcluir.jpg',
            // Doação
            11 => 'image:doacao/btndoacao.jpg',
            12 => 'image:doacao/btndoar.jpg',
            13 => 'image:doacao/btndoar.jpg',
            14 => 'image:doacao/FacaSuaDoacao.gif',
        );

    static private $_itens_produto = array('id', 'descr', 'quant', 'valor',
                                          'frete', 'peso');
    static private $_itens_produtos_obrigatorios = array('id', 'descr', 'quant',
                                                         'valor');
    static private $_itens_cliente = array('nome', 'cep', 'end', 'num', 'compl',
                                           'bairro', 'cidade', 'uf', 'pais',
                                           'ddd', 'tel', 'email');
    static private $_substitutos_produtos = array(
        'id'    => array('id', 'ID', 'Id', 'code', 'codigo', 'SKU', 'sku',
                         'uid', 'uniqid', 'slug'),
        'descr' => array('descr', 'desc', 'descricao', 'description'),
        'valor' => array('valor', 'preco', 'price'),
        'quant' => array('quant', 'quantidade', 'qtd', 'qty', 'quantity'),
        'frete' => array('frete', 'freight'),
        'peso'  => array('peso', 'weight'),
    );
    static private $_substitutos_cliente = array(
        'nome'   => array('nome', 'name'),
        'cep'    => array('cep', 'CEP', 'postal', 'postalcode', 'postal_code'),
        'end'    => array('end', 'endereco', 'endereço', 'address', 'address1',
                          'addr', 'addr1'),
        'num'    => array('num', 'numero', 'número', 'number', 'n'),
        'compl'  => array('compl', 'complemento', 'complement', 'address2',
                          'addr2'),
        'bairro' => array('bairro'),
        'cidade' => array('cidade', 'city', 'cid'),
        'uf'     => array('uf', 'estado', 'state'),
        'pais'   => array('pais', 'país', 'country'),
        'ddd'    => array('ddd'),
        'tel'    => array('tel', 'telefone', 'telephone'),
        'email'  => array('email', 'e-mail', 'mail'),
    );

    // Configurações
    public $url            = 'https://pagseguro.uol.com.br/checkout/checkout.jhtml';
    public $target         = 'pagseguro';
    public $id_formulario  = 'form_pagseguro';
    public $javascript     = false; // Imprime o javascript auto_submit. Só funciona se passar o id_formulario
    public $button         = 1;     // Botão para exibir, pode ser um inteiro indice do $_buttons ou html puro

    // Inputs
    public $tipo           = 'CP';  // Tipo de carrinho: CP ou CBR
    public $moeda          = 'BRL'; // Moeda: BRL
    public $email_cobranca = null;  // Seu e-mail pagseguro
    public $ref_transacao  = null;  // código único
    public $tipo_frete     = null;  // Tipo de frete: EN, SD
    public $encoding       = null;  // Encoding: UTF-8, UTF-16 ou US-ASCII.

    // Inputs de produtos que setam valor unico: item_frete_1, item_peso_1
    public $frete          = null;  // Frete único para todos os produtos (omite o dos outros produtos)
    public $peso           = null;  // Peso único para todos os produtos (omite o dos outros produtos)

    // Produtos
    public $produtos = array();

    // Cliente
    public $cliente  = array();

    public function __construct($args=null)
    {
        if ('string' === gettype($args)) {
            $this->email_cobranca = $args;
        } elseif (is_object($args) || is_array($args)) {
            $this->set($args);
        }
    }

    public function set($key, $value=null)
    {
        if (is_object($key) || is_array($key)) {
            settype($key, 'array');
            foreach (self::$_itens_config as $item) {
                if (isset($key[$item])) {
                    $this->set($item, $key[$item]);
                }
            }
            return true;
        }
        if (in_array($key, self::$_itens_config)) {
            settype($value, 'string');
            if ($key=='frete') {
                $value = $this->numero($value);
            }
            if ($key=='peso') {
                $value = $this->numero($value, false);
            }
            $this->$key = $value;
        } else {
            throw new Exception('Invalid argument key: '.$key);
        }
    }

    public function numero($value, $multiplicar = true)
    {
        if (!is_scalar($value)) {
            throw new Exception('Invalid argument to convert: '
                               .gettype($value));
        }
        if ('string' === gettype($value)) {
            $value = preg_replace('@[^0-9,\.-]@', '', $value);
            $value = str_replace(',', '.', $value);
        }
        if ($multiplicar) {
            $value = $value * 100;
        }
        $return = round($value);
        return $return;
    }

    /**
     * Adiciona um produto ao carrinho
     *
     * @param array|object $produto O produto em si
     */
    public function produto($produto)
    {
        if ('array' === gettype($produto) && isset($produto[0])) {
            foreach ($produto as $item) {
                $this->produto($item);
            }
            return;
        }
        settype($produto, 'array');
        foreach (self::$_substitutos_produtos as $chave=>$substs) {
            foreach ($substs as $item) {
                if (isset($produto[$item]) && $produto[$item]) {
                    $valor = $produto[$item];
                    unset($produto[$item]);
                    $produto[$chave] = $valor;
                }
            }
        }
        $chaves = array_keys($produto);
        foreach (self::$_itens_produtos_obrigatorios as $item) {
            if (!in_array($item, $chaves)) {
                throw new Exception('This product does not have the obrigatory '
                                   .'key: '.$item);
            }
        }
        settype($produto, 'object');
        $produto->valor = $this->numero($produto->valor);
        if (isset($produto->frete)) {
            $produto->frete = $this->numero($produto->frete);
        }
        if (isset($produto->peso)) {
            $produto->peso = $this->numero($produto->peso, false);
        }
        $p = array();
        foreach ($produto as $chave => $valor) {
            if (in_array($chave, self::$_itens_produto)) {
                $p[$chave] = $valor;
            }
        }
        $this->produtos[] = (object) $p;
    }

    /**
     * Adiciona dados do cliente
     *
     * @param string|array|object $key   Chave do campo que deseja adicionar
     * @param string|null         $value Valor da Chave
     */
    public function cliente($key, $value=null)
    {
        if (is_array($key) || is_object($key)) {
        	settype($key, 'array');
            foreach ($key as $k=>$v) {
                $this->cliente((string) $k, (string) $v);
            }
            return;
        }
        foreach (self::$_substitutos_cliente as $chave=>$valores) {
            foreach ($valores as $item) {
            	if ($key==$item) {
                	$key = $chave;
                }
            }
        }
        if (in_array($key, self::$_itens_cliente)) {
            $this->cliente[$key] = $value;
        }
    }

    public function input($name, $value)
    {
        return sprintf('<input type="hidden" name="%s" value="%s" />', $name, $value);
    }

    public function mostra(array $config=array())
    {
        $id = $target = $after_form = '';

        if ($this->id_formulario) {
            $id = " id=\"{$this->id_formulario}\"";
        }
        if ($this->target) {
            $target = " target=\"{$this->target}\"";
        }
        $open_form  = sprintf('<form action="%s"%s method="post"%s>', $this->url, $id, $target);

        $setup      = $this->_mostra_setup();
        $produtos   = $this->_mostra_produtos();
        $cliente    = $this->_mostra_cliente();
        $botao      = $this->_mostra_botao($this->button);

        $close_form = '</form>';
        if ($this->javascript && $this->id_formulario) {
            $after_form = '<script type="text/javascript>'
                        . 'document.getElementById(\''
                        . $this->id_formulario . '\').submit()</script>';
        }

        $interna   = $setup . $produtos . $cliente;
        $saida = $open_form . $interna . $botao . $close_form . $after_form;

        print $saida;
    }

    private function _mostra_setup()
    {
        $setup = '';
        if ($this->tipo) {
            $this->tipo = strtoupper($this->tipo) == 'CP' ? 'CP' : 'CBR';
        }

        foreach (self::$_itens_config_input as $key) {
            if ($this->$key) {
                $setup .= $this->input($key, $this->$key);
            }
        }

        if ($this->frete) {
            $setup .= $this->input('item_frete_1', $this->frete);
        }
        if ($this->peso) {
            $setup .= $this->input('item_peso_1', $this->peso);
        }
        return $setup;
    }

    private function _mostra_cliente()
    {
        $cliente = '';
        foreach ($this->cliente as $key=>$value) {
            $cliente .= $this->input('cliente_'.$key, $value);
        }
        return $cliente;
    }

    private function _mostra_produtos()
    {
        if ('CBR' === $this->tipo && count($produtos) > 1) {
            $message = 'The cart type CBR may have only one product. '
                     . 'Will be shown just one product.';
            trigger_error($message, E_USER_NOTICE);
        }

        $saida = '';
        $item = 0;
        foreach ($this->produtos as $produto) {
            $item++;
            foreach ($produto as $key=>$value) {
                $is_peso_unico  = ($key=='peso' && $this->peso);
                $is_frete_unico = ($key=='frete' && $this->frete);
                if ( $is_peso_unico || $is_frete_unico ) {
                    continue 1;
                }
                $saida .= $this->input('item_'.$key.'_'.$item, $value);
            }
            if ('CBR' === $this->tipo) {
                break;
            }
        }
        return $saida;
    }

    public function _mostra_botao($button)
    {
        if (ctype_digit($button)) {
            $button = (int) $button;
        }
        if ( in_array($button, array_keys(self::$_buttons), true) ) {
            $button = self::$_buttons[$button];
            $pastas = '(pagamento|carrinhoproprio|docacao)';
            $regexp = '@^image:('.$pastas.'.+\.(jpg|gif))$@';
            if (preg_match($regexp, $button, $m)) {
                $button = '<input type="image" src="https://p.simg.uol.com.br/out/pagseguro/i/botoes/'
                        . $m[1] . '" name="submit" alt="Pague com PagSeguro - é rápido,'
                        . ' grátis e seguro!" />';
            }
        }
        return $button;
    }
}
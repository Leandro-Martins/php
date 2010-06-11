<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

class Pagseguro_Carrinho
{
    static private $_itens_config  = array('url', 'target', 'id_formulario',
                                           'javascript', 'email_cobranca',
                                           'ref_transacao', 'tipo', 'moeda',
                                           'tipo_frete', 'encoding', 'frete',
                                           'peso');
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

    public $url            = 'https://pagseguro.uol.com.br/checkout/checkout.jhtml';
    public $target         = 'pagseguro';
    public $id_formulario  = 'form_pagseguro';
    public $javascript     = false; // Imprime o javascript auto_submit

    public $email_cobranca = null;  // Seu e-mail pagseguro
    public $ref_transacao  = null;  // código único
    public $tipo           = 'CP';  // Tipo de carrinho: CP ou CBR
    public $moeda          = 'BRL'; // Moeda: BRL
    public $tipo_frete     = null;  // Tipo de frete: EN, SD
    public $encoding       = null;  // Encoding: UTF-8, UTF-16 ou US-ASCII.

    public $frete          = null;  // Frete único para todos os produtos (omite o dos outros produtos)
    public $peso           = null;  // Peso único para todos os produtos (omite o dos outros produtos)

    public $produtos = array();
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
        $saida = $id = $target = '';
        if ($this->id_formulario) {
            $id = " id=\"{$this->id_formulario}\"";
        }
        if ($this->target) {
            $target = " target=\"{$this->target}\"";
        }
        $this->tipo = strtoupper($this->tipo) == 'CP' ? 'CP' : 'CBR';
        $saida .= sprintf('<form action="%s"%s method="post"%s>', $this->url, $id, $target);
        $saida .= $this->input('tipo', $this->tipo);
        $saida .= $this->input('moeda', $this->moeda);
        $saida .= $this->input('email_cobranca', $this->email_cobranca);

        if ($this->frete) {
            $saida .= $this->input('item_frete_1', $this->frete);
        }
        if ($this->peso) {
            $saida .= $this->input('item_peso_1', $this->peso);
        }

        $saida .= $this->_mostra_produtos();

        foreach ($this->cliente as $key=>$value) {
            $saida .= $this->input('cliente_'.$key, $value);
        }

        $saida .= '<input type="submit" value="Finalizar!" />';
        $saida .= '</form>';

        print $saida;
    }

    private function _mostra_produtos()
    {
        if ('CBR' === $this->tipo && count($produtos) > 1) {
            $message = 'O carrinho do tipo CBR possui mais de um produto. '
                     . 'Será exibido apenas o primeiro produto.';
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
}

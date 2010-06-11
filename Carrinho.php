<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

class Pagseguro_Carrinho
{
    static private $_itens_config  = array('url', 'email_cobranca', 'id_formulario',
                                          'tipo', 'moeda', 'frete', 'javascript');
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
    public $email_cobranca = null;
    public $id_formulario  = 'form_pagseguro';
    public $tipo           = 'CP';
    public $moeda          = 'BRL';
    public $frete          = null;
    public $javascript     = false;

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
            $this->$key = $value;
        } else {
            throw new Exception('Invalid argument key: '.$key);
        }
    }

    public function numero($value)
    {
        if (!is_scalar($value)) {
            throw new Exception('Invalid argument to convert: '
                               .gettype($value));
        }
        if ('string' === gettype($value)) {
            $value = preg_replace('@[^0-9,\.-]@', '', $value);
            $value = str_replace(',', '.', $value);
        }
        $return = round($value * 100);
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
            $produto->frete   = $this->numero($produto->frete);
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

    }
}

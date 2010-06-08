<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

class Pagseguro_Carrinho
{
    static private $_itens_config  = array('email_cobranca', 'id_formulario',
                                          'tipo', 'moeda', 'frete');
    static private $_itens_produto = array('id', 'descr', 'quant', 'valor',
                                          'frete', 'peso');
    static private $_itens_produtos_obrigatorios = array('id', 'descr', 'quant',
                                                         'valor');
    static private $_itens_cliente = array('nome', 'cep', 'end', 'num', 'compl',
                                           'bairro', 'cidade', 'uf', 'pais',
                                           'ddd', 'tel', 'email');
    static private $_substitutos = array(
        'id'    => array('id', 'ID', 'Id', 'code', 'codigo', 'SKU', 'sku',
                         'uid', 'uniqid', 'slug'),
        'descr' => array('descr', 'desc', 'descricao', 'description'),
        'valor' => array('valor', 'preco', 'price'),
        'quant' => array('quant', 'quantidade', 'qtd', 'qty', 'quantity'),
        'frete' => array('frete', 'freight'),
        'peso'  => array('peso', 'weight'),
    );

    public $id_formulario = 'form_pagseguro';
    public $email_cobranca = null;
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
        foreach (self::$_substitutos as $chave=>$substs) {
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
     * @return void
     */
    public function cliente($key, $value=null)
    {
        if (is_array($key) || is_object($key)) {
            foreach ($key as $k=>$v) {
                $this->cliente((string) $k, (string) $v);
            }
            return;
        }
        if (in_array($key, self::$_itens_cliente)) {
            $this->cliente[$key] = $value;
        }
    }
}

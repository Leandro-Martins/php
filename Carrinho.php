<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

class Pagseguro_Carrinho
{
    static private $itens_config = array('email_cobranca', 'id_formulario', 'tipo',
                                         'moeda', 'frete');

    public $id_formulario = 'form_pagseguro';
    public $email_cobranca = null;
    public $produtos = array();

    public function __construct($args=null)
    {
        if ('string' === gettype($args)) {
            $this->email_cobranca = $args;
        } elseif (is_object($args) OR is_array($args)) {
            $this->set($args);
        }
    }

    public function set($key, $value=null)
    {
        if (is_object($key) OR is_array($key)) {
            settype($key, 'array');
            foreach (self::$itens_config as $item) {
                if (isset($key[$item])) {
                    $this->set($item, $key[$item]);
                }
            }
            return true;
        }
        if (in_array($key, self::$itens_config)) {
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
            throw new Exception('Invalid argument to convert: '.gettype($value));
        }
        if ('string' === gettype($value)) {
            $value = preg_replace('@[^0-9,\.-]@', '', $value);
            $value = str_replace(',', '.', $value);
        }
        $return = round($value * 100);
        return $return;
    }

    public function produto($produto)
    {
        settype($produto, 'array');
        $chaves       = array_keys($produto);
        $obrigatorios = array('codigo', 'titulo', 'quantidade', 'preco');
        foreach ($obrigatorios as $item) {
            if (!in_array($item, $chaves)) {
                throw new Exception('This product does not have the obrigatory key: '.$item);
            }
        }
        settype($produto, 'object');
        $produto->preco   = $this->numero($produto->preco);
        $this->produtos[] = $produto;
    }
}

<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

class Pagseguro_Carrinho
{
    public $id = 'form_pagseguro';
    public $email_cobranca = null;

    public function __construct($args=null)
    {
        if ('string' === gettype($args)) {
            $this->email_cobranca = $args;
        } elseif (is_object($args) OR is_array($args)) {
            settype($args, 'array');
            $itens_config = array('email_cobranca', 'id');
            foreach ($itens_config as $item) {
                if (isset($args[$item])) {
                    settype($args[$item], 'string');
                    $this->$item = $args[$item];
                }
            }
        }
    }
}

<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

class Pagseguro_Carrinho
{
    public $email_cobranca = null;

    public function __construct($args=null)
    {
        if (gettype ($args)) {
            $this->email_cobranca = $args;
        }
    }
}

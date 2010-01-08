<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

class Pagseguro
{
    public function __get($key)
    {
        if (in_array($key, array('carrinho', 'frete', 'retorno'))) {
            return true;
        }
        throw new Exception('Invalid argument required.');
    }
}

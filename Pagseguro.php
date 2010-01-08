<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

class Pagseguro
{
    private function getModule($module)
    {
        require_once dirname(__FILE__).DIRECTORY_SEPARATOR.$module.'.php';
        $module = "Pagseguro_{$module}";
        return new $module;
    }

    static function carrinho()
    {
        return self::getModule('Carrinho');
    }

    static function frete()
    {
        return self::getModule('Frete');
    }

    static function retorno()
    {
        return self::getModule('Retorno');
    }

    public function __get($key)
    {
        if (in_array($key, array('carrinho', 'frete', 'retorno'))) {
            return true;
        }
        throw new Exception('Invalid argument required.');
    }
}

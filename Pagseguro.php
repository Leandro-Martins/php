<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

class Pagseguro
{
    public function getModule($module, $args=null)
    {
        $module = ucfirst(strtolower($module));
        require_once dirname(__FILE__).DIRECTORY_SEPARATOR.$module.'.php';
        $module = "Pagseguro_{$module}";
        return new $module($args);
    }

    static function carrinho($args=null)
    {
        return self::getModule('Carrinho', $args);
    }

    static function frete($args=null)
    {
        return self::getModule('Frete');
    }

    static function retorno($args=null)
    {
        return self::getModule('Retorno');
    }

    static function doacao($args=null)
    {
        return self::getModule('Doacao', $args);
    }

    public function __get($key)
    {
        if (in_array($key, array('carrinho', 'frete', 'retorno'))) {
            return $this->getModule(ucfirst($key));
        }
        throw new Exception('Invalid argument required.');
    }
}

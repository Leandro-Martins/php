<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Carrinho.php';

class Pagseguro_Doacao extends Pagseguro_Carrinho
{
    public $tipo = false;
    public $url  = 'https://pagseguro.uol.com.br/checkout/doacao.jhtml';
    public $id_formulario = false;
}

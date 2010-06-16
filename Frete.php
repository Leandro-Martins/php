<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

class Pagseguro_Frete
{
    // http://shopping.correios.com.br/wbm/shopping/script/CalcPrecoPrazo.aspx
    // ?StrRetorno=xml
    // &nCdServico=40010
    // &nVlPeso=1
    // &sCepOrigem=00000000
    // &sCepDestino=00000000
    // &nCdFormato=1
    // &sCdMaoPropria=N
    // &sCdAvisoRecebimento=N
    // &nVlValorDeclarado=0

    ####################################
    # Código dos Serviços dos Correios #
    #    FRETE PAC        = 41106      #
    #    FRETE SEDEX      = 40010      #
    #    FRETE SEDEX 10   = 40215      #
    #    FRETE SEDEX HOJE = 40290      #
    #    FRETE E-SEDEX    = 81019      #
    #    FRETE MALOTE     = 44105      #
    #    FRETE NORMAL     = 41017      #
    #    SEDEX A COBRAR   = 40045      #
    ####################################

    public $source  = array('pagseguro', 'correios'); // pagseguro, correios
    public $origem  = null;
    public $destino = null;
    public $peso    = 0; // Em gramas: 300 equivale a 3000 gramas. 1000 = 1Kg
    public $valor   = 0; // Valor declarado
    public $tipo    = array('sedex', 'pac'); // sedex, pac, sedex10, sedexhoje, esedex, malote, normal, sedexacobrar - nota: o pagseguro só aceita sedex/pac
}

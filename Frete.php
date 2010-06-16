<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

class Pagseguro_Frete
{
    static private $urlCorreios = 'http://shopping.correios.com.br/wbm/shopping/script/CalcPrecoPrazo.aspx';

    static private $servicosCorreios = array(
        'pac'          => 41106,
        'sedex'        => 40010,
        'sedex10'      => 40215,
        'sedexhoje'    => 40290,
        'esedex'       => 81019,
        'malote'       => 44105,
        'normal'       => 41017,
        'sedexacobrar' => 40045,
    );

    public $source  = array('pagseguro', 'correios'); // pagseguro, correios
    public $origem  = null;
    public $destino = null;
    public $peso    = 0; // Em gramas: 300 equivale a 3000 gramas. 1000 = 1Kg
    public $valor   = 0; // Valor declarado
    public $tipo    = array('sedex', 'pac'); // sedex, pac, sedex10, sedexhoje, esedex, malote, normal, sedexacobrar - nota: o pagseguro só aceita sedex/pac

    function montaCorreiosURL($source)
    {
        $peso = number_format($this->peso, 3, ',', '');
        $serv = $self::$servicosCorreios[$source];
        $url  = $self::$urlCorreios;
        $data = array(
            'StrRetorno'          => 'xml',
            'nCdServico'          => $serv,
            'nVlPeso'             => $peso,
            'sCepOrigem'          => $this->origem,
            'sCepDestino'         => $this->destino,
            'nCdFormato'          => 1,
            'sCdMaoPropria'       => 'N',
            'sCdAvisoRecebimento' => 'N',
            'nVlValorDeclarado'   => $this->valor,
        );
    }
}

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

    public function source()
    {
        $args = func_get_args();
        $data = array();
        foreach ($args as $item) {
            if (is_scalar($item) || 'array' === gettype($item)) {
                $data = array_merge($data, (array) $item);
            }
        }
        foreach ($data as $k=>$v) {
            $data[$k] = strtolower($v);
            if (!in_array(strtolower($v), array('pagseguro', 'correios'))) {
                unset($data[$k]);
            }
        }
        $data = array_unique($data);
        $this->source = $data;
    }

    private function _trata_origem_destino($cep)
    {
        if (!is_scalar($cep)) {
            throw new Exception('Invalid CEP source');
        }
        $cep = preg_replace('@\D@', '', $cep);
        if (strlen($cep) != 8) {
            throw new Exception('Invalid CEP source length');
        }
        return $cep;
    }

    public function origem($origem)
    {
        $this->origem = $this->_trata_origem_destino($origem);
    }

    public function destino($destino)
    {
        $this->destino = $this->_trata_origem_destino($destino);
    }

    public function montaURLCorreios($source)
    {
        $peso = number_format($this->peso, 3, ',', '');
        $serv = self::$servicosCorreios[$source];
        $url  = self::$urlCorreios;
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
        $url .= '?' . http_build_query($data, '_', '&');
        return $url;
    }

    public function parseCorreios($xml)
    {
        $xml = preg_replace('@(^\s*<[^>]+>|<[^>]+>\s*$)@', '', $xml);
        preg_match_all('@<(\w+)>(.+)<\/\1>@', $xml, $xml);
        $data = array();
        foreach ($xml[1] as $key => $value) {
            $data[$value] = $xml[2][$key];
        }
        return $data;
    }
}

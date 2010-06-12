<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

class Pagseguro_Retorno
{
	static private $_itens_geral = array (
            'VendedorEmail', 'TransacaoID', 'Referencia', 'Extras',
            'TipoFrete', 'ValorFrete', 'Anotacao', 'DataTransacao',
            'TipoPagamento', 'StatusTransacao', 'NumItens',
        );
    static private $_itens_cliente = array(
            'nome', 'email', 'endereco', 'numero', 'complemento',
            'bairro', 'cidade', 'estado', 'CEP', 'telefone',
        );
    static private $_itens_produto = array(
            'ID', 'descricao', 'valor', 'quantidade', 'frete'
        );
    static private $_itens_numericos = array(
            'ValorFrete', 'NumItens', 'valor', 'quantidade', 'frete'
        );

	public $url     = 'https://pagseguro.uol.com.br/pagseguro-ws/checkout/NPI.jhtml';
	public $timeout = 20;
	public $token   = null;
	public $funcao  = null;

	public function __construct($funcao)
	{
		if (defined('TOKEN')) {
    		$this->token = constant('TOKEN');
        }
        $this->funcao = $funcao;
    }

    public function prepara(array $post)
    {
        $post['Comando'] = 'validar';
        $post['Token']   = $this->token;
        return $post;
    }

    public function go($data=null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }

    public function post($key, $numeric = false)
    {
        $value = isset($_POST[$key]) ? $_POST[$key] : '';
        if ($numeric) {
        	$value = (double) (str_replace(',', '.', $value));
        }
        return $value;
    }

    public function numerico($key)
    {
        return in_array($key, self::$_itens_numericos);
    }

    public function run()
    {
    	if (!function_exists($this->funcao)) {
        	return false;
        }
        if ($this->go($this->prepara($_POST)) != 'VERIFICADO') {
            return false;
        }
        $geral = $cliente = $produtos = array();
        $referencia = $this->post('Referencia');
        foreach (self::$_itens_geral as $item) {
            $geral[$item] = $this->post($item, $this->numerico($item));
        }
        foreach (self::$_itens_cliente as $item) {
            $cliente[$item] = $this->post('Cli'.ucfirst($item));
        }
        $total = 0;
        for($i=1;$this->post("ProdID_{$i}");$i++) {
        	$produto = array();
            foreach (self::$_itens_produto as $item) {
            	$var = 'Prod'.ucfirst($item).'_'.$i;
            	$numerico = $this->numerico($item);
            	$produto[$item] = $this->post($var, $numerico);
            }
            settype($produto, 'object');
            $total     += $produto->valor + $produto->frete;
            $produtos[] = (object) $produto;
        }
        settype($geral, 'object');
        settype($cliente, 'object');
        $dados = (object) compact(
            'referencia', 'total', 'cliente', 'produtos', 'geral'
        );
        call_user_func($this->funcao, $dados);
    }
}


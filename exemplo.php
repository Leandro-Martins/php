<?php
# $sql = new PDO('sqlite2:file.sqlite');

# $sql->query('CREATE TABLE "carrinho" ("id" INTEGER PRIMARY KEY  NOT NULL, "cod" VARCHAR NOT NULL, "desc" VARCHAR NOT NULL, "qtd" FLOAT NOT NULL, "valor" FLOAT NOT NULL)');

#$sql->query("INSERT INTO /"carrinho/" VALUES(1,'A27','Mapa da cidade',1,'27.9');");
#$sql->query("INSERT INTO /"carrinho/" VALUES(2,'B720','Bala Freegells',12,'.8');");
#$sql->query("INSERT INTO /"carrinho/" VALUES(3,'A90','Caderno decorado',5,'16.30');");
#$sql->query("INSERT INTO /"carrinho/" VALUES(4,'C230','Tomada simples',16,'1.7');");


include ('pgs.php');
$pgs = new pgs(array(
  'email_cobranca' => 'suporte@lojamodelo.com.br',
  'tipo_frete'     => 'EN',
  'ref_transacao'  => 'AFB-580',
));

$sql = new PDO('sqlite2:file.sqlite');

$carrinho = $sql->query('SELECT * FROM "carrinho"');

foreach ($carrinho as $item) {
  $item = array (
    'id'         => $item['cod'],
    'descricao'  => $item['desc'],
    'quantidade' => $item['qtd'],
    'valor'      => $item['valor']
  );
  $pgs->adicionar($item);
}

$pgs->mostra(array ('btn_submit'=>2));
?>
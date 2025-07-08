<?php

require 'config.php'; 
session_start();

// Verifica se o utilizador está logado
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit;
}

// 1. Inserir a compra na tabela compras
$sql = "INSERT INTO compras (nome_cliente, numfiscal, contacto, email, data_compra, hora_compra, 
        morada_ent, cod_postal_ent, localidade_ent, valor_compra, data_entrega, hora_entrega) 
        VALUES (:nome_cliente, :numfiscal, :contacto, :email, :data_compra, :hora_compra, 
        :morada_ent, :cod_postal_ent, :localidade_ent, :valor_compra, :data_entrega, :hora_entrega)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':nome_cliente'  => $_POST['tnome'],
    ':numfiscal'     => $_POST['tnumfiscal'],
    ':contacto'      => $_POST['tcontacto'],
    ':email'         => $_POST['temail'],
    ':data_compra'   => date('Y-m-d'), // Obtém a data no formato correto para o MySQL
    ':hora_compra'   => date('H:i:s'), // Obtém a hora no formato correto para o MySQL
    ':morada_ent'    => $_POST['tmoradaent'],
    ':cod_postal_ent'=> $_POST['tcodpostalent'],
    ':localidade_ent'=> $_POST['tlocalidadeent'],
    ':valor_compra'  => $_SESSION['totalCarrinho'] ?? 0, // Usa o total do carrinho ou 0 se não existir
    ':data_entrega'  => date('Y-m-d'), // Mesma data da compra
    ':hora_entrega'  => date('H:i:s')  // Mesma hora da compra
]);

// Obtém o último id inserido (codcompra)
$codcompra = $pdo->lastInsertId();

// Atribua os valores a variáveis
$sessionId = session_id();
$codCliente = $_SESSION['codcliente'];

$sqlArtigos = "INSERT INTO compras_artigos (codcompra, codcarrinho, codartigo, pvp, quant, session_id, codcliente)
               SELECT :codcompra, codcarrinho, codartigo, pvp, quant, session_id, codcliente
               FROM carrinho
               WHERE codcliente = :codcliente
               AND session_id = :session_id";

$stmtArtigos = $pdo->prepare($sqlArtigos);
$stmtArtigos->bindParam(':codcompra', $codcompra, PDO::PARAM_INT);
$stmtArtigos->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
$stmtArtigos->bindParam(':codcliente', $codCliente, PDO::PARAM_INT);
$stmtArtigos->execute();

// Limpar o carrinho do cliente logado após a compra
$sqlLimparCarrinho = "DELETE FROM carrinho 
                      WHERE (codcliente = :codcliente AND codcliente IS NOT NULL)
                      OR (session_id = :session_id AND codcliente IS NULL)";

$stmtLimparCarrinho = $pdo->prepare($sqlLimparCarrinho);
$stmtLimparCarrinho->execute([
    ':codcliente' => $_SESSION['codcliente'] ?? null,
    ':session_id' => session_id()
]);

// Atualizar o stock dos artigos comprados
$sqlStockUpdate = "UPDATE artigos a
                   JOIN compras_artigos ca ON a.codartigo = ca.codartigo
                   JOIN compras c ON ca.codcompra = c.cod_compra  -- Corrigir para 'c.cod_compra'
                   SET a.qstock = a.qstock - ca.quant
                   WHERE ca.codcompra = :codcompra
                   AND c.nome_cliente = :nome_cliente";  // Filtra para o cliente específico

$stmtStock = $pdo->prepare($sqlStockUpdate);
$stmtStock->execute([
    ':codcompra' => $codcompra,
    ':nome_cliente' => $_POST['tnome']  // Nome do cliente que fez a compra
]);

unset($_SESSION['carrinho'], $_SESSION['totalCarrinho'], $_SESSION['cart_count']);


?>


<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Compra Realizada - Basic Prints</title>
  <link rel="stylesheet" href="processar_compra.css">
  <link rel="icon" href="img/novo-isotipo.png" type="logo-site">
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap" rel="stylesheet">

</head>
<body>

<main>
  <div class="sucesso-box">
    <h2>✅ Compra realizada com sucesso!</h2>
    <p>Obrigado por confiar na <strong>Basic Prints</strong>! Seu pedido foi processado com sucesso.</p>
    <a href="compras.php" class="botao">Voltar para a loja</a>
  </div>
</main>

</body>
</html>

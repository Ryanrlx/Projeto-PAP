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
  <link rel="icon" href="img/novo-isotipo.png" type="logo-site">
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: "Kanit", sans-serif;
      background-color: #f4f4f4;
      color: #333;
      padding: 100px 20px 20px 20px;
    }

    main {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: calc(100vh - 150px);
    }

    .sucesso-box {
      background-color: white;
      padding: 50px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      text-align: center;
      max-width: 600px;
      width: 100%;
      animation: fadeIn 0.8s ease;
    }

    .sucesso-box h2 {
      color: #2e7d32;
      font-size: 28px;
      margin-bottom: 20px;
    }

    .sucesso-box p {
      font-size: 18px;
      color: #444;
      margin-bottom: 30px;
    }

    .botao {
      display: inline-block;
      background-color: blueviolet;
      color: white;
      padding: 12px 25px;
      text-decoration: none;
      border-radius: 8px;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }

    .botao:hover {
      background-color: #9160be;
    }
    /* animação */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

<main>
  <div class="sucesso-box">
    <h2>✅ Compra realizada com sucesso!</h2>
    <p>Obrigado por confiar na <strong>Basic Prints</strong>! Seu pedido foi processado com sucesso.</p>
    <a href="index.html" class="botao">Voltar para a loja</a>
  </div>
</main>

</body>
</html>

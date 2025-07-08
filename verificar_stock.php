<?php
require 'config.php';
session_start();

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
$session_id = session_id();
$codcliente = $_SESSION['codcliente'] ?? null;

$sql = "SELECT a.descricao, a.qstock, c.quant 
        FROM carrinho c 
        JOIN artigos a ON c.codartigo = a.codartigo 
        WHERE (c.codcliente = :codcliente AND c.codcliente IS NOT NULL)
        OR (c.session_id = :session_id AND c.codcliente IS NULL)";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':session_id', $session_id, PDO::PARAM_STR);
$stmt->bindParam(':codcliente', $codcliente, PDO::PARAM_INT);
$stmt->execute();
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$forastock = array_filter($itens, fn($item) => $item['quant'] > $item['qstock']);

echo json_encode(['erro' => !empty($forastock), 'itens' => array_values($forastock)]);
?>
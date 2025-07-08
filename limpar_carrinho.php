<?php
// Inicializa ou recupera a sessão
session_start();
require 'config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Identifica a sessão do utilizador (ou cliente logado)
    $session_id = session_id();
    $codcliente = isset($_SESSION['codcliente']) ? $_SESSION['codcliente'] : null;

    // Limpa o carrinho do utilizador ou cliente atual (ou da sessão)
    $stmt = $pdo->prepare("DELETE FROM carrinho WHERE (codcliente = :codcliente AND codcliente IS NOT NULL) 
                          OR (session_id = :session_id AND codcliente IS NULL)");
    $stmt->bindParam(':session_id', $session_id, PDO::PARAM_STR);
    $stmt->bindParam(':codcliente', $codcliente, PDO::PARAM_INT);
    $stmt->execute();

    // Inicializa a zero o contador de itens no carrinho
    $_SESSION['cart_count'] = 0;
    $_SESSION['message'] = "Carrinho limpo com sucesso!";

    // Redireciona para a página do carrinho
    header('Location: ver_carrinho.php');
    exit();

} catch (PDOException $e) {
    die("Erro ao limpar o carrinho: " . $e->getMessage());
}
?>
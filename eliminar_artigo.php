<?php
session_start();
require 'config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar à base de dados: " . $e->getMessage());
}

// Verifica se o parâmetro codcarrinho foi passado
if (isset($_GET['codcarrinho'])) {
    $codcarrinho = $_GET['codcarrinho'];

    // Obtém os detalhes do artigo no carrinho antes de removê-lo
    $stmt = $pdo->prepare("SELECT quant FROM carrinho WHERE codcarrinho = :codcarrinho");
    $stmt->bindParam(':codcarrinho', $codcarrinho, PDO::PARAM_INT);
    $stmt->execute();
    $artigo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($artigo) {
        $quantidadeRemovida = (int) $artigo['quant'];

        // Remove o artigo do carrinho
        $stmt = $pdo->prepare("DELETE FROM carrinho WHERE codcarrinho = :codcarrinho");
        $stmt->bindParam(':codcarrinho', $codcarrinho, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Artigo removido do carrinho.";

            // Atualiza o contador de artigos no carrinho
            $_SESSION['cart_count'] -= $quantidadeRemovida;
        } else {
            $_SESSION['message'] = "Erro ao remover o artigo do carrinho.";
        }
    } else {
        $_SESSION['message'] = "Artigo não encontrado no carrinho.";
    }
} else {
    $_SESSION['message'] = "Erro: Código do carrinho não fornecido.";
}

// Redireciona para a página do carrinho
header('Location: ver_carrinho.php');
exit();
?>
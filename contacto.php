<?php
session_start();
require 'config.php';

$codcliente = $_SESSION['codcliente'] ?? null;
$_SESSION['cart_count'] = $_SESSION['cart_count'] ?? 0;

if ($codcliente !== null) {
    $stmt = $pdo->prepare("SELECT SUM(quant) as total FROM carrinho WHERE codcliente = :codcliente");
    $stmt->bindParam(':codcliente', $codcliente, PDO::PARAM_INT);
    $stmt->execute();
    $_SESSION['cart_count'] = $stmt->fetchColumn() ?? 0;
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Contactos</title>
    <link rel="icon" href="img/novo-isotipo.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap + CSS personalizado -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css"> <!-- garante que o CSS que mostraste está aqui -->

    <style>
        .form-contato {
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .form-contato label {
            font-weight: bold;
        }

        .form-contato button {
            width: 100%;
        }

        footer {
            margin-top: 60px;
            text-align: center;
            color: #888;
        }
    </style>
</head>
<body>

    <header>
        <nav>
            <a href="compras.php">Home</a>
            <a href="somos.html">Quem somos?</a>
            <a href="logout.php" class="sair">Sair</a>
        </nav>
    </header>

    <h2>Fale connosco</h2>
    
    <form class="form-contato" method="POST" action="#">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome:</label>
            <input type="text" id="nome" name="nome" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">E-mail:</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="mensagem" class="form-label">Mensagem:</label>
            <textarea id="mensagem" name="mensagem" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit">Enviar</button>
    </form>

    <footer>
        &copy; 2025 Basic Prints. Todos os direitos reservados.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

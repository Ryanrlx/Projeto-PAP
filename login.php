<?php
require 'config.php'; // Arquivo de conexão com o banco de dados

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        $erro = "Cliente não encontrado. Por favor, registre seu email.";
    } else {
        if (password_verify($senha, $cliente['senha'])) {
            $_SESSION['codcliente'] = $cliente['codcliente'];
            $_SESSION['nome'] = $cliente['nome'];
            $_SESSION['email'] = $email;
            $_SESSION['morada'] = $cliente['morada'];
            $_SESSION['codpostal'] = $cliente['codpostal'];
            $_SESSION['localidade'] = $cliente['localidade'];
            $_SESSION['numfiscal'] = $cliente['numfiscal'];
            $_SESSION['contacto'] = $cliente['contacto'];

            $sessionId = session_id();

            //faz com que ao entrar no site o carrinho esteja vazio
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM carrinho WHERE session_id = :session_id AND codcliente IS NULL"); 
            $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
            $stmt->execute();

            $countItems = $stmt->fetchColumn();

            if ($countItems > 0) {
                $stmt = $pdo->prepare("UPDATE carrinho SET codcliente = :codcliente WHERE session_id = :session_id AND codcliente IS NULL");
                $stmt->bindParam(':codcliente', $codcliente, PDO::PARAM_INT);
                $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
                $stmt->execute();
            }

            header("Location: compras.php");
            exit;
        } else {
            $erro = "Senha incorreta. Tente novamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" href="img/novo-isotipo.png" type="logo-site">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .login {
            background-color: #9160be;
            color: white;
            border-radius: 10px;
            padding: 0 9px;
        }

        body {
            font-family: "Kanit", sans-serif;
            background-color: #f4f4f4;
            padding: 100px 20px 20px 20px;
            color: #333;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-container {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        form {
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        form input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .btn-login {
            width: 100%;
            padding: 10px;
            background-color: blueviolet;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-login:hover {
            background-color: #9160be;
        }

        .btn-register {
            color: blueviolet;
            font-weight: bold;
            text-decoration: none;
        }

        .btn-register:hover {
            opacity: 0.7;
        }

        .btn-voltar {
            color: blueviolet;
            font-weight: bold;
            text-decoration: none;
        }

        .btn-voltar:hover {
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="email" class="form-label">Email:</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="senha" class="form-label">Senha:</label>
                <input type="password" name="senha" id="senha" class="form-control" required>
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <p class="mt-3 text-center">Ainda não tem conta? <a href="registo.php" class="btn-register">Registe-se aqui</a></p>
    </div>
</body>
</html>

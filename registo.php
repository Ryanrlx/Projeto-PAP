<?php
require 'config.php'; // ficheiro de ligação à base de dados

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $clienteExistente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($clienteExistente) {
        $erro = "O email já está registado!";
    } else {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, senha) VALUES (:nome, :email, :senha)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senhaHash);

        if ($stmt->execute()) {
            header("Location: login.php");
            exit;
        } else {
            $erro = "Erro ao registar o cliente!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registo</title>
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

        .form-group, .mb-3 {
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
        <h2>Registe-se</h2>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome:</label>
                <input type="text" name="nome" id="nome" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label for="senha" class="form-label">Senha:</label>
                <input type="password" name="senha" id="senha" class="form-control" required>
            </div>

            <button type="submit" class="btn-login">Registar</button>
        </form>

        <p class="mt-3 text-center">Já tem conta? <a href="login.php" class="btn-register">Faça login aqui</a></p>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Detalhes do Artigo</title>
    <link rel="icon" href="img/novo-isotipo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f4f4f4;
            padding: 40px 20px;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #4b0082;
            margin-bottom: 30px;
        }

        form {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #4b0082;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        img {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            background-color: #4b0082;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }

        a:hover {
            background-color: #6a1bb5;
        }

        .text-danger {
            color: red;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Detalhes do Artigo</h1>

    <?php
    // Configuração da conexão com a base de dados
    $host = 'localhost';
    $dbname = '3m-t2';
    $user = 'root';
    $password = '';

    if (isset($_GET['codartigo'])) {
        $codartigo = htmlspecialchars($_GET['codartigo']);

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Consulta para buscar os detalhes do artigo
            $sql = "SELECT descricao, pvp, qstock, imagem FROM artigos WHERE codartigo = :codartigo";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':codartigo', $codartigo, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $artigo = $stmt->fetch(PDO::FETCH_ASSOC);
                ?>

                <form>
                    <label for="codartigo">Código do Artigo:</label>
                    <input type="text" id="codartigo" value="<?= htmlspecialchars($codartigo); ?>" readonly>

                    <label for="descricao">Descrição:</label>
                    <input type="text" id="descricao" value="<?= htmlspecialchars($artigo['descricao']); ?>" readonly>

                    <label for="pvp">Preço de Venda (€):</label>
                    <input type="text" id="pvp" value="<?= number_format($artigo['pvp'], 2, ',', '.'); ?>" readonly>

                    <label for="qstock">Quantidade em Stock:</label>
                    <input type="text" id="qstock" value="<?= (int)$artigo['qstock']; ?>" readonly>

                    <?php if (!empty($artigo['imagem'])): ?>
                        <label for="imagem">Imagem:</label>
                        <img src="data:image/jpeg;base64,<?= base64_encode($artigo['imagem']); ?>" alt="Imagem do Artigo">
                    <?php else: ?>
                        <p><strong>Imagem:</strong> Não disponível</p>
                    <?php endif; ?>

                    <a href="index.html">Voltar para o início</a>
                </form>

                <?php
            } else {
                echo "<p class='text-danger'>Artigo não encontrado.</p>";
            }
        } catch (PDOException $e) {
            echo "<p class='text-danger'>Erro ao carregar os detalhes do artigo: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='text-danger'>Código do artigo não fornecido.</p>";
    }
    ?>
</body>
</html>

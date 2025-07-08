<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Detalhes do Artigo</title>
    <link rel="stylesheet" href="detalhes_artigo.css">
    <link rel="icon" href="img/novo-isotipo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <h1>Detalhes do Artigo</h1>

    <?php
    

    if (isset($_GET['codartigo'])) {
        $codartigo = htmlspecialchars($_GET['codartigo']);

        try {
            // Configuração da conexão com a base de dados
            require "config.php";

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

                    <a href="compras.php">Voltar para o início</a>
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

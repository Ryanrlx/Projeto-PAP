<?php

require 'config.php';

session_start();

if (isset($_SESSION['codcliente'])) {
    $codcliente = $_SESSION['codcliente'];
} else {
    $codcliente = null; // Usuário anônimo
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar à base de dados: " . $e->getMessage());
}


// Verifica se o carrinho está definido e não está vazio
if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0) {
    // Consulta os itens do carrinho
   $sql = "SELECT c.codcarrinho, a.codartigo, a.descricao, a.pvp, c.quant
        FROM carrinho c
        JOIN artigos a ON c.codartigo = a.codartigo
        WHERE (c.codcliente = :codcliente AND c.codcliente IS NOT NULL)
        OR (c.session_id = :session_id AND c.codcliente IS NULL)";

    $stmt = $pdo->prepare($sql);

    // Cria uma variável para o session_id
    $session_id = session_id();
    $stmt->bindParam(':session_id', $session_id, PDO::PARAM_STR);  // Passa a variável para bindParam
    $stmt->bindParam(':codcliente', $codcliente, PDO::PARAM_INT);  // codcliente, caso haja um cliente logado
    
	$stmt->execute();
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcula o total do carrinho
    $totalCarrinho = 0;
    foreach ($rows as $row) {
           $total = $row['pvp'] * $row['quant'];
           $totalCarrinho += $total;
    }

    // Armazena o total do carrinho na sessão
    $_SESSION['totalCarrinho'] = $totalCarrinho;

} else {
    $_SESSION['message'] = "O carrinho está vazio.";
    header('Location: compras.php'); // Redireciona para a página inicial ou listagem de artigos
    exit();
}


?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Carrinho</title>
    <link rel="icon" href="img/novo-isotipo.png" type="logo-site">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 20px;
        }

        h1 {
            margin-bottom: 20px;
        }

        .btn-group a {
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="text-center">Carrinho de Compras</h1>
        <?php if (isset($_SESSION['message'])) : ?>
            <div class="alert alert-info"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Descrição</th>
                    <th>Preço (€)</th>
                    <th>Quantidade</th>
                    <th>Total (€)</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                				
				$totalCarrinho = 0;
                if ($rows) {
                   foreach ($rows as $row) {
                       $total = $row['pvp'] * $row['quant'];
                       $totalCarrinho += $total;
                       echo "<tr>";
                       echo "<td>" . htmlspecialchars($row['descricao']) . "</td>";
                       echo "<td>" . number_format($row['pvp'], 2, ',', '.') . "</td>";
                       echo "<td>" . (int)$row['quant'] . "</td>";
                       echo "<td>" . number_format($total, 2, ',', '.') . "</td>";
                       echo "<td>
                       <a href='eliminar_artigo.php?codcarrinho=" . urlencode($row['codcarrinho']) . "' class='btn btn-danger'>Eliminar Artigo</a>
                      </td>";
                   echo "</tr>";
                  }
            } else {
                  echo "<tr><td colspan='5' class='text-center'>Carrinho vazio</td></tr>";
           }
				
	       ?>
            </tbody>
        </table>
        <h3>Total do Carrinho: <?= number_format($totalCarrinho, 2, ',', '.') ?> €</h3>
        
		<div class="btn-group">
             <a href="compras.php" class="btn btn-primary">Voltar</a>
             <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalClearCart">Limpar Carrinho</button>
             <a href="efetuar_compra.php" class="btn btn-success ms-2">Efetuar Compra</a> 
        </div>
    </div>

    <!-- Modal para confirmação de limpeza do carrinho -->
    <div class="modal fade" id="modalClearCart" tabindex="-1" aria-labelledby="modalClearCartLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalClearCartLabel">Confirmar Limpeza do Carrinho</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza de que deseja limpar o carrinho?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="limpar_carrinho.php" class="btn btn-danger">Limpar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
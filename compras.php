<?php

session_start();

if (!isset($_SESSION['cart_count'])) {
    $_SESSION['cart_count'] = 0;
}


// Verifica se o utilizador está logado e define o código do cliente
$codcliente = $_SESSION['codcliente'] ?? null; // Se não existir, será null

require 'config.php';

if ($codcliente !== null) {
    // Buscar a soma das quantidades (quant) para esse cliente no carrinho
    $stmt = $pdo->prepare("SELECT SUM(CAST(quant AS UNSIGNED)) as total FROM carrinho WHERE codcliente = :codcliente");
    $stmt->bindParam(':codcliente', $codcliente, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Atualizar $_SESSION['cart_count'] com a contagem real de artigos no carrinho
    $cart_total = $result['total'] ?? 0;
    $_SESSION['cart_count'] = $cart_total;
	

}

// Se o utilizador estiver logado, define o codcliente corretamente
$codcliente = $_SESSION['codcliente'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codartigo'], $_POST['quantidade'])) {
    $codartigo = $_POST['codartigo'];
    $quantidade = (int)$_POST['quantidade'];

    // Obter o preço e o stock do artigo
    $stmt = $pdo->prepare("SELECT pvp, qstock FROM artigos WHERE codartigo = :codartigo");
    $stmt->bindParam(':codartigo', $codartigo, PDO::PARAM_STR);
    $stmt->execute();
    $artigo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($artigo) {
        $pvp = $artigo['pvp'];
        $qstock = (int)$artigo['qstock'];
        $sessionId = session_id();
		
	   // Verificar quantidade atual no carrinho
       if ($codcliente !== null) {
          $stmt = $pdo->prepare("SELECT quant FROM carrinho WHERE codartigo = :codartigo AND codcliente = :codcliente");
          $stmt->bindParam(':codartigo', $codartigo, PDO::PARAM_STR);
          $stmt->bindParam(':codcliente', $codcliente, PDO::PARAM_INT);
       } else {
         $stmt = $pdo->prepare("SELECT quant FROM carrinho WHERE codartigo = :codartigo AND session_id = :session_id AND codcliente IS NULL");
         $stmt->bindParam(':codartigo', $codartigo, PDO::PARAM_STR);
         $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
        }

        $stmt->execute();
        $itemExiste = $stmt->fetch(PDO::FETCH_ASSOC);
        $quantidadeAtualNoCarrinho = $itemExiste ? (int)$itemExiste['quant'] : 0;

      // Verificar se a soma da quantidade adicionada com a já existente no carrinho ultrapassa o stock
      if (($quantidadeAtualNoCarrinho + $quantidade) > $qstock) {
            $_SESSION['message'] = "<div class='alert alert-danger'>Erro: A quantidade deste artigo adicionada é superior ao valor do stock do artigo ($qstock unidades).</div>";
      } else {
            if ($codcliente !== null) {
                // Verificar se o item já existe no carrinho
                $stmt = $pdo->prepare("SELECT quant FROM carrinho WHERE codartigo = :codartigo AND codcliente = :codcliente");
                $stmt->bindParam(':codartigo', $codartigo, PDO::PARAM_STR);
                $stmt->bindParam(':codcliente', $codcliente, PDO::PARAM_INT);
            } else {
                // Se não houver cliente autenticado, verifica se o item existe na sessão
                $stmt = $pdo->prepare("SELECT quant FROM carrinho WHERE codartigo = :codartigo AND session_id = :session_id AND codcliente IS NULL");
                $stmt->bindParam(':codartigo', $codartigo, PDO::PARAM_STR);
                $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
            }

            $stmt->execute();
            $itemExiste = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($itemExiste) {
                // Atualiza a quantidade do item existente
                if ($codcliente !== null) {
                    $stmt = $pdo->prepare("UPDATE carrinho SET quant = quant + :quant WHERE codartigo = :codartigo AND codcliente = :codcliente");
                    $stmt->bindParam(':codcliente', $codcliente, PDO::PARAM_INT);
                } else {
                    $stmt = $pdo->prepare("UPDATE carrinho SET quant = quant + :quant WHERE codartigo = :codartigo AND session_id = :session_id AND codcliente IS NULL");
                    $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
                }
                $stmt->bindParam(':codartigo', $codartigo, PDO::PARAM_STR);
                $stmt->bindParam(':quant', $quantidade, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                // Insere um novo item no carrinho
                $stmt = $pdo->prepare("INSERT INTO carrinho (codartigo, pvp, quant, session_id, codcliente) 
                                       VALUES (:codartigo, :pvp, :quant, :session_id, :codcliente)");
                $stmt->bindParam(':codartigo', $codartigo, PDO::PARAM_STR);
                $stmt->bindParam(':pvp', $pvp, PDO::PARAM_STR);
                $stmt->bindParam(':quant', $quantidade, PDO::PARAM_INT);
                $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
                $stmt->bindValue(':codcliente', $codcliente, $codcliente !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                $stmt->execute();
            }

           // Atualizar a contagem de itens no carrinho
            if ($codcliente !== null) {
                $stmt = $pdo->prepare("SELECT SUM(quant) as total FROM carrinho WHERE codcliente = :codcliente");
                $stmt->bindParam(':codcliente', $codcliente, PDO::PARAM_INT);
            } else {
                $stmt = $pdo->prepare("SELECT SUM(quant) as total FROM carrinho WHERE session_id = :session_id AND codcliente IS NULL");
                $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
            }

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['cart_count'] = $result['total'] ?? 0;
        }
    }
}

    

       

      
?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listagem de Artigos</title>
    <link rel="icon" href="img/novo-isotipo.png" type="logo-site">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
      <header>
        <nav>
            <a href="#">Home</a>
            <a href="somos.html">Quem somos?</a>
            <a href="logout.php" class="sair">Sair</a>
        </nav>
    </header>
<div class="caixa carrossel">
    <button class="btn-anterior"></button>
    <div class="slides">
        <img class="img" src="img/carrossel.jpg" alt="Slide 1">
        <img class="img2" src="img/carrossel2.jpg" alt="Slide 2">
    </div>
    <button class="btn-proximo"></button>
</div>

<main>
        <h2>Lançamentos</h2>
      
    <style>

@import url('https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: "Kanit", sans-serif;
    background-color: #f4f4f4;
    padding: 100px 20px 20px 20px; 
    color: #333;
}

nav {
    display: flex;
    justify-content: flex-end;
    align-items: center;
}

.sair{
    color: rgb(201, 30, 30);
} 
nav a:hover.sair{
    color: red;
}

nav a {
    margin-right: 15px;
    padding-left: 20px;
    text-decoration: none;
    font-size: 20px;
    font-family: "Kanit", sans-serif;
    color: black;
    transition: all 0.3s ease;
}

nav a:hover {
    opacity: 0.7;
    font-size: 23px;
}

nav a:active {
    opacity: 0.5;
}

header {
    position: fixed;
    top: 0;
    right: 0;
    width: 100%;
    padding: 20px;
    background-color: #f4f4f4;
    z-index: 3;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.carrossel {
    position: relative;
    width: 1320px;
    height: 500px;
    margin: 20px auto;
    overflow: hidden;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.carrossel .slides {
    display: flex;
    transition: transform 0.5s ease-in-out;
    width: calc(1320px * 2); /* 3 imagens */
}

.carrossel img {
    width: 1500px;
    height: 500px;
    object-fit: cover;
    flex-shrink: 0;
}

.carrossel button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    padding: 12px;
    font-size: 28px;
    cursor: pointer;
    z-index: 10;
    border-radius: 50%;
}

.carrossel .btn-anterior {
    left: 15px;
}

.carrossel .btn-proximo {
    right: 15px;
}

.detalhe{
    display: inline-block;
    margin-top: 10px;
    padding: 10px 15px;
    background-color: rgb(176, 81, 231);
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

.detalhe:hover {
    background-color: rgb(150, 35, 216);
}


button {
    margin-top: 10px;
    padding: 10px 15px;
    background-color: rgb(176, 81, 231);
    color: white;
    border: none;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: rgb(150, 35, 216);
}

h2 {
    text-align: center;
    padding-top: 150px;
}

.container {
    max-width: 1200px;
    margin: auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    padding: 10px;
}

.produto {
    background: white;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
}

.produto:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.produto img {
    width: 100%;
    border-radius: 10px;
    height: auto;
    object-fit: cover;
}

.produto h3 {
    margin: 10px 0;
    font-size: 20px;
}

.preco {
    font-size: 16px;
    color: #27ae60;
    font-weight: bold;
}


        .produto a.detalhe,
.produto button.botao {
    display: inline-block;
    margin: 5px 5px 0 5px;
}


        .container {
            margin-top: 20px;
        }


        .btn-group a {
            margin-right: 10px;
        }

        .cart {
            position: fixed;
            z-index: 6;
            top: 100px;
            right: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #fff;
            padding: 10px 15px;
            border-radius: 30px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .cart .cart-icon {
            font-size: 24px;
            color: #4CAF50;
            margin-bottom: 5px;
        }

        .cart span {
            font-weight: bold;
            color: #555;
        }

        .cart a {
            margin-top: 5px;
            font-size: 14px;
            text-decoration: none;
            color: #4CAF50;
        }

        .cart a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <!-- Carrinho no canto superior direito -->
    <div class="cart">
        <i class="bi bi-cart-fill cart-icon"></i>
        <span><?= $_SESSION['cart_count']; ?> artigos adicionados</span>
        <a href="ver_carrinho.php">Ver carrinho</a>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['message'])) : ?>
            <div class="alert alert-info"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <section class="container fade-in">
    <?php
    $sql = "SELECT codartigo, descricao, pvp, qstock, imagem FROM artigos";
    $stmt = $pdo->query($sql);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<div class="produto">';

        // Imagem
        if ($row['imagem']) {
            $imgData = base64_encode($row['imagem']);
            echo "<img src='data:image/jpeg;base64,{$imgData}' alt='Imagem do Produto' style='height: 250px; object-fit: cover;'>";
        } else {
            echo "<div style='height: 250px; background: #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center;'>Sem Imagem</div>";
        }

        // Título e preço
        echo "<h3>" . htmlspecialchars($row['descricao']) . "</h3>";
        echo "<p class='preco'>€" . number_format($row['pvp'], 2, ',', '.') . "</p>";

        // Botões
        echo "<button class='button' data-bs-toggle='modal' data-bs-target='#modalAddToCart' data-codartigo='" . htmlspecialchars($row['codartigo']) . "'>Adicionar</button>";
        echo "<a href='detalhes_artigo.php?codartigo=" . urlencode($row['codartigo']) . "' class='detalhe'>Detalhe</a>";
        echo "</div>";
    }
    ?>
</section>

    </div>

    <!-- Modal para Adicionar ao Carrinho -->
    <div class="modal fade" id="modalAddToCart" tabindex="-1" aria-labelledby="modalAddToCartLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAddToCartLabel">Adicionar ao Carrinho</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="codartigo" id="modalCodArtigo">
                        <label for="quantidade" class="form-label">Quantidade:</label>
                        <input type="number" name="quantidade" id="modalQuantidade" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
                const slides = document.querySelector('.carrossel .slides');
    const imagens = slides.querySelectorAll('img');
    let index = 0;

    document.querySelector('.btn-proximo').addEventListener('click', () => {
        index = (index + 1) % imagens.length;
        atualizarSlide();
    });

    document.querySelector('.btn-anterior').addEventListener('click', () => {
        index = (index - 1 + imagens.length) % imagens.length;
        atualizarSlide();
    });

    function atualizarSlide() {
        slides.style.transform = `translateX(-${index * 1500}px)`;
    }
        // Passar o código do artigo para o modal
        var modalAddToCart = document.getElementById('modalAddToCart');
        modalAddToCart.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var codartigo = button.getAttribute('data-codartigo');
            var inputCodArtigo = modalAddToCart.querySelector('#modalCodArtigo');
            inputCodArtigo.value = codartigo;
        });
    </script>
</body>

</html>
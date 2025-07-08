<?php

require 'config.php';
session_start();

if (!isset($_SESSION['codcliente'])) {
    header("Location: login.php");
    exit;
}

// Obtém a data e hora atuais do sistema
$dataHoraAtual = date('d/m/Y H:i:s');

// Obtém o total do carrinho vindo da sessão (caso esteja definido)
$totalCarrinho = isset($_SESSION['totalCarrinho']) ? $_SESSION['totalCarrinho'] : 0;

$nomeCliente = isset($_SESSION['nome']) ? $_SESSION['nome'] : '';
$emailCliente = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$moradaCliente = isset($_SESSION['morada']) ? $_SESSION['morada'] : '';
$codpostalCliente = isset($_SESSION['codpostal']) ? $_SESSION['codpostal'] : '';
$localidadeCliente = isset($_SESSION['localidade']) ? $_SESSION['localidade'] : '';
$numfiscalCliente = isset($_SESSION['numfiscal']) ? $_SESSION['numfiscal'] : '';
$contactoCliente = isset($_SESSION['contacto']) ? $_SESSION['contacto'] : '';

?>

    
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/novo-isotipo.png" type="logo-site">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 20px;
        }

        h2 {
            margin-bottom: 20px;
            color: black;
        }

        .btn-group a {
            margin-right: 10px;
        }
		
	    input.form-control {
            font-weight: bold;
            color: #d9534f;
        }
		
				
    </style>
</head>

<body>

<div class="container">

       <div class="d-flex justify-content-between align-items-center mb-4 p-3 border rounded shadow-sm" style="background-color: #f8f9fa;">
    <span style="font-size: 1.5rem; color: #333;">
        <strong>Data e Hora:</strong> <?php echo $dataHoraAtual; ?>
    </span>
    <span style="font-size: 2rem; font-weight: bold; color: #d9534f;">
        <strong>Total da Compra:</strong> <?php echo number_format($totalCarrinho, 2, ',', '.'); ?> €
    </span>
	
</div>
        
<h2>Dados do cliente que está a efetuar a compra</h2>

<form action="processar_compra.php" method="post">
 <!-- area dos campos do formulario -->
 <hr />
 
 <div class="row">
 
 <div class="form-group col-md-6">
 <label for="nome">Nome</label>
 <input type="text" class="form-control" name='tnome' id="tnome" value="<?= htmlspecialchars($nomeCliente); ?>" required>
 </div>
 <div class="form-group col-md-2">
 <label for="nif">Núm. Fiscal</label>
 <input type="text" class="form-control" name='tnumfiscal' id="tnumfiscal" value="<?= htmlspecialchars($numfiscalCliente); ?>" required>
 </div>
 <div class="form-group col-md-2">
 <label for="contacto">Contacto</label>
 <input type="text" class="form-control" name='tcontacto' id="tcontacto" value="<?= htmlspecialchars($contactoCliente); ?>" required>
 </div>
 
 </div>

 
 <div class="row">
            <div class="form-group col-md-6">
                <label for="morada">Morada</label>
                <input type="text" class="form-control" name='tmorada' id="tmorada" value="<?= htmlspecialchars($moradaCliente); ?>" required>
            </div>
            <div class="form-group col-md-2">
                <label for="cp">Cód. Postal</label>
                <input type="text" class="form-control" name='tcodpostal' id="tcodpostal" value="<?= htmlspecialchars($codpostalCliente); ?>" required>
            </div>
            <div class="form-group col-md-3">
                <label for="localidade">Localidade</label>
                <input type="text" class="form-control" name='tlocalidade' id="tlocalidade" value="<?= htmlspecialchars($localidadeCliente); ?>" required>
            </div>
 </div>
 
 
 <!-- Campo de E-mail -->
<div class="form-group col-md-6">
    <label for="cidade">E-Mail</label>
    <input type="email" class="form-control" name="temail" id="temail" value="<?= htmlspecialchars($emailCliente); ?>" required>
    <small id="emailError" class="text-danger" style="display: none;">E-mail inválido!</small>
</div>
 
 
 
 <h2>Local de entrega da mercadoria</h2>
 
  <!-- Checkbox para copiar os dados -->
  <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="usarMesmoEndereco">
            <label class="form-check-label" for="usarMesmoEndereco">
            Usar os mesmos dados do cliente, inseridos anteriormente?
            </label>
  </div>
 
 <div class="row">
            <div class="form-group col-md-6">
                <label for="morada">Morada</label>
                <input type="text" class="form-control" name='tmoradaent' id="tmoradaent" required>
            </div>
            <div class="form-group col-md-2">
                <label for="local">Cód. Postal</label>
                <input type="text" class="form-control" name='tcodpostalent' id="tcodpostalent" required>
            </div>
            <div class="form-group col-md-3">
                <label for="cp">Localidade</label>
                <input type="text" class="form-control" name='tlocalidadeent' id="tlocalidadeent" required>
            </div>
 </div>


  <div id="actions" class="row mt-3">
        <div class="col-md-12">
		    <a href="ver_carrinho.php" class="btn btn-primary">Voltar para o carrinho</a>
            <button type="submit" class="btn btn-success">Gravar compra</button>
        </div>
  </div>
 
 
</form>

</div>


<script>

<!-- JavaScript para validações -->

  // Função para validar e-mail com regex
    function validarEmail(email) {
        const regexEmail = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return regexEmail.test(email);
    }

    document.getElementById("temail").addEventListener("blur", function () {
        const emailInput = this.value.trim();
        const emailError = document.getElementById("emailError");

        if (emailInput === "" || validarEmail(emailInput)) {
            emailError.style.display = "none";  // Esconde a mensagem se estiver vazio ou válido
            this.classList.remove("is-invalid"); // Remove a classe de erro
        } else {
            emailError.style.display = "block"; // Exibe a mensagem se for inválido
            this.classList.add("is-invalid"); // Adiciona classe para destacar erro
            this.focus(); // Mantém o foco no campo até o utilizador corrigir
        }
    });
	

    // Restringir campos para aceitar apenas números
    document.getElementById("tnumfiscal").addEventListener("input", function () {
        this.value = this.value.replace(/\D/g, ''); // Remove qualquer caractere que não seja número
    });

    document.getElementById("tcontacto").addEventListener("input", function () {
        this.value = this.value.replace(/\D/g, ''); // Apenas números
    });

    // Validação do formato do código postal
    document.getElementById("tcodpostal").addEventListener("input", function () {
        this.value = this.value.replace(/[^0-9\-]/g, ''); // Apenas números e hífen
        if (this.value.length > 8) {
            this.value = this.value.slice(0, 8); // Limita o comprimento a 8 caracteres
        }
        if (/^\d{4}$/.test(this.value)) {
            this.value += "-"; // Adiciona hífen automaticamente após 4 números
        }
    });
	
	// Validação do formato do código postal de entrega da mercadoria
    document.getElementById("tcodpostalent").addEventListener("input", function () {
        this.value = this.value.replace(/[^0-9\-]/g, ''); // Apenas números e hífen
        if (this.value.length > 8) {
            this.value = this.value.slice(0, 8); // Limita o comprimento a 8 caracteres
        }
        if (/^\d{4}$/.test(this.value)) {
            this.value += "-"; // Adiciona hífen automaticamente após 4 números
        }
    });
	

<!-- JavaScript para copiar os dados -->

    document.getElementById("usarMesmoEndereco").addEventListener("change", function() {
        if (this.checked) {
            document.getElementById("tmoradaent").value = document.getElementById("tmorada").value;
            document.getElementById("tcodpostalent").value = document.getElementById("tcodpostal").value;
            document.getElementById("tlocalidadeent").value = document.getElementById("tlocalidade").value;
        } else {
            document.getElementById("tmoradaent").value = "";
            document.getElementById("tcodpostalent").value = "";
            document.getElementById("tlocalidadeent").value = "";
        }
    });
</script>

<!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
	
</body>

</html>


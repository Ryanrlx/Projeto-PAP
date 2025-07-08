<?php
// Configuração da conexão com a base de dados
$host = 'localhost';
$dbname = 'basic';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar à base de dados: " . $e->getMessage());
}
?>




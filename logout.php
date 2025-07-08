<?php
session_start();
session_destroy(); // Destrói a sessão do utilizador
header("Location: login.php"); // Redireciona para login
exit;
?>


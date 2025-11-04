<?php
session_start();

// Limpa todas as variáveis da sessão
$_SESSION = [];

// Remove o cookie de sessão, se existir
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroi a sessão
session_destroy();

// Redireciona imediatamente para a tela de login
header("Location: login.php");
exit;
?>

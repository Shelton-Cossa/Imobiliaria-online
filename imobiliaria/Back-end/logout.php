<?php
session_start();

// Remove todas as variáveis de sessão
$_SESSION = array();

// Se desejar destruir o cookie da sessão (recomendado)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Finalmente, destrói a sessão
session_destroy();

// Redireciona para a página de login (ou início)
header("Location: ../Paginas/login.php");
exit;
?>
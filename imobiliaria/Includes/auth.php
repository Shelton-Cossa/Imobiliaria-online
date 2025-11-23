<?php
// Includes/auth.php
if (session_status() === PHP_SESSION_NONE) session_start();

function protegerPagina() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../Paginas/login.php");
        exit;
    }
}
?>
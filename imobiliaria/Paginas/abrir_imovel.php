<?php
session_start();

// Verifica se o ID foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: inicio.php");
    exit();
}

$id_imovel = (int) $_GET['id'];

// Gera token único e armazena na sessão
$token = bin2hex(random_bytes(16));
$_SESSION['imovel_tokens'][$token] = $id_imovel;

// Redireciona para detalhes usando o token
header("Location: detalhes_imovel.php?token=" . urlencode($token));
exit();
?>
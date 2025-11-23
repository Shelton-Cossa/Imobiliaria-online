<?php
// Paginas/abrir_imovel.php
session_start();
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: inicio.php");
    exit();
}
$id_imovel = (int) $_GET['id'];
$token = bin2hex(random_bytes(16));
if (!isset($_SESSION['imovel_tokens']))
    $_SESSION['imovel_tokens'] = [];
$_SESSION['imovel_tokens'][$token] = $id_imovel;
// opcional: limitar tempo de validade (não implementado aqui)
header("Location: detalhes_imovel.php?token=" . urlencode($token));
exit;
?>
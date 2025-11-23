<?php
session_start();
require_once "../Includes/conexao.php";
require_once "../Includes/auth.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];
$conversa_id = (int) $_POST['conversa_id'];
$mensagem = trim($_POST['mensagem']);

if ($mensagem === "")
    exit;

$stmt = $conn->prepare("INSERT INTO mensagens (conversa_id, remetente_id, mensagem) VALUES (:c, :u, :m)");
$stmt->execute([
    ':c' => $conversa_id,
    ':u' => $usuario_id,
    ':m' => $mensagem
]);

echo '
<div class="mensagem enviada">
    <p>' . nl2br(htmlspecialchars($mensagem)) . '</p>
    <span class="hora">' . date("H:i") . '</span>
</div>';

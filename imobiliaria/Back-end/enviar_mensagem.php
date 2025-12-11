<?php
session_start();
require_once "../Includes/conexao.php";
require_once "../Includes/auth.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];
$conversa_id = (int)($_POST['conversa_id'] ?? 0);
$mensagem = trim($_POST['mensagem'] ?? '');

if ($conversa_id <= 0 || $mensagem === '') {
    header("Location: ../Paginas/mensagens.php");
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO mensagens (conversa_id, remetente_id, mensagem, data_envio)
                            VALUES (:cid, :rid, :msg, NOW())");
    $stmt->execute([
        ':cid' => $conversa_id,
        ':rid' => $usuario_id,
        ':msg' => $mensagem
    ]);

    // ✅ Após enviar, redireciona para a conversa atual
    header("Location: ../Paginas/mensagens.php?conversa_id=" . $conversa_id);
    exit;

} catch (PDOException $e) {
    error_log("Erro ao enviar mensagem: " . $e->getMessage());
    header("Location: ../Paginas/mensagens.php");
    exit;
}

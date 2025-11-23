<?php
session_start();
require_once "../Includes/conexao.php";
require_once "../Includes/auth.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];
$destinatario_id = isset($_GET['destinatario']) ? (int) $_GET['destinatario'] : 0;

// ⚠️ Impedir conversa consigo mesmo
if ($destinatario_id === $usuario_id || $destinatario_id <= 0) {
    $_SESSION['mensagem_erro'] = "Não é possível iniciar conversa inválida.";
    header("Location: ../Paginas/inicio.php");
    exit;
}

try {
    // 🔍 Verifica se já existe conversa entre os dois
    $stmt = $conn->prepare("
        SELECT id FROM conversas
        WHERE (usuario1_id = :u1 AND usuario2_id = :u2)
           OR (usuario1_id = :u2 AND usuario2_id = :u1)
        LIMIT 1
    ");
    $stmt->execute([':u1' => $usuario_id, ':u2' => $destinatario_id]);
    $conversa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($conversa) {
        // ✅ Já existe — redireciona
        $conversa_id = $conversa['id'];
    } else {
        // 🆕 Cria nova conversa
        $stmt = $conn->prepare("
            INSERT INTO conversas (usuario1_id, usuario2_id, data_criacao)
            VALUES (:u1, :u2, NOW())
        ");
        $stmt->execute([':u1' => $usuario_id, ':u2' => $destinatario_id]);
        $conversa_id = $conn->lastInsertId();
    }

    // 🔁 Redireciona para mensagens.php com a conversa carregada
    header("Location: ../Paginas/mensagens.php?conversa_id=" . $conversa_id);
    exit;

} catch (PDOException $e) {
    error_log("Erro ao iniciar conversa: " . $e->getMessage());
    $_SESSION['mensagem_erro'] = "Erro ao iniciar conversa. Tente novamente.";
    header("Location: ../Paginas/inicio.php");
    exit;
}

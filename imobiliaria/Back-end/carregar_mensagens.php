<?php
session_start();
require_once "../Includes/conexao.php";
require_once "../Includes/auth.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];
$conversa_id = (int)($_GET['conversa_id'] ?? 0);

$stmt = $conn->prepare("
    SELECT m.*, u.nome, u.foto_perfil 
    FROM mensagens m
    JOIN usuarios u ON u.id = m.remetente_id
    WHERE m.conversa_id = :id
    ORDER BY m.data_envio ASC
");
$stmt->execute([':id' => $conversa_id]);
$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="chat-header">
    <h4>Conversa</h4>
</div>

<div id="chat-mensagens" class="chat-mensagens">
    <?php foreach ($mensagens as $m): ?>
        <div class="mensagem <?= $m['remetente_id'] == $usuario_id ? 'enviada' : 'recebida' ?>">
            <p><?= nl2br(htmlspecialchars($m['mensagem'])) ?></p>
            <span class="hora"><?= date("H:i", strtotime($m['data_envio'])) ?></span>
        </div>
    <?php endforeach; ?>
</div>

<form id="formMensagem" class="chat-input">
    <input type="hidden" name="conversa_id" value="<?= $conversa_id ?>">
    <input type="text" id="textoMensagem" name="mensagem" placeholder="Escreva uma mensagem..." required>
    <button type="submit">Enviar</button>
</form>

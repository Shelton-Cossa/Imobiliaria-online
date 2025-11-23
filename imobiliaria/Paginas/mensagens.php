<?php
session_start();
require_once "../Includes/conexao.php";
require_once "../Includes/auth.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];

// 🔹 Buscar todas as conversas do usuário com nome e foto do outro participante
$stmt = $conn->prepare("
    SELECT 
        c.id AS conversa_id,
        u.id AS outro_id,
        u.nome AS outro_nome,
        u.foto_perfil AS outro_foto,
        (SELECT mensagem FROM mensagens WHERE conversa_id = c.id ORDER BY data_envio DESC LIMIT 1) AS ultima_msg
    FROM conversas c
    JOIN usuarios u ON (u.id = CASE WHEN c.usuario1_id = :id THEN c.usuario2_id ELSE c.usuario1_id END)
    WHERE c.usuario1_id = :id OR c.usuario2_id = :id
    ORDER BY c.id DESC
");
$stmt->execute([':id' => $usuario_id]);
$conversas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagens</title>
    <link rel="stylesheet" href="../Assets/CSS/mensagens.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>
    <?php include "../Includes/header.php"; ?>

    <main class="mensagens-container">
        <!-- Lista de conversas -->
        <aside class="lista-conversas">
            <h3>Conversas</h3>
            <?php if (empty($conversas)): ?>
                <p style="text-align:center; color:#777;">Nenhuma conversa iniciada.</p>
            <?php else: ?>
                <?php foreach ($conversas as $c): ?>
                    <div class="conversa" data-id="<?= $c['conversa_id'] ?>">
                        <img src="<?= !empty($c['outro_foto']) ? htmlspecialchars($c['outro_foto']) : '../uploads/perfil/default.jpg' ?>"
                            alt="Foto" class="avatar">
                        <div class="info-conversa">
                            <h4><?= htmlspecialchars($c['outro_nome']) ?></h4>
                            <p><?= htmlspecialchars($c['ultima_msg'] ?? 'Sem mensagens ainda...') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </aside>

        <!-- Janela de chat -->
        <section class="chat-area">
            <div id="chat-content">
                <p style="text-align:center; color:#777; margin-top:20px;">
                    Selecione uma conversa para começar.
                </p>
            </div>
        </section>
    </main>

    <script>
        // ====================
        // 🔁 Carregar mensagens
        // ====================
        $(document).on("click", ".conversa", function () {
            $(".conversa").removeClass("ativa");
            $(this).addClass("ativa");

            let id = $(this).data("id");
            $("#chat-content").load("../Back-end/carregar_mensagens.php?conversa_id=" + id);
        });

        // ====================
        // 📩 Enviar mensagem
        // ====================
        $(document).on("submit", "#formMensagem", function (e) {
            e.preventDefault();

            $.post("../Back-end/enviar_mensagem.php", $(this).serialize(), function (html) {
                $("#chat-mensagens").append(html);
                $("#textoMensagem").val("");
                $("#chat-mensagens").scrollTop($("#chat-mensagens")[0].scrollHeight);
            });
        });
    </script>

</body>

</html>
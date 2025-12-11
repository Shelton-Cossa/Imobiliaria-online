<?php
session_start();
require_once "../Includes/conexao.php";
require_once "../Includes/auth.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];
$conversa_id = isset($_GET['conversa_id']) ? (int) $_GET['conversa_id'] : 0;

// -----------------------------
// Função utilitária: resolve caminho de foto
// -----------------------------
function resolvePhotoPath(?string $dbPath): string
{
    // Caminho padrão
    $default = '../Uploads/perfil/default.jpg';

    if (empty($dbPath)) {
        return $default;
    }

    // Normaliza separadores
    $p = str_replace('\\', '/', trim($dbPath));

    // 1) Se não começa com ../, tenta com ../ prefixado
    if (strpos($p, '../') !== 0 && strpos($p, '/') !== 0) {
        $try = '../' . ltrim($p, './');
        if (file_exists($try))
            return $try;
    }

    // 2) Tenta usar tal qual (pode já ser relativo correto)
    if (file_exists($p))
        return $p;

    // 3) Tenta adicionar ../ se começar com "Uploads/..." sem ../
    if (strpos($p, 'Uploads/') === 0) {
        $try2 = '../' . $p;
        if (file_exists($try2))
            return $try2;
    }

    // 4) fallback para default
    return $default;
}

// ======================================
// 🧭 Busca todas as conversas do usuário
// ======================================
$stmt = $conn->prepare("
    SELECT 
        c.id AS conversa_id,
        u1.id AS u1_id, u1.nome AS u1_nome, u1.foto_perfil AS u1_foto,
        u2.id AS u2_id, u2.nome AS u2_nome, u2.foto_perfil AS u2_foto,
        m.mensagem AS ultima_msg,
        m.data_envio AS ultima_data
    FROM conversas c
    JOIN usuarios u1 ON c.usuario1_id = u1.id
    JOIN usuarios u2 ON c.usuario2_id = u2.id
    LEFT JOIN mensagens m ON m.id = (
        SELECT id FROM mensagens 
        WHERE conversa_id = c.id 
        ORDER BY data_envio DESC LIMIT 1
    )
    WHERE c.usuario1_id = :uid OR c.usuario2_id = :uid
    ORDER BY COALESCE(m.data_envio, c.data_criacao) DESC
");
$stmt->execute([':uid' => $usuario_id]);
$raw_conversas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processa conversas para determinar parceiro e photo path
$conversas = [];
foreach ($raw_conversas as $c) {
    if ((int) $c['u1_id'] === (int) $usuario_id) {
        $parceiro_id = $c['u2_id'];
        $parceiro_nome = $c['u2_nome'];
        $parceiro_foto_db = $c['u2_foto'];
    } else {
        $parceiro_id = $c['u1_id'];
        $parceiro_nome = $c['u1_nome'];
        $parceiro_foto_db = $c['u1_foto'];
    }

    $parceiro_foto_path = resolvePhotoPath($parceiro_foto_db);

    $conversas[] = [
        'conversa_id' => $c['conversa_id'],
        'parceiro_id' => $parceiro_id,
        'parceiro_nome' => $parceiro_nome,
        'parceiro_foto' => $parceiro_foto_path,
        'ultima_msg' => $c['ultima_msg'],
        'ultima_data' => $c['ultima_data']
    ];
}

// ======================================
// 💬 Carrega mensagens da conversa ativa
// ======================================
$mensagens = [];
$parceiro_nome = "Selecione uma conversa";
$parceiro_foto = '../Uploads/perfil/default.jpg';
$parceiro_id = null;
$info = null;

if ($conversa_id) {
    $stmt = $conn->prepare("
        SELECT 
            c.usuario1_id, c.usuario2_id,
            u1.nome AS nome1, u1.foto_perfil AS foto1,
            u2.nome AS nome2, u2.foto_perfil AS foto2
        FROM conversas c
        JOIN usuarios u1 ON c.usuario1_id = u1.id
        JOIN usuarios u2 ON c.usuario2_id = u2.id
        WHERE c.id = :cid
        LIMIT 1
    ");
    $stmt->execute([':cid' => $conversa_id]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($info && ($info['usuario1_id'] == $usuario_id || $info['usuario2_id'] == $usuario_id)) {
        // define parceiro
        if ($info['usuario1_id'] == $usuario_id) {
            $parceiro_nome = $info['nome2'];
            $parceiro_foto = resolvePhotoPath($info['foto2']);
            $parceiro_id = $info['usuario2_id'];
        } else {
            $parceiro_nome = $info['nome1'];
            $parceiro_foto = resolvePhotoPath($info['foto1']);
            $parceiro_id = $info['usuario1_id'];
        }

        // busca mensagens
        $stmt = $conn->prepare("
            SELECT remetente_id, mensagem, data_envio 
            FROM mensagens 
            WHERE conversa_id = :cid 
            ORDER BY data_envio ASC
        ");
        $stmt->execute([':cid' => $conversa_id]);
        $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // conversa não pertence ao utilizador -> ignora
        $conversa_id = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Mensagens</title>
    <link rel="stylesheet" href="../Assets/CSS/mensagens.css">
</head>

<body>

    <?php include "../Includes/header.php"; ?>

    <main class="mensagens-container">
        <!-- Lista de conversas -->
        <aside class="lista-conversas">
            <h3>Conversas</h3>
            <?php if (empty($conversas)): ?>
                <p class="sem-conversas">Sem conversas ainda.</p>
            <?php else: ?>
                <?php foreach ($conversas as $c): ?>
                    <a href="?conversa_id=<?= (int) $c['conversa_id'] ?>"
                        class="conversa <?= $conversa_id == $c['conversa_id'] ? 'ativa' : '' ?>">
                        <img src="<?= htmlspecialchars($c['parceiro_foto']) ?>" alt="Foto" class="avatar">
                        <div class="info-conversa">
                            <h4><?= htmlspecialchars($c['parceiro_nome']) ?></h4>
                            <p><?= htmlspecialchars($c['ultima_msg'] ?: 'Sem mensagens ainda...') ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </aside>

        <!-- Janela de chat -->
        <section class="chat-area">
            <?php if ($conversa_id && $info): ?>
                <div class="chat-header">
                    <img src="<?= htmlspecialchars($parceiro_foto) ?>" alt="Foto de perfil" class="avatar-header">
                    <h4><?= htmlspecialchars($parceiro_nome) ?></h4>
                </div>

                <div class="chat-mensagens">
                    <?php if (!empty($mensagens)): ?>
                        <?php foreach ($mensagens as $m): ?>
                            <div class="mensagem <?= ((int) $m['remetente_id'] === (int) $usuario_id) ? 'enviada' : 'recebida' ?>">
                                <p><?= nl2br(htmlspecialchars($m['mensagem'])) ?></p>
                                <span class="hora"><?= date('H:i', strtotime($m['data_envio'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="sem-mensagens">Nenhuma mensagem nesta conversa ainda.</p>
                    <?php endif; ?>
                </div>

                <form class="chat-input" action="../Back-end/enviar_mensagem.php" method="POST">
                    <input type="hidden" name="conversa_id" value="<?= $conversa_id ?>">
                    <input type="text" name="mensagem" placeholder="Escreva uma mensagem..." required>
                    <button type="submit">Enviar</button>
                </form>
            <?php else: ?>
                <div class="chat-placeholder">
                    <p>Selecione uma conversa à esquerda para começar a conversar.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

</body>

</html>
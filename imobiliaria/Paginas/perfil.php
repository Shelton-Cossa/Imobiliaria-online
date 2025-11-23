<?php
session_start();
require_once "../Includes/conexao.php";

// 🔒 Verifica login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// ======================================================
// 🧾 Buscar dados do usuário
// ======================================================
try {
    $stmt = $conn->prepare("SELECT nome, email, telefone, provincia, pais, foto_perfil 
                            FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $_SESSION['mensagem_erro'] = "Usuário não encontrado.";
        header("Location: inicio.php");
        exit();
    }

    $foto_perfil = !empty($usuario['foto_perfil']) ? "../" . $usuario['foto_perfil'] : "../Assets/Imagens/default_user.png";

    // ======================================================
    // 🏠 Buscar imóveis publicados pelo usuário
    // ======================================================
    $stmt = $conn->prepare("
        SELECT id, titulo, tipo_imovel, provincia, preco, descricao, imagem_principal
        FROM imoveis
        WHERE usuario_id = :usuario_id
        ORDER BY data_publicacao DESC
    ");
    $stmt->execute([':usuario_id' => $usuario_id]);
    $imoveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao carregar dados do perfil.");
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="stylesheet" href="../Assets/CSS/perfil.css">
</head>

<body>
    <!-- HEADER -->
    <?php include "../Includes/header.php"; ?>

    <main class="perfil-container">
        <!-- Cabeçalho do perfil -->
        <section class="perfil-header">
            <div class="perfil-info">
                <img src="<?= htmlspecialchars($foto_perfil) ?>" alt="Foto de perfil" class="foto-perfil">
                <div class="dados-usuario">
                    <h2><?= htmlspecialchars($usuario['nome']) ?></h2>
                    <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
                    <p><strong>Telefone:</strong> <?= htmlspecialchars($usuario['telefone'] ?? 'N/A') ?></p>
                    <p><strong>Província:</strong> <?= htmlspecialchars($usuario['provincia'] ?? 'N/A') ?></p>
                    <p><strong>País:</strong> <?= htmlspecialchars($usuario['pais'] ?? 'Moçambique') ?></p>

                    <div class="acoes-perfil">
                        <button class="btn-acao editar-perfil" onclick="window.location.href='editar_perfil.php'">Editar
                            Perfil</button>
                        <button class="btn-acao mudar-senha" onclick="window.location.href='mudar_senha.php'">Mudar
                            Palavra-passe</button>
                        <button class="btn-acao excluir-conta excluir" id="btnExcluirConta">Excluir Conta</button>
                    </div>
                </div>
            </div>
        </section>

        <hr class="linha-divisoria">

        <!-- Seção de publicações -->
        <section class="publicacoes-container">
            <h3>Minhas Publicações</h3>

            <?php if (empty($imoveis)): ?>
                <p class="sem-publicacoes">Você ainda não publicou nenhum imóvel.</p>
            <?php else: ?>
                <?php foreach ($imoveis as $imovel): ?>
                    <div class="publicacao">
                        <img src="../<?= htmlspecialchars($imovel['imagem_principal']) ?>" alt="Imagem do Imóvel"
                            class="imagem-imovel">

                        <div class="dados-imovel">
                            <h4><?= htmlspecialchars($imovel['titulo']) ?></h4>
                            <p><strong><?= htmlspecialchars($imovel['tipo_imovel']) ?> -
                                    <?= htmlspecialchars($imovel['provincia']) ?></strong></p>
                            <p><strong>Preço:</strong> <?= number_format($imovel['preco'], 2, ',', '.') ?> MZN</p>
                            <p><strong>Descrição:</strong> <?= htmlspecialchars($imovel['descricao']) ?></p>

                            <div class="acoes-imovel">
                                <button class="btn-editar" onclick="editarImovel(<?= $imovel['id'] ?>)">Editar</button>
                                <button class="btn-remover" class="excluir" onclick="removerImovel(<?= $imovel['id'] ?>)">Remover</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
    <!-- MODAL DE CONFIRMAÇÃO -->
    <div id="modalExcluirConta" class="modal">
        <div class="modal-content">
            <h2>Excluir Conta</h2>
            <p>Tem certeza de que deseja <strong>excluir permanentemente</strong> sua conta e todos os seus imóveis?
                Esta ação não pode ser desfeita.</p>
            <div class="modal-acoes">
                <button id="cancelarExclusao" class="btn-cancelar">Cancelar</button>
                <a href="../Back-end/excluir_conta.php" class="btn-confirmar">Sim, excluir</a>
            </div>
        </div>
    </div>


    <?php include "../Includes/footer.php"; ?>
</body>

<script src="../Assets/JS/header.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const btnExcluir = document.getElementById("btnExcluirConta");
        const modal = document.getElementById("modalExcluirConta");
        const cancelar = document.getElementById("cancelarExclusao");

        btnExcluir.addEventListener("click", () => {
            modal.style.display = "flex";
        });

        cancelar.addEventListener("click", () => {
            modal.style.display = "none";
        });

        // Fecha o modal ao clicar fora dele
        window.addEventListener("click", (e) => {
            if (e.target === modal) {
                modal.style.display = "none";
            }
        });
    });
    function editarImovel(id) {
        window.location.href = `editar_imovel.php?id=${id}`;
    }

    function removerImovel(id) {
        if (confirm("Tem certeza que deseja remover este imóvel?")) {
            window.location.href = `../Back-end/remover_imovel.php?id=${id}`;
        }
    }
</script>

</html>
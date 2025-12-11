<?php
session_start();
require_once "../Includes/conexao.php";
require_once "../Includes/auth.php";
protegerPagina();

// ✅ Valida token seguro
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("Location: inicio.php");
    exit();
}

$token = $_GET['token'];
if (!isset($_SESSION['imovel_tokens'][$token])) {
    header("Location: inicio.php");
    exit();
}

$id_imovel = (int) $_SESSION['imovel_tokens'][$token];

try {
    // 🏠 Busca dados do imóvel
    $stmt = $conn->prepare("SELECT * FROM imoveis WHERE id = :id");
    $stmt->bindParam(":id", $id_imovel, PDO::PARAM_INT);
    $stmt->execute();
    $imovel = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$imovel) {
        header("Location: inicio.php");
        exit();
    }

    $usuario_id = $_SESSION['usuario_id'];
    $proprietario = ($imovel['usuario_id'] == $usuario_id);

    // Diretórios base
    $base_path = "../Uploads/imoveis/{$imovel['usuario_id']}/{$id_imovel}/";

    // 📸 Imagem principal
    $principal_dir = $base_path . "principal/";
    $imagem_principal = null;
    if (is_dir($principal_dir)) {
        $arquivos = glob($principal_dir . "*.{jpg,jpeg,png,webp,gif}", GLOB_BRACE);
        if (!empty($arquivos)) {
            $imagem_principal = $arquivos[0];
        }
    }

    // 🖼️ Galeria
    $galeria = [];
    $galeria_dir = $base_path . "galeria/";
    if (is_dir($galeria_dir)) {
        $galeria = glob($galeria_dir . "*.{jpg,jpeg,png,webp,gif}", GLOB_BRACE);
    }

} catch (PDOException $e) {
    die("Erro ao carregar imóvel: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Imóvel</title>
    <link rel="stylesheet" href="../Assets/CSS/detalhes_imovel.css">
</head>

<body>
    <?php include "../Includes/header.php"; ?>

    <main class="container">
        <section class="detalhes-imovel">
            <h1><?= htmlspecialchars($imovel['titulo']) ?></h1>

            <div class="imagens">
                <?php if ($imagem_principal): ?>
                    <img src="<?= htmlspecialchars($imagem_principal) ?>" alt="Imagem principal" class="principal">
                <?php else: ?>
                    <p class="sem-foto">Sem imagem principal disponível</p>
                <?php endif; ?>

                <?php if (!empty($galeria)): ?>
                    <div class="galeria">
                        <?php foreach ($galeria as $foto): ?>
                            <img src="<?= htmlspecialchars($foto) ?>" alt="Imagem adicional">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="info">
                <p><strong>Tipo:</strong> <?= htmlspecialchars($imovel['tipo_imovel']) ?></p>
                <p><strong>Finalidade:</strong> <?= htmlspecialchars($imovel['finalidade']) ?></p>
                <p><strong>Preço:</strong> <?= number_format($imovel['preco'], 2, ',', '.') ?> MT</p>
                <p><strong>Localização:</strong>
                    <?= htmlspecialchars($imovel['endereco']) ?>,
                    <?= htmlspecialchars($imovel['cidade'] ?? '') ?> -
                    <?= htmlspecialchars($imovel['provincia']) ?>
                </p>
                <p><strong>Descrição:</strong> <?= nl2br(htmlspecialchars($imovel['descricao'])) ?></p>
                <p><strong>Publicado em:</strong> <?= date('d/m/Y H:i', strtotime($imovel['data_publicacao'])) ?></p>
            </div>

            <div class="acoes">
                <?php if ($proprietario): ?>
                    <a href="editar_imovel.php?token=<?= urlencode($token) ?>" class="btn editar">Editar</a>
                <?php else: ?>
                    <a href="../Back-end/iniciar_conversa.php?destinatario=<?= urlencode($imovel['usuario_id']) ?>"
                        class="btn">Contactar Anunciante</a>
                <?php endif; ?>
                <a href="inicio.php" class="btn voltar">Voltar</a>
            </div>
        </section>
    </main>

    <?php include "../Includes/footer.php"; ?>
</body>

</html>
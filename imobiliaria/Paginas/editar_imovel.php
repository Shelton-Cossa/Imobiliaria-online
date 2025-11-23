<?php
session_start();
require_once "../Includes/conexao.php";
require_once "../Includes/auth.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];

// ✅ Verifica o ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: perfil.php");
    exit;
}

$imovel_id = (int) $_GET['id'];

// 🔍 Busca os dados do imóvel
$stmt = $conn->prepare("SELECT * FROM imoveis WHERE id = :id AND usuario_id = :usuario_id");
$stmt->execute([':id' => $imovel_id, ':usuario_id' => $usuario_id]);
$imovel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$imovel) {
    $_SESSION['mensagem_erro'] = "Imóvel não encontrado.";
    header("Location: perfil.php");
    exit;
}

// 📸 Busca imagens da galeria
$stmt = $conn->prepare("SELECT * FROM imagens_imoveis WHERE imovel_id = :id");
$stmt->execute([':id' => $imovel_id]);
$galeria = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Imóvel</title>
    <link rel="stylesheet" href="../Assets/CSS/editar_imovel.css">
</head>

<body>
    <?php include "../Includes/header.php"; ?>

    <main class="editar-container">
        <h1>Editar Imóvel</h1>

        <form action="../Back-end/processar_edicao_imovel.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="imovel_id" value="<?= htmlspecialchars($imovel['id']) ?>">

            <label>Tipo de Imóvel</label>
            <input type="text" name="tipo_imovel" value="<?= htmlspecialchars($imovel['tipo_imovel']) ?>" required>

            <label>Finalidade</label>
            <input type="text" name="finalidade" value="<?= htmlspecialchars($imovel['finalidade']) ?>" required>

            <label>Preço (MZN)</label>
            <input type="number" name="preco" step="0.01" value="<?= htmlspecialchars($imovel['preco']) ?>" required>

            <label>Província</label>
            <input type="text" name="provincia" value="<?= htmlspecialchars($imovel['provincia']) ?>" required>

            <label>Endereço</label>
            <input type="text" name="endereco" value="<?= htmlspecialchars($imovel['endereco']) ?>" required>

            <label>Descrição</label>
            <textarea name="descricao" rows="5" required><?= htmlspecialchars($imovel['descricao']) ?></textarea>

            <hr>

            <div class="imagem-atual">
                <p><strong>Imagem principal atual:</strong></p>
                <img src="../<?= htmlspecialchars($imovel['imagem_principal']) ?>" class="thumb">
                <label>Substituir imagem principal:</label>
                <input type="file" name="imagem_principal" accept="image/*">
            </div>

            <hr>

            <div class="galeria-atual">
                <p><strong>Galeria atual:</strong></p>
                <div class="galeria">
                    <?php foreach ($galeria as $img): ?>
                        <div class="foto-item">
                            <img src="../<?= htmlspecialchars($img['caminho_imagem']) ?>" class="thumb">
                            <input type="checkbox" name="remover_galeria[]" value="<?= $img['id'] ?>"> Remover
                        </div>
                    <?php endforeach; ?>
                </div>
                <label>Adicionar novas imagens:</label>
                <input type="file" name="outras_imagens[]" accept="image/*" multiple>
            </div>

            <button type="submit" class="btn-salvar">Salvar Alterações</button>
            <a href="perfil.php" class="btn-voltar">Cancelar</a>
        </form>
    </main>
</body>

</html>
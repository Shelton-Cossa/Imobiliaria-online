<?php
session_start();

// 🔐 Verificação de login obrigatória
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit;
}

// 🔎 Dados básicos do usuário logado
$usuario_nome = $_SESSION['usuario_nome'] ?? "Usuário";
$usuario_foto = $_SESSION['usuario_foto'] ?? "../Assets/Imagens/default_user.png";
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicar Imóvel</title>
    <link rel="stylesheet" href="../Assets/CSS/publicar_imovel.css">
</head>

<body>
    <!-- HEADER -->
    <?php include "../Includes/header.php"; ?>
    <!-- MAIN -->
    <main class="publicar">
        <h1>Publicar Imóvel</h1>

        <form action="../Back-end/processar_publicacao.php" 
              method="post" 
              enctype="multipart/form-data" 
              class="form-publicar" 
              id="formPublicar">

            <!-- Tipo de imóvel -->
            <label for="tipo">Tipo de Imóvel</label>
            <select name="tipo" id="tipo" required>
                <option value="">Selecione</option>
                <option value="Casa">Casa</option>
                <option value="Apartamento">Apartamento</option>
                <option value="Escritório">Escritório</option>
                <option value="Terreno">Terreno</option>
                <option value="Loja">Loja</option>
                <option value="Armazém">Armazém</option>
                <option value="Vivenda">Vivenda</option>
                <option value="Outro">Outro</option>
            </select>

            <!-- Finalidade -->
            <label for="finalidade">Finalidade</label>
            <select name="finalidade" id="finalidade" required>
                <option value="">Selecione</option>
                <option value="Venda">Venda</option>
                <option value="Aluguel">Aluguel</option>
            </select>

            <!-- Preço -->
            <label for="preco">Preço (MZN)</label>
            <input type="number" name="preco" id="preco" min="0" step="any" placeholder="Ex: 5000000" required>

            <!-- Província -->
            <label for="provincia">Província</label>
            <select name="provincia" id="provincia" required>
                <option value="">Selecione a Província</option>
                <option value="Cabo Delgado">Cabo Delgado</option>
                <option value="Niassa">Niassa</option>
                <option value="Nampula">Nampula</option>
                <option value="Zambézia">Zambézia</option>
                <option value="Tete">Tete</option>
                <option value="Manica">Manica</option>
                <option value="Sofala">Sofala</option>
                <option value="Inhambane">Inhambane</option>
                <option value="Gaza">Gaza</option>
                <option value="Maputo (Província)">Maputo (Província)</option>
                <option value="Maputo (Cidade)">Maputo (Cidade)</option>
            </select>

            <!-- Endereço -->
            <label for="endereco">Endereço</label>
            <input type="text" name="endereco" id="endereco" placeholder="Ex: Bairro Central, Rua 123" required>

            <!-- Descrição -->
            <label for="descricao">Descrição</label>
            <textarea name="descricao" id="descricao" rows="5" placeholder="Descreva os detalhes do imóvel..." required></textarea>

            <!-- Imagem Principal -->
            <label for="imagem_principal">Imagem Principal</label>
            <input type="file" name="imagem_principal" id="imagem_principal" accept="image/*" required>
            <div class="preview-container" id="previewPrincipal"></div>

            <!-- Outras Imagens -->
            <label for="outras_imagens">Outras Imagens do Imóvel</label>
            <input type="file" name="outras_imagens[]" id="outras_imagens" accept="image/*" multiple>
            <div class="preview-container" id="previewOutras"></div>

            <!-- Botão -->
            <button type="submit" class="btn-publicar">Publicar</button>
        </form>
    </main>

    <!-- FOOTER -->
    <?php include "../Includes/footer.php"; ?>
</body>
<script src="../Assets/JS/publicar_imovel.js"></script>
</html>

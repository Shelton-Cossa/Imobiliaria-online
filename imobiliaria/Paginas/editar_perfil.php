<?php
session_start();
require_once "../Includes/conexao.php";

// 🔒 Verifica login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

try {
    $stmt = $conn->prepare("SELECT nome, email, telefone, provincia, pais, foto_perfil FROM usuarios WHERE id = :id");
    $stmt->bindParam(":id", $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die("Usuário não encontrado.");
    }

    // Caminho seguro da imagem de perfil
    if (!empty($usuario['foto_perfil']) && file_exists("../" . $usuario['foto_perfil'])) {
        $foto_perfil = "../" . ltrim($usuario['foto_perfil'], '/');
    } else {
        $foto_perfil = "../uploads/perfil/default.jpg";
    }

} catch (PDOException $e) {
    die("Erro ao carregar informações do perfil.");
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="../Assets/CSS/editar_perfil.css">
</head>

<body>

    <?php include "../Includes/header.php"; ?>

    <main class="container">
        <section class="editar-perfil">
            <h1>Editar Perfil</h1>

            <form action="../Back-end/processar_edicao_perfil.php" method="POST" enctype="multipart/form-data">
                <div class="perfil-foto">
                    <img src="<?= htmlspecialchars($foto_perfil) ?>" alt="Foto de Perfil" class="foto-preview"
                        id="previewImagem">
                    <label for="foto_perfil">Alterar Foto:</label>
                    <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*"
                        onchange="previewImagem(this)">
                </div>

                <label>Nome:</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>

                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>

                <label>Telefone:</label>
                <input type="text" name="telefone" value="<?= htmlspecialchars($usuario['telefone']) ?>">

                <label>Província:</label>
                <input type="text" name="provincia" value="<?= htmlspecialchars($usuario['provincia']) ?>">

                <label>País:</label>
                <input type="text" name="pais" value="<?= htmlspecialchars($usuario['pais']) ?>">

                <div class="botoes">
                    <button type="submit" class="btn-salvar">Salvar Alterações</button>
                    <a href="perfil.php" class="btn-voltar">Cancelar</a>
                </div>
            </form>
        </section>
    </main>

    <script>
        function previewImagem(input) {
            const preview = document.getElementById('previewImagem');
            const file = input.files[0];
            if (file) {
                preview.src = URL.createObjectURL(file);
            }
        }
    </script>
    <?php include "../Includes/footer.php"; ?>
</body>

</html>
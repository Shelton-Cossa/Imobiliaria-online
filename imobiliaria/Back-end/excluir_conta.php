<?php
session_start();
require_once "../Includes/conexao.php";
require_once "../Includes/auth.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];

try {
    // 🧩 1️⃣ Buscar imóveis do usuário
    $stmt = $conn->prepare("SELECT id FROM imoveis WHERE usuario_id = :usuario_id");
    $stmt->execute([':usuario_id' => $usuario_id]);
    $imoveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🧹 2️⃣ Função auxiliar para remover diretórios recursivamente
    function removerDiretorio($dir)
    {
        if (!is_dir($dir))
            return;

        $arquivos = scandir($dir);
        foreach ($arquivos as $arquivo) {
            if ($arquivo === '.' || $arquivo === '..')
                continue;

            $caminho = $dir . DIRECTORY_SEPARATOR . $arquivo;
            if (is_dir($caminho)) {
                removerDiretorio($caminho); // recursivo
            } else {
                @unlink($caminho);
            }
        }
        @rmdir($dir);
    }

    // 🧩 3️⃣ Apagar diretórios dos imóveis do usuário
    foreach ($imoveis as $imovel) {
        $pasta_imovel = "../Uploads/imoveis/$usuario_id/{$imovel['id']}";
        if (is_dir($pasta_imovel)) {
            removerDiretorio($pasta_imovel);
        }
    }

    // 🧩 4️⃣ Apagar a pasta de perfil do usuário (foto de perfil)
    $pasta_perfil = "../Uploads/perfil/$usuario_id";
    if (is_dir($pasta_perfil)) {
        removerDiretorio($pasta_perfil);
    }

    // 🧩 5️⃣ Apagar pasta principal do usuário em /imoveis/
    $pasta_usuario_imoveis = "../Uploads/imoveis/$usuario_id";
    if (is_dir($pasta_usuario_imoveis)) {
        removerDiretorio($pasta_usuario_imoveis);
    }

    // 🧩 6️⃣ Remover dados do banco
    $conn->beginTransaction();

    // Apaga imóveis do usuário
    $stmt = $conn->prepare("DELETE FROM imoveis WHERE usuario_id = :usuario_id");
    $stmt->execute([':usuario_id' => $usuario_id]);

    // Apaga usuário
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = :usuario_id");
    $stmt->execute([':usuario_id' => $usuario_id]);

    $conn->commit();

    // 🧩 7️⃣ Encerrar sessão
    session_destroy();

    // 🧩 8️⃣ Redirecionar
    header("Location: ../Paginas/login.php?mensagem=conta_excluida");
    exit();

} catch (Exception $e) {
    if ($conn->inTransaction())
        $conn->rollBack();
    $_SESSION['mensagem_erro'] = "Erro ao excluir conta: " . $e->getMessage();
    header("Location: ../Paginas/perfil.php");
    exit();
}
?>
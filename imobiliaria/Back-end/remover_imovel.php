<?php
session_start();
require_once "../Includes/conexao.php";
require_once "../Includes/auth.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];

// ✅ Verifica se o ID foi passado corretamente
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensagem_erro'] = "ID do imóvel inválido.";
    header("Location: ../Paginas/perfil.php");
    exit;
}

$imovel_id = (int) $_GET['id'];

try {
    $conn->beginTransaction();

    // 1️⃣ Verifica se o imóvel pertence ao usuário
    $stmt = $conn->prepare("SELECT imagem_principal FROM imoveis WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->execute([':id' => $imovel_id, ':usuario_id' => $usuario_id]);
    $imovel = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$imovel) {
        $_SESSION['mensagem_erro'] = "Imóvel não encontrado ou não pertence a você.";
        header("Location: ../Paginas/perfil.php");
        exit;
    }

    // 2️⃣ Busca todas as imagens da galeria
    $stmt = $conn->prepare("SELECT caminho_imagem FROM imagens_imoveis WHERE imovel_id = :id");
    $stmt->execute([':id' => $imovel_id]);
    $imagens_galeria = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 🗑️ 3️⃣ Remove todas as imagens (principal + galeria)
    if (!empty($imovel['imagem_principal']) && file_exists("../" . $imovel['imagem_principal'])) {
        unlink("../" . $imovel['imagem_principal']);
    }

    if (!empty($imagens_galeria)) {
        foreach ($imagens_galeria as $imgPath) {
            $fullPath = "../" . $imgPath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    // 🧹 4️⃣ Remove diretórios do imóvel recursivamente
    $imovelDir = "../Uploads/imoveis/$usuario_id/$imovel_id/";

    if (is_dir($imovelDir)) {
        function deletarDiretorio($dir)
        {
            $arquivos = array_diff(scandir($dir), ['.', '..']);
            foreach ($arquivos as $arquivo) {
                $caminho = "$dir/$arquivo";
                if (is_dir($caminho)) {
                    deletarDiretorio($caminho);
                } else {
                    unlink($caminho);
                }
            }
            return rmdir($dir);
        }
        deletarDiretorio($imovelDir);
    }

    // 💾 5️⃣ Remove registros do banco
    $stmt = $conn->prepare("DELETE FROM imagens_imoveis WHERE imovel_id = :id");
    $stmt->execute([':id' => $imovel_id]);

    $stmt = $conn->prepare("DELETE FROM imoveis WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->execute([':id' => $imovel_id, ':usuario_id' => $usuario_id]);

    // ✅ 6️⃣ Verifica se o usuário ainda tem imóveis
    $stmt = $conn->prepare("SELECT COUNT(*) FROM imoveis WHERE usuario_id = :usuario_id");
    $stmt->execute([':usuario_id' => $usuario_id]);
    $tem_mais_imoveis = $stmt->fetchColumn() > 0;

    // 🧾 7️⃣ Se não tiver mais imóveis, remove também a pasta do usuário
    $userDir = "../Uploads/imoveis/$usuario_id/";
    if (!$tem_mais_imoveis && is_dir($userDir)) {
        deletarDiretorio($userDir);
    }

    $conn->commit();

    $_SESSION['mensagem_sucesso'] = "Imóvel e todas as suas imagens foram removidos com sucesso!";
    header("Location: ../Paginas/perfil.php");
    exit;

} catch (Exception $e) {
    if ($conn->inTransaction())
        $conn->rollBack();
    error_log("Erro ao remover imóvel: " . $e->getMessage());
    $_SESSION['mensagem_erro'] = "Erro ao remover imóvel. Tente novamente.";
    header("Location: ../Paginas/perfil.php");
    exit;
}
?>
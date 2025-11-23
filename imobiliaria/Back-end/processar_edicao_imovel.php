<?php
session_start();
require_once "../Includes/conexao.php";
require_once "../Includes/auth.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../Paginas/perfil.php");
    exit;
}

$imovel_id = (int) $_POST['imovel_id'];
$tipo = trim($_POST['tipo_imovel']);
$finalidade = trim($_POST['finalidade']);
$preco = floatval($_POST['preco']);
$provincia = trim($_POST['provincia']);
$endereco = trim($_POST['endereco']);
$descricao = trim($_POST['descricao']);

try {
    // 🏠 Verifica se o imóvel pertence ao usuário
    $stmt = $conn->prepare("SELECT * FROM imoveis WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->execute([':id' => $imovel_id, ':usuario_id' => $usuario_id]);
    $imovel = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$imovel) {
        throw new Exception("Imóvel não encontrado ou não pertence ao usuário.");
    }

    // Diretórios
    $baseDir = "../Uploads/imoveis/$usuario_id/$imovel_id/";
    $principalDir = $baseDir . "principal/";
    $galeriaDir = $baseDir . "galeria/";

    // 🖼️ Atualiza imagem principal
    if (!empty($_FILES['imagem_principal']['name'])) {
        $oldMain = "../" . $imovel['imagem_principal'];
        if (file_exists($oldMain))
            unlink($oldMain);

        $ext = pathinfo($_FILES['imagem_principal']['name'], PATHINFO_EXTENSION);
        $novoNome = "principal_" . uniqid() . "." . strtolower($ext);
        $destino = $principalDir . $novoNome;

        move_uploaded_file($_FILES['imagem_principal']['tmp_name'], $destino);

        $pathDB = "Uploads/imoveis/$usuario_id/$imovel_id/principal/$novoNome";
        $stmt = $conn->prepare("UPDATE imoveis SET imagem_principal = :img WHERE id = :id");
        $stmt->execute([':img' => $pathDB, ':id' => $imovel_id]);
    }

    // 🗑️ Remover imagens da galeria
    if (!empty($_POST['remover_galeria'])) {
        $ids = $_POST['remover_galeria'];
        foreach ($ids as $img_id) {
            $stmt = $conn->prepare("SELECT caminho_imagem FROM imagens_imoveis WHERE id = :id AND imovel_id = :imovel_id");
            $stmt->execute([':id' => $img_id, ':imovel_id' => $imovel_id]);
            $img = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($img && file_exists("../" . $img['caminho_imagem'])) {
                unlink("../" . $img['caminho_imagem']);
            }
            $conn->prepare("DELETE FROM imagens_imoveis WHERE id = :id")->execute([':id' => $img_id]);
        }
    }

    // ➕ Novas imagens da galeria
    if (!empty($_FILES['outras_imagens']['name'][0])) {
        $stmtAdd = $conn->prepare("INSERT INTO imagens_imoveis (imovel_id, caminho_imagem) VALUES (:imovel_id, :caminho)");
        foreach ($_FILES['outras_imagens']['name'] as $i => $name) {
            if ($_FILES['outras_imagens']['error'][$i] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $novoNome = "foto_" . uniqid() . "." . $ext;
                $destino = $galeriaDir . $novoNome;
                move_uploaded_file($_FILES['outras_imagens']['tmp_name'][$i], $destino);
                $pathDB = "Uploads/imoveis/$usuario_id/$imovel_id/galeria/$novoNome";
                $stmtAdd->execute([':imovel_id' => $imovel_id, ':caminho' => $pathDB]);
            }
        }
    }

    // 🧾 Atualiza informações gerais
    $stmt = $conn->prepare("
        UPDATE imoveis 
        SET tipo_imovel = :tipo, finalidade = :finalidade, preco = :preco, provincia = :provincia, 
            endereco = :endereco, descricao = :descricao
        WHERE id = :id AND usuario_id = :usuario_id
    ");
    $stmt->execute([
        ':tipo' => $tipo,
        ':finalidade' => $finalidade,
        ':preco' => $preco,
        ':provincia' => $provincia,
        ':endereco' => $endereco,
        ':descricao' => $descricao,
        ':id' => $imovel_id,
        ':usuario_id' => $usuario_id
    ]);

    $_SESSION['mensagem_sucesso'] = "Imóvel atualizado com sucesso!";
    header("Location: ../Paginas/perfil.php");
    exit;

} catch (Exception $e) {
    $_SESSION['mensagem_erro'] = "Erro ao atualizar imóvel: " . $e->getMessage();
    header("Location: ../Paginas/perfil.php");
    exit;
}
?>
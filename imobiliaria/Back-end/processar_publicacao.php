<?php
session_start();
require_once "../Includes/conexao.php";

// ======================================================
// 🔐 VERIFICA LOGIN
// ======================================================
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['mensagem_erro'] = "Precisa fazer login para publicar um imóvel.";
    header("Location: ../Paginas/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../Paginas/publicar_imovel.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// ======================================================
// ⚙️ CONFIGURAÇÕES
// ======================================================
$MAX_FILE_SIZE = 3 * 1024 * 1024; // 3 MB
$MAX_ADICIONAIS = 10;
$ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$BASE_UPLOAD_DIR = "../Uploads/imoveis/";

// ======================================================
// 🧾 CAPTURA CAMPOS
// ======================================================
$tipo = trim($_POST['tipo'] ?? '');
$finalidade = trim($_POST['finalidade'] ?? '');
$preco = filter_var(str_replace(',', '.', $_POST['preco'] ?? ''), FILTER_VALIDATE_FLOAT);
$provincia = trim($_POST['provincia'] ?? '');
$endereco = trim($_POST['endereco'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');

if (!$tipo || !$finalidade || !$preco || !$provincia || !$endereco || !$descricao) {
    $_SESSION['mensagem_erro'] = "Por favor, preencha todos os campos obrigatórios.";
    header("Location: ../Paginas/publicar_imovel.php");
    exit;
}

try {
    $conn->beginTransaction();

    // ======================================================
    // 🧱 INSERE O IMÓVEL (ainda sem imagens)
    // ======================================================
    $stmt = $conn->prepare("
        INSERT INTO imoveis 
        (usuario_id, tipo_imovel, finalidade, preco, provincia, endereco, descricao, data_publicacao)
        VALUES (:usuario_id, :tipo, :finalidade, :preco, :provincia, :endereco, :descricao, NOW())
    ");
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':tipo' => $tipo,
        ':finalidade' => $finalidade,
        ':preco' => $preco,
        ':provincia' => $provincia,
        ':endereco' => $endereco,
        ':descricao' => $descricao
    ]);

    $imovel_id = $conn->lastInsertId();

    // ======================================================
    // 📁 CRIA ESTRUTURA DE PASTAS
    // ../Uploads/imoveis/{usuario_id}/{imovel_id}/principal/
    // ../Uploads/imoveis/{usuario_id}/{imovel_id}/galeria/
    // ======================================================
    $userDir = $BASE_UPLOAD_DIR . $usuario_id . "/";
    $imovelDir = $userDir . $imovel_id . "/";
    $principalDir = $imovelDir . "principal/";
    $galeriaDir = $imovelDir . "galeria/";

    foreach ([$userDir, $imovelDir, $principalDir, $galeriaDir] as $dir) {
        if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
            throw new Exception("Falha ao criar diretório: $dir");
        }
    }

    // ======================================================
    // 🖼️ IMAGEM PRINCIPAL
    // ======================================================
    if (!isset($_FILES['imagem_principal']) || $_FILES['imagem_principal']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Imagem principal obrigatória.");
    }

    $main = $_FILES['imagem_principal'];
    $ext = strtolower(pathinfo($main['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $ALLOWED_EXT))
        throw new Exception("Formato de imagem principal inválido.");
    if ($main['size'] > $MAX_FILE_SIZE)
        throw new Exception("Imagem principal excede 3MB.");

    $nomeMain = "principal_" . uniqid() . "." . $ext;
    $destinoMain = $principalDir . $nomeMain;

    if (!move_uploaded_file($main['tmp_name'], $destinoMain)) {
        throw new Exception("Erro ao mover imagem principal.");
    }

    // Caminho relativo (para exibição posterior)
    $pathPrincipalDB = "Uploads/imoveis/$usuario_id/$imovel_id/principal/$nomeMain";

    // ======================================================
    // 🖼️ GALERIA DE IMAGENS (opcional)
    // ======================================================
    if (!empty($_FILES['outras_imagens']['name'][0])) {
        $total = min(count($_FILES['outras_imagens']['name']), $MAX_ADICIONAIS);

        $stmtImg = $conn->prepare("
            INSERT INTO imagens_imoveis (imovel_id, caminho_imagem)
            VALUES (:imovel_id, :caminho)
        ");

        for ($i = 0; $i < $total; $i++) {
            if ($_FILES['outras_imagens']['error'][$i] !== UPLOAD_ERR_OK)
                continue;

            $orig = $_FILES['outras_imagens']['name'][$i];
            $tmp = $_FILES['outras_imagens']['tmp_name'][$i];
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $size = $_FILES['outras_imagens']['size'][$i];

            if (!in_array($ext, $ALLOWED_EXT) || $size > $MAX_FILE_SIZE)
                continue;

            $nomeFoto = "foto_" . uniqid() . "." . $ext;
            $destino = $galeriaDir . $nomeFoto;

            if (move_uploaded_file($tmp, $destino)) {
                $pathDB = "Uploads/imoveis/$usuario_id/$imovel_id/galeria/$nomeFoto";
                $stmtImg->execute([':imovel_id' => $imovel_id, ':caminho' => $pathDB]);
            }
        }
    }

    // ======================================================
    // 🧾 ATUALIZA IMAGEM PRINCIPAL NA TABELA IMÓVEIS
    // ======================================================
    $stmtUpdate = $conn->prepare("UPDATE imoveis SET imagem_principal = :img WHERE id = :id");
    $stmtUpdate->execute([':img' => $pathPrincipalDB, ':id' => $imovel_id]);

    $conn->commit();

    $_SESSION['mensagem_sucesso'] = "Imóvel publicado com sucesso!";
    header("Location: ../Paginas/publicar_imovel.php");
    exit;

} catch (Exception $e) {
    if ($conn->inTransaction())
        $conn->rollBack();

    error_log("Erro ao publicar imóvel: " . $e->getMessage());
    $_SESSION['mensagem_erro'] = "Erro ao publicar imóvel. Tente novamente.";
    header("Location: ../Paginas/publicar_imovel.php");
    exit;
}

<?php
session_start();
require_once "../Includes/conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../Paginas/login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../Paginas/editar_perfil.php");
    exit();
}

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$provincia = trim($_POST['provincia'] ?? '');
$pais = trim($_POST['pais'] ?? '');

if (!$nome || !$email) {
    $_SESSION['mensagem_erro'] = "Preencha todos os campos obrigatórios.";
    header("Location: ../Paginas/editar_perfil.php");
    exit();
}

// 📁 Diretório base
$baseDir = "../uploads/perfil/";
$userDir = $baseDir . $usuario_id . "/";

if (!is_dir($userDir)) {
    mkdir($userDir, 0777, true);
}

try {
    $conn->beginTransaction();

    // Atualiza dados básicos
    $stmt = $conn->prepare("
        UPDATE usuarios 
        SET nome = :nome, email = :email, telefone = :telefone, provincia = :provincia, pais = :pais
        WHERE id = :id
    ");
    $stmt->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':telefone' => $telefone,
        ':provincia' => $provincia,
        ':pais' => $pais,
        ':id' => $usuario_id
    ]);

    // 📸 Upload da nova foto
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $foto = $_FILES['foto_perfil'];
        $ext = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $permitidas)) {
            $nome_arquivo = "perfil_" . uniqid() . "." . $ext;
            $destino = $userDir . $nome_arquivo;

            if (move_uploaded_file($foto['tmp_name'], $destino)) {
                $caminhoDB = "uploads/perfil/$usuario_id/$nome_arquivo";

                // Atualiza caminho da imagem
                $stmtImg = $conn->prepare("UPDATE usuarios SET foto_perfil = :foto WHERE id = :id");
                $stmtImg->execute([':foto' => $caminhoDB, ':id' => $usuario_id]);
            }
        }
    }

    $conn->commit();
    $_SESSION['mensagem_sucesso'] = "Perfil atualizado com sucesso!";
    header("Location: ../Paginas/perfil.php");
    exit();

} catch (Exception $e) {
    if ($conn->inTransaction())
        $conn->rollBack();
    error_log("Erro ao atualizar perfil: " . $e->getMessage());
    $_SESSION['mensagem_erro'] = "Erro ao atualizar perfil.";
    header("Location: ../Paginas/editar_perfil.php");
    exit();
}
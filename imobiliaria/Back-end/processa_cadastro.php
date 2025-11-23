<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../Includes/conexao.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $provincia = trim($_POST['provincia']);
    $senha = $_POST['senha'];
    $pais = "Moçambique"; // valor fixo

    if (empty($nome) || empty($email) || empty($telefone) || empty($provincia) || empty($senha)) {
        die("Por favor, preencha todos os campos obrigatórios!");
    }

    // Verifica duplicação de email
    $verificar = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $verificar->execute([$email]);
    if ($verificar->rowCount() > 0) {
        die("O email informado já está em uso!");
    }

    // Upload de foto
    $foto_perfil = "../uploads/perfil/default.jpg";
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === 0) {
        $pasta = "../uploads/perfil/";
        if (!is_dir($pasta))
            mkdir($pasta, 0777, true);
        $extensao = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($extensao, $permitidas)) {
            $novo_nome = uniqid("perfil_", true) . "." . $extensao;
            $destino = $pasta . $novo_nome;
            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $destino)) {
                $foto_perfil = "../uploads/perfil/" . $novo_nome;
            }
        }
    }

    // Criptografa senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Insere no banco
    $sql = "INSERT INTO usuarios (nome, email, telefone, pais, provincia, senha, foto_perfil, data_cadastro)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);

    try {
        $stmt->execute([$nome, $email, $telefone, $pais, $provincia, $senha_hash, $foto_perfil]);
        header("Location: ../Paginas/login.php?status=sucesso");
        exit;
    } catch (PDOException $e) {
        die("Erro ao cadastrar: " . $e->getMessage());
    }
}
?>
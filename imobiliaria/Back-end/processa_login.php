<?php
session_start();
require_once "../Includes/conexao.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // ✅ Verificação simples dos campos
    if (empty($email) || empty($senha)) {
        $_SESSION['login_erro'] = "Por favor, preencha todos os campos.";
        header("Location: ../Paginas/login.html");
        exit;
    }

    try {
        // 🔒 Busca o usuário (sem indicar se email existe)
        $sql = "SELECT id, nome, email, senha, foto_perfil FROM usuarios WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica senha (se o email não existir, o $usuario será null)
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // ✅ Login bem-sucedido
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_foto'] = $usuario['foto_perfil'];

            header("Location: ../Paginas/inicio.php");
            exit;
        } else {
            // ⚠️ Mensagem genérica — não informa se é senha ou email incorreto
            $_SESSION['login_erro'] = "Credenciais inválidas. Tente novamente.";
            header("Location: ../Paginas/login.php");
            exit;
        }
    } catch (PDOException $e) {
        // 🚨 Em produção, nunca mostre o erro real!
        $_SESSION['login_erro'] = "Erro interno. Tente novamente mais tarde.";
        header("Location: ../Paginas/login.php");
        exit;
    }
}
?>
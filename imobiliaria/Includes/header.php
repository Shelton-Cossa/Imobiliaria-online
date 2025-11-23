<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

// =====================================================
// 🔄 SINCRONIZAÇÃO AUTOMÁTICA COM O BANCO
// =====================================================
if (isset($_SESSION['usuario_id'])) {
    require_once "../Includes/conexao.php";

    // Se não houver dados completos na sessão, recarrega do banco
    if (empty($_SESSION['usuario_nome']) || empty($_SESSION['usuario_foto'])) {
        $stmt = $conn->prepare("SELECT nome, caminho_foto FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['usuario_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['usuario_foto'] = $user['caminho_foto'] ?: "../uploads/perfil/default.jpg";
        }
    }
}

// =====================================================
// 🔐 DADOS DO USUÁRIO LOGADO
// =====================================================
$usuario_nome = $_SESSION['usuario_nome'] ?? "Usuário";
$usuario_foto = $_SESSION['usuario_foto'] ?? "../uploads/perfil/default.jpg";
?>
<link rel="stylesheet" href="../Assets/CSS/header.css">

<body>
    <!-- HEADER -->
    <header>
        <nav>
            <span><a href="inicio.php">Imobiliária Online</a></span>

            <ul>
                <li><a href="inicio.php"
                        class="<?= basename($_SERVER['PHP_SELF']) == 'inicio.php' ? 'active' : '' ?>">Início</a></li>
                <li><a href="pesquisar_imoveis.php"
                        class="<?= basename($_SERVER['PHP_SELF']) == 'pesquisar_imoveis.php' ? 'active' : '' ?>">Pesquisar
                        Imóveis</a></li>
                <li><a href="publicar_imovel.php"
                        class="<?= basename($_SERVER['PHP_SELF']) == 'publicar_imovel.php' ? 'active' : '' ?>">Publicar
                        Imóvel</a></li>
                <li><a href="sobre.html"
                        class="<?= basename($_SERVER['PHP_SELF']) == 'sobre.html' ? 'active' : '' ?>">Sobre</a></li>
            </ul>

            <!-- Menu do usuário -->
            <div class="user-menu">
                <img src="<?= htmlspecialchars($usuario_foto) . '?v=' . time(); ?>" alt="Perfil" class="user-avatar"
                    id="userAvatar">

                <ul class="dropdown" id="userDropdown">
                    <li><a href="perfil.php">Meu Perfil (<?= htmlspecialchars($usuario_nome) ?>)</a></li>
                    <li><a href="mensagens.php">Mensagens</a></li>
                    <li><a href="../Back-end/logout.php">Sair</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <script src="../Assets/JS/header.js"></script>
</body>
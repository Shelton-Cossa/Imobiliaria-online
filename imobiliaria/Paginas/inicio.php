<?php
session_start();
require_once "../Includes/conexao.php";

// Bloquear acesso se não estiver logado
require_once "../Includes/auth.php";
protegerPagina();

// Dados do usuário
$usuario_nome = htmlspecialchars($_SESSION["usuario_nome"] ?? "Usuário");

// -----------------------------
// Detectar colunas reais da tabela "imoveis"
// -----------------------------
try {
    $stmtCols = $conn->query("SHOW COLUMNS FROM imoveis");
    $cols = $stmtCols->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erro ao ler estrutura da tabela imoveis: " . $e->getMessage());
}

// Determinar nome da chave primária (id)
$pk = null;
foreach ($cols as $c) {
    if (!empty($c['Key']) && strtoupper($c['Key']) === 'PRI') {
        $pk = $c['Field'];
        break;
    }
}
if (!$pk) {
    // fallback comum
    $pk = 'id';
}

// Determinar campo de imagem (preferências)
$imgField = null;
$possibleImages = ['imagem_principal', 'foto_principal', 'imagem', 'foto'];
foreach ($cols as $c) {
    if (in_array($c['Field'], $possibleImages, true)) {
        $imgField = $c['Field'];
        break;
    }
}
if (!$imgField) {
    // se não encontrou, usa primeira coluna varchar/text provável (tentativa)
    foreach ($cols as $c) {
        $type = strtolower($c['Type']);
        if (strpos($type, 'varchar') !== false || strpos($type, 'text') !== false) {
            if (stripos($c['Field'], 'img') !== false || stripos($c['Field'], 'foto') !== false) {
                $imgField = $c['Field'];
                break;
            }
        }
    }
}
// Se ainda não encontrou, define nulo e trata no output
// -----------------------------

// Função para obter caminho seguro da imagem
function safeImagePath($row, $imgField)
{
    // Se não tiver imagem ou campo vazio
    if (!$imgField || empty($row[$imgField])) {
        return '../Assets/Imagens/no-image.jpg'; // imagem padrão
    }

    $path = trim($row[$imgField]);

    // Normaliza separadores
    $path = str_replace('\\', '/', $path);

    // Se não começa com "../", adiciona
    if (!str_starts_with($path, '../')) {
        $path = '../' . $path;
    }

    // Evita caminhos duplicados tipo "../../Uploads"
    $path = preg_replace('#(\.\./)+Uploads#', '../Uploads', $path);

    // Segurança básica
    return htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
}


// -----------------------------
// Cache (24h) — destaques aleatórios
// -----------------------------
$cache_file = __DIR__ . "/../Back-end/cache_destaques.json";
$cache_time = 24 * 60 * 60; // 24h

$destaques = [];
try {
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
        $destaques = json_decode(file_get_contents($cache_file), true);
        if (!is_array($destaques))
            $destaques = [];
    } else {
        // Busca 3 aleatórios — usa o nome da PK e do campo de imagem dinamicamente
        $selectImg = $imgField ? $imgField : 'NULL AS imagem';
        $sql = "SELECT {$pk} AS id_imovel, tipo_imovel, provincia, preco, {$selectImg} FROM imoveis ORDER BY RAND() LIMIT 3";
        $stmt = $conn->query($sql);
        $destaques = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // grava cache (array)
        file_put_contents($cache_file, json_encode($destaques));
    }
} catch (Exception $e) {
    // não fatal: apenas deixa a lista vazia
    error_log("Erro ao buscar destaques: " . $e->getMessage());
    $destaques = [];
}

// -----------------------------
// Outros imóveis — todos (ordenados por data_publicacao se existir)
// -----------------------------
$outros = [];
try {
    // escolher ordenação por data_publicacao quando existir
    $hasDataPub = false;
    foreach ($cols as $c) {
        if ($c['Field'] === 'data_publicacao') {
            $hasDataPub = true;
            break;
        }
    }
    $orderBy = $hasDataPub ? 'data_publicacao DESC' : "{$pk} DESC";
    $selectImg = $imgField ? $imgField : 'NULL AS imagem';
    $sql2 = "SELECT {$pk} AS id_imovel, tipo_imovel, provincia, preco, {$selectImg} FROM imoveis ORDER BY {$orderBy}";
    $stmt2 = $conn->query($sql2);
    $outros = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erro ao buscar outros imoveis: " . $e->getMessage());
    $outros = [];
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Início - Imobiliária Online</title>
    <link rel="stylesheet" href="../Assets/CSS/inicio.css">
</head>

<body>
    <?php include "../Includes/header.php"; ?>

    <div class="welcome">
        <p>Bem-vindo, <?= $usuario_nome ?>!</p>
        <button onclick="location.href='pesquisar_imoveis.php'">Pesquisar imóveis</button>
    </div>

    <!-- DESTAQUES -->
    <section class="imoveis destaque">
        <h2>Imóveis em Destaque</h2>
        <div class="cards-container">
            <?php if (!empty($destaques)): ?>
                <?php foreach ($destaques as $imovel):
                    $img = safeImagePath($imovel, $imgField);
                    $id_show = urlencode($imovel['id_imovel']); // usa id_imovel consistente
                    ?>
                    <div class="card">
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($imovel['tipo_imovel'] ?? 'Imóvel') ?>">
                        <h3><?= htmlspecialchars(ucfirst($imovel['tipo_imovel'] ?? 'Tipo')) ?> -
                            <?= htmlspecialchars($imovel['provincia'] ?? '') ?>
                        </h3>
                        <p>Preço: <?= isset($imovel['preco']) ? number_format($imovel['preco'], 0, ',', '.') . " MT" : "—" ?>
                        </p>

                        <!-- botão clicável para detalhes -->
                        <button class="btn-detalhes" onclick="location.href='id=<?= $id_show ?>'">
                            Ver Detalhes
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum imóvel em destaque no momento.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- OUTROS IMOVEIS -->
    <section class="imoveis outros">
        <h2>Outros Imóveis</h2>
        <div class="cards-container">
            <?php if (!empty($outros)): ?>
                <?php foreach ($outros as $imovel):
                    $img = safeImagePath($imovel, $imgField);
                    $id_show = urlencode($imovel['id_imovel']);
                    ?>
                    <div class="card">
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($imovel['tipo_imovel'] ?? 'Imóvel') ?>">
                        <h3><?= htmlspecialchars(ucfirst($imovel['tipo_imovel'] ?? 'Tipo')) ?> -
                            <?= htmlspecialchars($imovel['provincia'] ?? '') ?>
                        </h3>
                        <p>Preço: <?= isset($imovel['preco']) ? number_format($imovel['preco'], 0, ',', '.') . " MT" : "—" ?>
                        </p>

                        <button class="btn-detalhes" onclick="location.href='abrir_imovel.php?id=<?= $id_show ?>'">
                            Ver Detalhes
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum imóvel encontrado.</p>
            <?php endif; ?>
        </div>
    </section>
    <?php include "../Includes/footer.php"; ?>
</body>

</html>
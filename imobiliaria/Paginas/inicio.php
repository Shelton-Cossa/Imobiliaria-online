<?php
// Paginas/inicio.php
session_start();
require_once "../Includes/conexao.php";
require_once "../Includes/auth.php";
protegerPagina();

$usuario_nome = htmlspecialchars($_SESSION["usuario_nome"] ?? "Usuário");

// ler colunas da tabela imoveis (ajuste dinâmico)
try {
    $stmtCols = $conn->query("SHOW COLUMNS FROM imoveis");
    $cols = $stmtCols->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erro ao ler estrutura da tabela imoveis: " . $e->getMessage());
}

// determinar pk
$pk = 'id';
foreach ($cols as $c) {
    if (!empty($c['Key']) && strtoupper($c['Key']) === 'PRI') {
        $pk = $c['Field'];
        break;
    }
}

// imagem field preferida
$imgField = null;
$possibleImages = ['imagem_principal', 'foto_principal', 'imagem', 'foto'];
foreach ($cols as $c) {
    if (in_array($c['Field'], $possibleImages, true)) {
        $imgField = $c['Field'];
        break;
    }
}

function safeImagePath($row, $imgField)
{
    if (!$imgField || empty($row[$imgField])) {
        return '../Assets/Imagens/no-image.jpg';
    }
    $path = trim($row[$imgField]);
    $path = str_replace('\\', '/', $path);
    if (!str_starts_with($path, '../')) {
        $path = '../' . $path;
    }
    $path = preg_replace('#(\.\./)+Uploads#', '../Uploads', $path);
    return htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
}

// cache destaques 24h
$cache_file = __DIR__ . "/../Back-end/cache_destaques.json";
$cache_time = 24 * 60 * 60;
$destaques = [];
try {
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
        $destaques = json_decode(file_get_contents($cache_file), true) ?: [];
    } else {
        $selectImg = $imgField ? $imgField : 'NULL AS imagem';
        $sql = "SELECT {$pk} AS id_imovel, titulo, tipo_imovel, provincia, preco, {$selectImg} FROM imoveis ORDER BY RAND() LIMIT 3";
        $stmt = $conn->query($sql);
        $destaques = $stmt->fetchAll(PDO::FETCH_ASSOC);
        file_put_contents($cache_file, json_encode($destaques));
    }
} catch (Exception $e) {
    error_log("Erro ao buscar destaques: " . $e->getMessage());
    $destaques = [];
}

// outros imoveis
$outros = [];
try {
    $orderBy = 'data_publicacao DESC';
    $selectImg = $imgField ? $imgField : 'NULL AS imagem';
    $sql2 = "SELECT {$pk} AS id_imovel, titulo, tipo_imovel, provincia, preco, {$selectImg} FROM imoveis ORDER BY {$orderBy}";
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

    <section class="imoveis destaque">
        <h2>Imóveis em Destaque</h2>
        <div class="cards-container">
            <?php if (!empty($destaques)): ?>
                <?php foreach ($destaques as $imovel):
                    $img = safeImagePath($imovel, $imgField);
                    ?>
                    <div class="card">
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($imovel['titulo'] ?? 'Imóvel') ?>">
                        <h3><?= htmlspecialchars($imovel['titulo'] ?? ($imovel['tipo_imovel'] ?? 'Tipo')) ?> -
                            <?= htmlspecialchars($imovel['provincia'] ?? '') ?>
                        </h3>
                        <p>Preço: <?= isset($imovel['preco']) ? number_format($imovel['preco'], 0, ',', '.') . " MT" : "—" ?>
                        </p>
                        <button onclick="location.href='detalhes_imovel.php?id=<?= urlencode($imovel['id_imovel']) ?>'">Ver
                            Detalhes</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum imóvel em destaque no momento.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="imoveis outros">
        <h2>Outros Imóveis</h2>
        <div class="cards-container">
            <?php if (!empty($outros)): ?>
                <?php foreach ($outros as $imovel):
                    $img = safeImagePath($imovel, $imgField);
                    ?>
                    <div class="card">
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($imovel['titulo'] ?? 'Imóvel') ?>">
                        <h3><?= htmlspecialchars($imovel['tipo_imovel']) ?> -
                            <?= htmlspecialchars($imovel['provincia'] ?? '') ?>
                        </h3>
                        <p>Preço: <?= isset($imovel['preco']) ? number_format($imovel['preco'], 0, ',', '.') . " MT" : "—" ?>
                        </p>
                        <button onclick="location.href='detalhes_imovel.php?id=<?= urlencode($imovel['id_imovel']) ?>'">Ver
                            Detalhes</button>
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
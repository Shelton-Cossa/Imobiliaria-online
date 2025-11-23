<?php
session_start();
require_once "../Includes/conexao.php";

// Bloquear acesso se não estiver logado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../Paginas/login.php");
    exit();
}

$usuario_nome = htmlspecialchars($_SESSION["usuario_nome"] ?? "Usuário");

// -----------------------------
// Função utilitária: caminho seguro de imagem
// -----------------------------
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

// -----------------------------
// Detectar campo de imagem e colunas
// -----------------------------
try {
    $stmtCols = $conn->query("SHOW COLUMNS FROM imoveis");
    $cols = $stmtCols->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erro ao ler estrutura da tabela imoveis: " . $e->getMessage());
}

$pk = null;
foreach ($cols as $c) {
    if (strtoupper($c['Key']) === 'PRI') {
        $pk = $c['Field'];
        break;
    }
}
if (!$pk) $pk = 'id';

$possibleImages = ['imagem_principal', 'foto_principal', 'imagem', 'foto'];
$imgField = null;
foreach ($cols as $c) {
    if (in_array($c['Field'], $possibleImages)) {
        $imgField = $c['Field'];
        break;
    }
}

// -----------------------------
// Montar consulta dinâmica
// -----------------------------
$where = [];
$params = [];

if (!empty($_GET['provincia'])) {
    $where[] = "provincia = :provincia";
    $params[':provincia'] = $_GET['provincia'];
}
if (!empty($_GET['tipo'])) {
    $where[] = "tipo_imovel = :tipo";
    $params[':tipo'] = $_GET['tipo'];
}
if (!empty($_GET['finalidade'])) {
    $where[] = "finalidade = :finalidade";
    $params[':finalidade'] = $_GET['finalidade'];
}
if (!empty($_GET['preco_min'])) {
    $where[] = "preco >= :preco_min";
    $params[':preco_min'] = $_GET['preco_min'];
}
if (!empty($_GET['preco_max'])) {
    $where[] = "preco <= :preco_max";
    $params[':preco_max'] = $_GET['preco_max'];
}

$cond = count($where) ? "WHERE " . implode(" AND ", $where) : "";
$sql = "SELECT {$pk} AS id_imovel, tipo_imovel, provincia, preco, finalidade, " . ($imgField ? $imgField : "NULL AS imagem") . " 
        FROM imoveis $cond ORDER BY preco ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisar Imóveis</title>
    <link rel="stylesheet" href="../Assets/CSS/pesquisar_imoveis.css">
</head>
<body>
    <?php include "../Includes/header.php"; ?>

    <!-- Filtros -->
    <section class="filtros">
        <h2>Pesquisar Imóveis</h2>
        <form method="get" class="form-filtros">
            <select name="provincia">
                <option value="">Selecione a Província</option>
                <?php
                $provincias = ["cabo_delgado","niassa","nampula","zambezia","tete","manica","sofala","inhambane","gaza","maputo_provincia","maputo_cidade"];
                foreach ($provincias as $p):
                    $sel = (($_GET['provincia'] ?? '') === $p) ? "selected" : "";
                    echo "<option value='$p' $sel>" . ucwords(str_replace("_", " ", $p)) . "</option>";
                endforeach;
                ?>
            </select>
            <select name="tipo">
                <option value="">Tipo de Imóvel</option>
                <?php
                $tipos = ["casa","apartamento","escritorio","terreno","loja","armazem","vivenda","outro"];
                foreach ($tipos as $t):
                    $sel = (($_GET['tipo'] ?? '') === $t) ? "selected" : "";
                    echo "<option value='$t' $sel>" . ucfirst($t) . "</option>";
                endforeach;
                ?>
            </select>
            <input type="number" name="preco_min" placeholder="Preço mínimo" value="<?= htmlspecialchars($_GET['preco_min'] ?? '') ?>">
            <input type="number" name="preco_max" placeholder="Preço máximo" value="<?= htmlspecialchars($_GET['preco_max'] ?? '') ?>">
            <select name="finalidade">
                <option value="">Finalidade</option>
                <option value="venda" <?= (($_GET['finalidade'] ?? '') === 'venda') ? 'selected' : '' ?>>Venda</option>
                <option value="aluguel" <?= (($_GET['finalidade'] ?? '') === 'aluguel') ? 'selected' : '' ?>>Aluguel</option>
            </select>
            <button type="submit">Pesquisar</button>
        </form>
    </section>

    <!-- Resultados -->
    <section class="resultados">
        <h2>Resultados da Pesquisa</h2>
        <div class="cards-container">
            <?php if (!empty($resultados)): ?>
                <?php foreach ($resultados as $imovel):
                    $img = safeImagePath($imovel, $imgField);
                ?>
                <div class="card">
                    <img src="<?= $img ?>" alt="<?= htmlspecialchars($imovel['tipo_imovel'] ?? 'Imóvel') ?>">
                    <h3><?= htmlspecialchars(ucfirst($imovel['tipo_imovel'] ?? 'Tipo')) ?> - <?= htmlspecialchars($imovel['provincia'] ?? '') ?></h3>
                    <p>Preço: <?= isset($imovel['preco']) ? number_format($imovel['preco'], 0, ',', '.') . " MT" : "—" ?></p>
                    <p>Finalidade: <?= htmlspecialchars($imovel['finalidade'] ?? '—') ?></p>
                    <button onclick="location.href='detalhes_imovel.php?id=<?= urlencode($imovel['id_imovel']) ?>'">Ver Detalhes</button>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="msg erro">Nenhum imóvel encontrado com os filtros aplicados.</p>
            <?php endif; ?>
        </div>
    </section>

    <?php include "../Includes/footer.php"; ?>
</body>
</html>

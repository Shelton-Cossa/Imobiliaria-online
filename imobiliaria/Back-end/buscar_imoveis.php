<?php
require_once "../Includes/conexao.php";

$busca = $_GET["q"] ?? "";

$stmt = $conn->prepare("SELECT * FROM imoveis WHERE titulo LIKE :q OR descricao LIKE :q OR provincia LIKE :q");
$stmt->execute([":q" => "%$busca%"]);
$imoveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

header("Content-Type: application/json");
echo json_encode($imoveis);
?>
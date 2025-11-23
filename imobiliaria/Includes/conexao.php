<?php
// Includes/conexao.php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "imobiliaria_online";
$port = 3306;

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Em ambiente de dev pode mostrar, em prod logue e mostre mensagem genérica
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>
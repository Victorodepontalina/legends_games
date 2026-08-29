<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "legends_games_1";

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Erro de conexão com o banco de dados: " . $conn->connect_error);
}

// Criamos um alias (apelido) para não quebrar arquivos que usavam a variável $conexao em vez de $conn
$conexao = $conn;
?>
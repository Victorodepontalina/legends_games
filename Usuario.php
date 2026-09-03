<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db = "legends_games_1";

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit();
}

$ID_Usuario = $_SESSION['ID_Usuario'];

// Busca dados do usuário no banco
$sql = "SELECT * FROM usuario WHERE ID_Usuario = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Erro no prepare SQL: " . $conn->error);
}

$stmt->bind_param("i", $ID_Usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $usuario = $resultado->fetch_assoc();
} else {
    die("Usuário não encontrado.");
}

// Suporte para variação do nome das colunas no banco
$nomeUsuario = $usuario['Nome'] ?? $usuario['nome'] ?? 'Usuário';
$emailUsuario = $usuario['Email'] ?? $usuario['email'] ?? 'Não informado';

// FOTO DE PERFIL PADRÃO
$fotoUsuario = !empty($usuario['Foto'] ?? $usuario['foto'] ?? '')
    ? ($usuario['Foto'] ?? $usuario['foto'])
    : 'img/perfil_padrao.png';

// Estatísticas
$jogos = 24;
$compras = 12;
$favoritos = 8;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Minha Conta - Legends Games</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: radial-gradient(circle at top, #1b2838, #0a0d12);
    color: #fff;
    min-height: 100vh;
}

/* TOPBAR */
.topo {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 40px;
    background: rgba(13, 15, 19, 0.95);
    border-bottom: 2px solid #FFD700;
    backdrop-filter: blur(10px);
}

.logo {
    font-size: 26px;
    font-weight: 800;
    color: #FFD700;
    letter-spacing: 1px;
}

.usuario-topo {
    display: flex;
    align-items: center;
    gap: 15px;
}

.usuario-topo img {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    border: 2px solid #FFD700;
    object-fit: cover;
}

.usuario-topo span {
    font-weight: 600;
    color: #eee;
}

.logout {
    background: #FFD700;
    color: #0d0f13;
    border: none;
    padding: 8px 18px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    text-decoration: none;
    transition: 0.3s;
}

.logout:hover {
    background: #e6c200;
    transform: translateY(-2px);
}

/* LAYOUT CONTAINER */
.container {
    display: flex;
    max-width: 1300px;
    margin: 30px auto;
    gap: 30px;
    padding: 0 20px;
}

/* MENU LATERAL */
.menu {
    width: 240px;
    background: rgba(20, 24, 31, 0.8);
    padding: 20px 10px;
    border-radius: 16px;
    border: 1px solid rgba(255, 215, 0, 0.1);
    height: fit-content;
}

.menu ul {
    list-style: none;
}

.menu li a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    color: #aaa;
    text-decoration: none;
    font-weight: 600;
    border-radius: 10px;
    transition: all 0.3s ease;
    margin-bottom: 8px;
}

.menu li.ativo a,
.menu li a:hover {
    background: rgba(255, 215, 0, 0.15);
    color: #FFD700;
    border-left: 4px solid #FFD700;
}

/* CONTEÚDO PRINCIPAL */
.conteudo {
    flex: 1;
}

/* CARTÃO DE PERFIL */
.perfil {
    background: rgba(20, 24, 31, 0.9);
    border-radius: 20px;
    padding: 35px;
    display: flex;
    align-items: center;
    gap: 30px;
    border: 1px solid rgba(255, 215, 0, 0.2);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    margin-bottom: 30px;
}

.perfil-img-container {
    position: relative;
}

.perfil img {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    border: 3px solid #FFD700;
    object-fit: cover;
    box-shadow: 0 0 20px rgba(255, 215, 0, 0.2);
}

.info h1 {
    font-size: 32px;
    color: #fff;
    margin-bottom: 5px;
}

.info p {
    color: #88a0b5;
    font-size: 15px;
    margin-bottom: 12px;
}

.badge {
    display: inline-block;
    background: linear-gradient(135deg, #FFD700, #ffa500);
    color: #000;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* CARDS ESTATÍSTICAS */
.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    background: rgba(20, 24, 31, 0.7);
    padding: 25px;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    text-align: center;
    transition: transform 0.3s ease, border-color 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    border-color: rgba(255, 215, 0, 0.4);
}

.card h2 {
    font-size: 38px;
    color: #FFD700;
    font-weight: 800;
}

.card p {
    color: #9bb0c1;
    margin-top: 5px;
    font-size: 15px;
}

/* BOTÕES DE AÇÃO */
.acoes {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: bold;
    font-size: 14px;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-editar {
    background: #FFD700;
    color: #000;
}

.btn-editar:hover {
    background: #e6c200;
    box-shadow: 0 0 15px rgba(255, 215, 0, 0.4);
}

.btn-senha {
    background: transparent;
    color: #FFD700;
    border: 2px solid #FFD700;
}

.btn-senha:hover {
    background: rgba(255, 215, 0, 0.1);
}

.btn-sair-conta {
    background: #ff4d4d;
    color: #fff;
    border: 2px solid #ff4d4d;
}

.btn-sair-conta:hover {
    background: #cc0000;
    border-color: #cc0000;
    box-shadow: 0 0 15px rgba(255, 77, 77, 0.4);
}

@media (max-width: 850px) {
    .container {
        flex-direction: column;
    }

    .menu {
        width: 100%;
    }

    .perfil {
        flex-direction: column;
        text-align: center;
    }
}
</style>
</head>

<body>

<header class="topo">
    <div class="logo">Legends_Games</div>

    <div class="usuario-topo">

        <img
            src="<?= htmlspecialchars($fotoUsuario); ?>"
            alt="Avatar"
        >

        <span>
            <?= htmlspecialchars($nomeUsuario); ?>
        </span>

        <a href="tela_inicial.php" class="logout">
            Voltar
        </a>

    </div>
</header>

<div class="container">

<aside class="menu">
    <ul>

        <li class="ativo">
            <a href="#">👤 Minha Conta</a>
        </li>

        <li>
            <a href="biblioteca.php">🎮 Biblioteca</a>
        </li>

        <li>
            <a href="configuração.php">⚙️Configurações</a>
        </li>

        <li>
            <a href="carrinho.php">🛒 Carrinho</a>
        </li>

    </ul>
</aside>

<main class="conteudo">

<section class="perfil">

    <div class="perfil-img-container">

        <img
            src="<?= htmlspecialchars($fotoUsuario); ?>"
            alt="Foto Perfil"
        >

    </div>

    <div class="info">

        <h1>
            <?= htmlspecialchars($nomeUsuario); ?>
        </h1>

        <p>
            Email: <?= htmlspecialchars($emailUsuario); ?>
        </p>

        <span class="badge">
            Membro VIP
        </span>

    </div>

</section>

<section class="stats">

    <div class="card">
        <h2><?= $jogos; ?></h2>
        <p>Jogos Comprados</p>
    </div>

    <div class="card">
        <h2><?= $compras; ?></h2>
        <p>Total de Compras</p>
    </div>

    <div class="card">
        <h2><?= $favoritos; ?></h2>
        <p>Favoritos</p>
    </div>

</section>

<div class="acoes">

    <button class="btn btn-editar">
        Editar Perfil
    </button>

    <button class="btn btn-senha">
        Alterar Senha
    </button>

    <a
        href="login.php"
        class="btn btn-sair-conta"
    >
        Sair da Conta
    </a>

</div>

</main>

</div>

</body>
</html>

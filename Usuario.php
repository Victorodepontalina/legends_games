<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db = "legends_games_1";

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

/*
|--------------------------------------------------------------------------
| VERIFICA LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['logado']) || $_SESSION['logado'] != true) {
    header("Location: login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| ID DO USUÁRIO
|--------------------------------------------------------------------------
*/

$ID_Usuario = $_SESSION['ID_Usuario'];

/*
|--------------------------------------------------------------------------
| BUSCA USUÁRIO (CORRIGIDO E PROTEGIDO)
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| ESTATÍSTICAS (EXEMPLO)
|--------------------------------------------------------------------------
*/

$jogos = 24;
$compras = 12;
$favoritos = 8;

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Minha Conta - Legends Games</title>

<style>

body{
    margin:0;
    font-family: Arial, sans-serif;
    background: radial-gradient(circle at top, #1b2838, #0f141a 70%);
    color:#FFD700;
}

.topo{
    display:flex;
    justify-content:center;
    align-items:center;
    position:relative;
    padding:20px;
    background: rgba(17,17,17,0.95);
}

.logo{
    font-size:42px;
    font-weight:bold;
    background: linear-gradient(90deg,#FFD700,#FFE066,#FFD700);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    border:3px solid #FFD700;
    padding:10px 30px;
    border-radius:12px;
}

.usuario-topo{
    position:absolute;
    right:20px;
    display:flex;
    align-items:center;
    gap:10px;
}

.usuario-topo img{
    width:50px;
    height:50px;
    border-radius:50%;
    border:2px solid #FFD700;
    object-fit:cover;
}

.logout{
    background:#FFD700;
    color:#000;
    border:none;
    padding:10px 15px;
    border-radius:10px;
    cursor:pointer;
    font-weight:bold;
}

.container{
    display:flex;
}

.menu{
    width:220px;
    background: rgba(17,17,17,0.9);
    padding:20px;
    margin:20px;
    border-radius:15px;
}

.menu ul{
    list-style:none;
    padding:0;
}

.menu li{
    margin:20px 0;
    padding:10px;
    border-radius:8px;
    transition:0.3s;
    cursor:pointer;
}

.menu li:hover{
    background:#FFD700;
    color:#000;
}

.conteudo{
    flex:1;
    padding:20px;
}

.perfil{
    background:#1c1c1c;
    border-radius:20px;
    padding:30px;
    display:flex;
    align-items:center;
    gap:30px;
    margin-bottom:30px;
}

.perfil img{
    width:140px;
    height:140px;
    border-radius:50%;
    border:4px solid #FFD700;
    object-fit:cover;
}

.info h1{
    margin:0;
    font-size:36px;
}

.info p{
    margin:8px 0;
    color:#ddd;
}

.badge{
    display:inline-block;
    margin-top:10px;
    background:#FFD700;
    color:#000;
    padding:8px 15px;
    border-radius:20px;
    font-weight:bold;
}

.stats{
    display:flex;
    gap:20px;
    flex-wrap:wrap;
}

.card{
    flex:1;
    min-width:200px;
    background:#222;
    padding:25px;
    border-radius:15px;
    text-align:center;
}

.card h2{
    font-size:40px;
    margin:0;
    color:#FFD700;
}

.card p{
    margin-top:10px;
    color:#ccc;
    font-size:18px;
}

.acoes{
    margin-top:30px;
    display:flex;
    gap:15px;
    flex-wrap:wrap;
}

.btn{
    padding:12px 20px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-weight:bold;
}

.btn-editar{
    background:#FFD700;
    color:#000;
}

.btn-senha{
    background:#333;
    color:#FFD700;
    border:1px solid #FFD700;
}

</style>
</head>

<body>

<header class="topo">

    <div class="logo">Legends_Games</div>

    <div class="usuario-topo">

        <img src="<?= !empty($usuario['foto']) ? $usuario['foto'] : 'imagens/user.png'; ?>">

        <span><?= htmlspecialchars($usuario['nome']); ?></span>

        <a href="logout.php">
            <button class="logout">Sair</button>
        </a>

    </div>

</header>

<div class="container">

<aside class="menu">
    <ul>
        <li>👤 Minha Conta</li>
        <li>🎮 Biblioteca</li>
        <li>❤️ Favoritos</li>
        <li>🛒 Carrinho</li>
        <li>⚙️ Configurações</li>
    </ul>
</aside>

<main class="conteudo">

<section class="perfil">

    <img src="<?= !empty($usuario['foto']) ? $usuario['foto'] : 'imagens/user.png'; ?>">

    <div class="info">

        <h1><?= htmlspecialchars($usuario['nome']); ?></h1>

        <p>Email: <?= htmlspecialchars($usuario['email']); ?></p>

        <div class="badge">Usuário</div>

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

    <button class="btn btn-editar">Editar Perfil</button>
    <button class="btn btn-senha">Alterar Senha</button>

</div>

</main>
</div>

</body>
</html>
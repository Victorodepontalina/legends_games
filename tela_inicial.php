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

$cadastroRealizado = isset($_SESSION['cadastro']) && $_SESSION['cadastro'] == true;

$games = [
    ["nome"=>"Minecraft","preco"=>99.99,"nota"=>4.8,"img"=>"imagens/minecraft_jogo.jpg"],
    ["nome"=>"Red Dead Redemption 2","preco"=>282.90,"nota"=>5.0,"img"=>"imagens/red_dead_redemption_2.jpg"],
    ["nome"=>"Elden Ring","preco"=>320.99,"nota"=>4.5,"img"=>"imagens/Elden_Ring.jpg"],
    ["nome"=>"Cyberpunk 2077","preco"=>349.90,"nota"=>4.8,"img"=>"imagens/cyberpunk.jpg"],
    ["nome"=>"Call of Duty: Warzone","preco"=>349.90,"nota"=>4.8,"img"=>"imagens/Call_of_Duty.jpg"],
    ["nome"=>"Call of Duty: Warzone","preco"=>349.90,"nota"=>4.8,"img"=>"imagens/Call_of_duty_3.png"],
];

$novos = [
    ["nome"=>"DOOM Eternal","preco"=>219.90,"nota"=>4.9,"img"=>"imagens/Dom.jpg"],
    ["nome"=>"God Of War: Ragnarock","preco"=>159.90,"nota"=>4.5,"img"=>"imagens/god_of_war.jpg"],
    ["nome"=>"Hades","preco"=>139.90,"nota"=>4.6,"img"=>"imagens/Hades.jpg"],
    ["nome"=>"Horizon Forbidden West","preco"=>199.90,"nota"=>4.8,"img"=>"imagens/horizon.jpg"],
    ["nome"=>"No Man's Sky","preco"=>119.90,"nota"=>4.4,"img"=>"imagens/não_sei.jpg"],
    ["nome"=>"Resident Evil 4","preco"=>179.90,"nota"=>4.7,"img"=>"imagens/Resident_Evil_4.jpg"]
];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Legends_Games Premium</title>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    background: radial-gradient(circle at top, #1b2838, #0f141a 70%);
    color: #FFD700;
}

.topo {
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    padding: 20px;
    background: rgba(17,17,17,0.95);
}

.logo {
    font-size: 42px;
    font-weight: bold;
    background: linear-gradient(90deg,#FFD700,#FFE066,#FFD700);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    padding: 10px 30px;
    border: 3px solid #FFD700;
    border-radius: 12px;
}

.auth {
    position: absolute;
    right: 20px;
}

.auth button {
    padding: 10px 20px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    font-weight: bold;
}

.login {
    background: #222;
    color: #FFD700;
}

.cadastro {
    background: #FFD700;
    color: #000;
}

.container {
    display: flex;
}

.menu {
    width: 200px;
    background: rgba(17,17,17,0.9);
    padding: 20px;
    margin: 20px;
    border-radius: 10px;
}

.menu li {
    margin: 20px 0;
}

.conteudo {
    flex: 1;
    padding: 20px;
}

.titulo {
    text-align: center;
    border: 2px solid #FFD700;
    padding: 10px;
    border-radius: 10px;
    font-size: 28px;
}

.games {
    display: flex;
    gap: 25px;
    flex-wrap: wrap;
    justify-content: center;
}

.card {
    width: 240px;
    background: #222;
    border-radius: 15px;
    overflow: hidden;
    transition: 0.3s;
}

.card:hover {
    transform: scale(1.05);
}

.card img {
    width: 100%;
    height: 140px;
    object-fit: cover;
}

.card-content {
    padding: 10px;
}

.top-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nota {
    background: gold;
    color: black;
    padding: 3px 8px;
    border-radius: 8px;
    font-size: 14px;
}

.preco {
    margin-top: 10px;
}
</style>
</head>

<body>

<header class="topo">
    <div class="logo">Legends_Games</div>

    <div class="auth">

        <?php if(!isset($_SESSION['logado']) || $_SESSION['logado'] != true){ ?>
            
            <!-- 🔥 BOTÃO LOGIN AGORA FUNCIONA -->
            <a href="login.php">
                <button class="login">Login</button>
            </a>

            <?php if(!$cadastroRealizado){ ?>
                <a href="cadastro.php">
                    <button class="cadastro">Cadastrar</button>
                </a>
            <?php } ?>

        <?php } ?>

    </div>
</header>

<div class="container">

<aside class="menu">
    <ul>
        <li>🎮 Categoria</li>
        <li>📚 Biblioteca</li>
        <li>⚙️ Configurações</li>
        <li>🛒 Carrinho</li>
    </ul>
</aside>

<main class="conteudo">

<h1 class="titulo">DESTAQUES E RECOMENDADOS</h1>

<div class="games">
<?php foreach($games as $g){ ?>
<div class="card">
    <img src="<?= $g['img']; ?>">
    <div class="card-content">
        <div class="top-info">
            <h3><?= htmlspecialchars($g['nome']); ?></h3>
            <div class="nota">⭐ <?= $g['nota']; ?></div>
        </div>
        <p class="preco">R$ <?= number_format($g['preco'],2,',','.'); ?></p>
    </div>
</div>
<?php } ?>
</div>

<h1 class="titulo">NOVOS LANÇAMENTOS</h1>

<div class="games">
<?php foreach($novos as $n){ ?>
<div class="card">
    <img src="<?= $n['img']; ?>">
    <div class="card-content">
        <div class="top-info">
            <h3><?= htmlspecialchars($n['nome']); ?></h3>
            <div class="nota">⭐ <?= $n['nota']; ?></div>
        </div>
        <p class="preco">R$ <?= number_format($n['preco'],2,',','.'); ?></p>
    </div>
</div>
<?php } ?>
</div>

</main>
</div>

</body>
</html>
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

/* ADICIONAR AO CARRINHO */
if (isset($_POST['adicionar'])) {

    $nome = $_POST['nome'];
    $preco = (float)$_POST['preco'];
    $img = $_POST['img'];

    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }

    $existe = false;

    foreach ($_SESSION['carrinho'] as &$item) {

        if ($item['nome'] == $nome) {
            $item['quantidade']++;
            $existe = true;
            break;
        }
    }

    if (!$existe) {

        $_SESSION['carrinho'][] = [
            "nome" => $nome,
            "preco" => $preco,
            "img" => $img,
            "quantidade" => 1
        ];
    }

    header("Location: tela_inicial.php");
    exit;
}

$cadastroRealizado =
    isset($_SESSION['cadastro']) &&
    $_SESSION['cadastro'] == true;

$games = [
    ["Minecraft",99.99,4.8,"imagens/minecraft_jogo.jpg"],
    ["Red Dead Redemption 2",282.90,5.0,"imagens/red_dead_redemption_2.jpg"],
    ["Elden Ring",320.99,4.5,"imagens/Elden_Ring.jpg"],
    ["Cyberpunk 2077",349.90,4.8,"imagens/cyberpunk.jpg"],
    ["Call of Duty: Warzone",349.90,4.8,"imagens/Call_of_Duty.jpg"],
    ["Call of Duty: Warzone 3",349.90,4.8,"imagens/Call_of_duty_3.png"]
];

$novos = [
    ["DOOM Eternal",219.90,4.9,"imagens/Dom.jpg"],
    ["God Of War: Ragnarock",159.90,4.5,"imagens/god_of_war.jpg"],
    ["Hades",139.90,4.6,"imagens/Hades.jpg"],
    ["Horizon Forbidden West",199.90,4.8,"imagens/horizon.jpg"],
    ["No Man's Sky",119.90,4.4,"imagens/não_sei.jpg"],
    ["Resident Evil 4",179.90,4.7,"imagens/Resident_Evil_4.jpg"]
];

$qtdCarrinho = 0;

if (isset($_SESSION['carrinho'])) {

    foreach ($_SESSION['carrinho'] as $item) {
        $qtdCarrinho += $item['quantidade'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">

<title>Legends_Games</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial;
    background: radial-gradient(circle at top,#1b2838,#0f141a);
    color: #FFD700;
}

.topo {
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    padding: 20px;
    background: #111;
}

.logo {
    font-size: 42px;
    font-weight: bold;
    border: 3px solid gold;
    padding: 10px 30px;
    border-radius: 12px;
}

.auth {
    position: absolute;
    right: 20px;
}

.auth button {
    padding: 10px 20px;
    border: 0;
    border-radius: 10px;
    font-weight: bold;
    cursor: pointer;
}

.login {
    background: #222;
    color: gold;
}

.cadastro {
    background: gold;
    color: black;
}

.container {
    display: flex;
}

.menu {
    width: 200px;
    background: #111;
    padding: 20px;
    margin: 20px;
    border-radius: 10px;
}

.menu li {
    margin: 20px 0;
}

.menu a {
    color: inherit;
    text-decoration: none;
}

.conteudo {
    flex: 1;
    padding: 20px;
}

.titulo {
    text-align: center;
    border: 2px solid gold;
    padding: 10px;
    border-radius: 10px;
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
    transition: .3s;
}

.card:hover {
    transform: scale(1.04);
}

.card img {
    width: 100%;
    height: 140px;
    object-fit: cover;
}

.card-content {
    padding: 10px;
}

.card h3 {
    color: white;
}

.nota {
    background: gold;
    color: black;
    padding: 3px 8px;
    border-radius: 8px;
}

.preco {
    color: gold;
    font-size: 18px;
}

.detalhes {
    display: block;
    background: #333;
    color: white;
    text-align: center;
    padding: 9px;
    text-decoration: none;
    border-radius: 8px;
    margin-top: 8px;
}

.add {
    width: 100%;
    padding: 10px;
    background: gold;
    color: black;
    border: 0;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    margin-top: 8px;
}

.add:hover {
    background: white;
}

.carrinho {
    color: gold;
    text-decoration: none;
    font-weight: bold;
}

</style>

</head>

<body>

<header class="topo">

    <div class="logo">
        Legends_Games
    </div>

    <div class="auth">

        <a href="carrinho.php" class="carrinho">
            🛒 Carrinho (<?= $qtdCarrinho ?>)
        </a>

        <?php if (!isset($_SESSION['logado']) || !$_SESSION['logado']) { ?>

            <a href="login.php">
                <button class="login">Login</button>
            </a>

            <?php if (!$cadastroRealizado) { ?>

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

<li>
<a href="Usuario.php">👤 Minha Conta</a>
</li>

<li>🎮 Categoria</li>

<li>
<a href="biblioteca.php">📚 Biblioteca</a>
</li>

<li>
    <a href="configuração.php">⚙️Configurações</a>
</li>

</ul>

</aside>


<main class="conteudo">


<h1 class="titulo">
DESTAQUES E RECOMENDADOS
</h1>


<div class="games">

<?php foreach ($games as $g) { ?>

<div class="card">

<img src="<?= htmlspecialchars($g[3]) ?>"
onerror="this.src='https://via.placeholder.com/300x150/111111/FFD700?text=Sem+Capa'">

<div class="card-content">

<h3>
<?= htmlspecialchars($g[0]) ?>
</h3>

<div class="nota">
⭐ <?= $g[2] ?>
</div>

<p class="preco">
R$ <?= number_format($g[1],2,',','.') ?>
</p>


<a
class="detalhes"
href="tela_de_jogo.php?nome=<?= urlencode($g[0]) ?>">
🎮 Ver jogo
</a>


<form method="post">

<input type="hidden"
name="nome"
value="<?= htmlspecialchars($g[0]) ?>">

<input type="hidden"
name="preco"
value="<?= $g[1] ?>">

<input type="hidden"
name="img"
value="<?= htmlspecialchars($g[3]) ?>">

<button class="add" name="adicionar">
🛒 Adicionar ao carrinho
</button>

</form>

</div>

</div>

<?php } ?>

</div>


<h1 class="titulo">
NOVOS LANÇAMENTOS
</h1>


<div class="games">

<?php foreach ($novos as $n) { ?>

<div class="card">

<img src="<?= htmlspecialchars($n[3]) ?>"
onerror="this.src='https://via.placeholder.com/300x150/111111/FFD700?text=Sem+Capa'">

<div class="card-content">

<h3>
<?= htmlspecialchars($n[0]) ?>
</h3>

<div class="nota">
⭐ <?= $n[2] ?>
</div>

<p class="preco">
R$ <?= number_format($n[1],2,',','.') ?>
</p>


<a
class="detalhes"
href="tela_de_jogo.php?nome=<?= urlencode($n[0]) ?>">
🎮 Ver jogo
</a>


<form method="post">

<input type="hidden"
name="nome"
value="<?= htmlspecialchars($n[0]) ?>">

<input type="hidden"
name="preco"
value="<?= $n[1] ?>">

<input type="hidden"
name="img"
value="<?= htmlspecialchars($n[3]) ?>">

<button class="add" name="adicionar">
🛒 Adicionar ao carrinho
</button>

</form>

</div>

</div>

<?php } ?>

</div>

</main>

</div>

</body>
</html>
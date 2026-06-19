<?php
session_start();

if (!isset($_SESSION["ID_Usuario"])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION["ID_Usuario"];

$usuario_foto = "https://cdn-icons-png.flaticon.com/512/149/149071.png";

$host = "localhost";
$db   = "legends_games_1";
$user = "root";
$pass = "";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}

$sql = "
SELECT
    b.ID_Biblioteca,
    j.ID_jogo,
    j.Nome,
    j.Capa
FROM biblioteca b
INNER JOIN jogo j
    ON b.ID_jogo = j.ID_jogo
WHERE b.ID_usuario = :usuario
ORDER BY j.Nome
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'usuario' => $id_usuario
]);

$biblioteca = $stmt->fetchAll();

$busca = $_GET['busca'] ?? '';

if ($busca != '') {
    $biblioteca = array_filter($biblioteca, function ($jogo) use ($busca) {
        return stripos($jogo['Nome'], $busca) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Minha Biblioteca</title>

<style>

body{
    background: linear-gradient(135deg,#000000 0%,#111111 40%,#ffd700 80%);
    color:#ffd700;
    font-family:Segoe UI;
    margin:0;
    min-height:100vh;
}

header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    background:rgba(0,0,0,0.85);
    padding:15px;
    border-bottom:2px solid #ffd700;
    position:relative;
}

header h1{
    position:absolute;
    left:50%;
    transform:translateX(-50%);
    margin:0;
    font-size:32px;
}

.voltar{
    color:#ffd700;
    text-decoration:none;
    font-size:30px;
    font-weight:bold;
}

.voltar:hover{
    color:white;
}

.foto{
    width:45px;
    height:45px;
    border-radius:50%;
    border:2px solid #ffd700;
}

main{
    padding:20px;
}

.busca{
    width:300px;
    padding:12px;
    border:none;
    border-radius:20px;
    background:#111;
    color:#ffd700;
}

.grid{
    margin-top:25px;
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(220px,1fr));
    gap:20px;
}

.card{
    background:rgba(0,0,0,0.85);
    border:2px solid #ffd700;
    border-radius:10px;
    overflow:hidden;
    transition:0.3s;
}

.card:hover{
    transform:scale(1.04);
    box-shadow:0 0 15px #ffd700;
}

.card img{
    width:100%;
    height:130px;
    object-fit:cover;
}

.title{
    padding:10px;
    text-align:center;
    font-weight:bold;
}

.empty{
    text-align:center;
    margin-top:50px;
    font-size:22px;
}

.usuario-info{
    text-align:center;
    margin-bottom:20px;
}

.usuario-info h2{
    margin:0;
}

</style>

</head>

<body>

<header>

    <a href="tela_inicial.php" class="voltar">
        &#8592;
    </a>

    <h1>Legends Games</h1>

    <a href="configuração.php">
        <img src="<?= $usuario_foto ?>" class="foto">
    </a>

</header>

<main>

<div class="usuario-info">
</div>

<form method="GET">
    <input
        type="search"
        name="busca"
        class="busca"
        placeholder="Buscar jogo..."
        value="<?= htmlspecialchars($busca) ?>"
    >
</form>

<?php if(count($biblioteca) == 0): ?>

    <p class="empty">
        Você ainda não possui jogos na biblioteca.
    </p>

<?php else: ?>

<div class="grid">

<?php foreach($biblioteca as $jogo): ?>

<div class="card">

    <img
        src="<?= htmlspecialchars($jogo['Capa']) ?>"
        onerror="this.parentElement.style.display='none'"
    >

    <div class="title">
        <?= htmlspecialchars($jogo['Nome']) ?>
    </div>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

</main>

</body>
</html>
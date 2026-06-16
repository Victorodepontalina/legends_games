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

// 1. Capturar o nome do jogo clicado vindo na URL
$nomeJogo = isset($_GET['nome']) ? $_GET['nome'] : "ARK: Survival Evolved";

// 2. Valores Padrão (Fallback de segurança caso o jogo não venha do banco de dados)
$preco = "R$ 89,90";
$nota = "4.7";
$avaliacoes = "12.458";
$categoria = "Sobrevivência";
$descricao = "ARK é um jogo de sobrevivência com dinossauros em mundo aberto.";
$ram = "8GB";
$cpu = "i5";
$gpu = "GTX 670";
$img1 = "ark1.jpg";
$img2 = "ark2.jpg";
$img3 = "ark3.jpg";
$video_url = "https://www.youtube.com/embed/5fIAPcVdZO8";

// 3. Consulta para buscar dados dinâmicos da tabela 'jogos'
$stmt = $conn->prepare("SELECT * FROM jogos WHERE nome = ?");
if ($stmt) {
    $stmt->bind_param("s", $nomeJogo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $jogoBanco = $result->fetch_assoc();
        // Atualiza as variáveis se existirem colunas correspondentes na tabela do seu banco
        if (isset($jogoBanco['preco'])) $preco = "R$ " . number_format($jogoBanco['preco'], 2, ',', '.');
        if (isset($jogoBanco['nota'])) $nota = $jogoBanco['nota'];
        if (isset($jogoBanco['img'])) $img1 = $jogoBanco['img']; 
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($nomeJogo); ?></title>

<style>
*{margin:0;padding:0;box-sizing:border-box;}

body{
    font-family:Arial;
    background:radial-gradient(circle at top,#1b2838,#05070a);
    color:#fff;
}

/* TOPO */
.topo{
    background:#0d0f13;
    padding:25px 50px;
    display:flex;
    justify-content:space-between;
    border-bottom:2px solid gold;
}

.logo{
    color:gold;
    font-size:30px;
}

.voltar{
    background:#1a1f26;
    color:gold;
    padding:10px 15px;
    border-radius:10px;
    cursor:pointer;
    text-decoration: none;
    font-size: 14px;
}

/* TITULO */
.titulo{
    font-size:50px;
    color:gold;
    margin:40px;
}

/* LAYOUT */
.container{
    width:90%;
    margin:auto;
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:50px;
}

/* VIDEO */
.video iframe{
    width:100%;
    height:420px;
    border-radius:15px;
    border: none;
}

/* IMAGEM PRINCIPAL */
.imagem-principal img{
    width:100%;
    height:400px;
    object-fit:cover;
    border-radius:15px;
    margin-top:20px;
}

/* GALERIA */
.galeria{
    display:flex;
    gap:10px;
    margin-top:15px;
}

.galeria img{
    width:33%;
    height:120px;
    object-fit:cover;
    border-radius:10px;
    cursor:pointer;
    opacity:0.7;
}

.galeria img:hover{
    opacity:1;
    transform:scale(1.05);
}

/* COMENTARIOS */
.comentarios{
    margin-top:40px;
    background:#14181f;
    padding:20px;
    border-radius:15px;
}

textarea{
    width:100%;
    height:80px;
    border-radius:10px;
    padding:10px;
    background: #1f2a36;
    color: #fff;
    border: 1px solid #333;
}

.btn{
    margin-top:10px;
    background:gold;
    padding:10px;
    border:none;
    border-radius:10px;
    cursor: pointer;
    font-weight: bold;
}

.card{
    background:#1f2a36;
    padding:10px;
    margin-top:10px;
    border-radius:10px;
}

.nome{color:gold;}
.tempo{font-size:12px;color:#aaa;}

/* COMUNIDADE */
.comunidade{
    margin-top:40px;
}

.galeria-comunidade{
    display:flex;
    gap:10px;
}

.galeria-comunidade img{
    width:25%;
    height:120px;
    border-radius:10px;
    object-fit: cover;
}

/* SIDEBAR */
.sidebar{
    background:#14181f;
    padding:25px;
    border-radius:15px;
}

.preco{
    font-size:28px;
    color:gold;
}

/* AVALIAÇÃO */
.estrelas{
    color:gold;
    font-size:20px;
}

/* BOTÃO */
.botao{
    background:gold;
    padding:12px;
    text-align:center;
    border-radius:10px;
    margin:15px 0;
    color: #000;
    font-weight: bold;
    cursor: pointer;
}

/* RODAPÉ */
.rodape{
    margin-top:60px;
    background:#0d0f13;
    border-top:2px solid gold;
    padding:40px;
}

.rodape-container{
    display:flex;
    justify-content:space-between;
    width:90%;
    margin:auto;
    color:#ccc;
}

.rodape h3{color:gold;}

@media(max-width:900px){
.container{
    grid-template-columns:1fr;
}
}
</style>
</head>

<body>

<div class="topo">
    <div class="logo">Lendas_Games</div>
    <a href="tela_inicial.php" class="voltar">⬅ Voltar</a>
</div>

<h1 class="titulo"><?= htmlspecialchars($nomeJogo); ?></h1>

<div class="container">

<div>

<div class="video">
    <iframe src="<?= $video_url; ?>"></iframe>
</div>

<div class="imagem-principal">
    <img id="imgPrincipal" src="<?= $img1; ?>">
</div>

<div class="galeria">
    <img src="<?= $img1; ?>" onclick="trocar(this)">
    <img src="<?= $img2; ?>" onclick="trocar(this)">
    <img src="<?= $img3; ?>" onclick="trocar(this)">
</div>

<div class="comentarios">
    <h2 style="color:gold;">Comentários</h2>
    <textarea id="txt"></textarea>
    <button class="btn" onclick="add()">Enviar</button>
    <div id="lista"></div>
</div>

<div class="comunidade">
    <h2 style="color:gold;">Comunidade</h2>
    <div class="galeria-comunidade">
        <img src="com1.jpg">
        <img src="com2.jpg">
        <img src="com3.jpg">
        <img src="com4.jpg">
    </div>
</div>

</div>

<div class="sidebar">

<div class="preco"><?= $preco; ?></div>

<div class="estrelas">★★★★★</div>
<p><?= $nota; ?> (<?= $avaliacoes; ?> avaliações)</p>

<div class="botao">🛒 Comprar</div>

<p><strong>Categoria:</strong> <?= $categoria; ?></p>
<p style="margin-top: 10px; color: #eee;"><?= $descricao; ?></p>

<h3 style="color:gold; margin-top: 30px;">Requisitos</h3>
<p>RAM: <?= $ram; ?></p>
<p>CPU: <?= $cpu; ?></p>
<p>GPU: <?= $gpu; ?></p>

</div>

</div>

<footer class="rodape">
<div class="rodape-container">
    <div>
        <h3>Legends_Games</h3>
        <p>Explore novos mundos.</p>
    </div>
    <div>
        <p>Loja</p>
        <p>Biblioteca</p>
    </div>
    <div>
        <p>Suporte</p>
        <p>© 2026</p>
    </div>
</div>
</footer>

<script>
function trocar(img){
    document.getElementById("imgPrincipal").src = img.src;
}

function add(){
    let t = document.getElementById("txt").value;
    if(t=="") return;

    let d = document.createElement("div");
    d.className="card";
    d.innerHTML=`<div class="nome">Usuário</div><div>${t}</div><div class="tempo">agora</div>`;

    document.getElementById("lista").prepend(d);
    document.getElementById("txt").value="";
}
</script>

</body>
</html>
<?php
session_start();
require_once 'conexao.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Atualiza a tabela de comentários para aceitar Notas
$conexao->query("ALTER TABLE comentarios ADD COLUMN IF NOT EXISTS Nota INT DEFAULT 5");

$sqlCriarComentarios = "CREATE TABLE IF NOT EXISTS comentarios (
    ID_Comentario INT(11) NOT NULL AUTO_INCREMENT,
    ID_usuario INT(11) DEFAULT NULL,
    ID_jogo INT(11) NOT NULL,
    Comentario VARCHAR(1000) NOT NULL,
    Nota INT DEFAULT 5,
    Data_Comentario DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (ID_Comentario),
    KEY ID_usuario (ID_usuario),
    KEY ID_jogo (ID_jogo),
    CONSTRAINT comentarios_ibfk_usuario FOREIGN KEY (ID_usuario) REFERENCES usuario (ID_usuario) ON DELETE SET NULL,
    CONSTRAINT comentarios_ibfk_jogo FOREIGN KEY (ID_jogo) REFERENCES jogo (ID_jogo) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conexao->query($sqlCriarComentarios);

/* =========================================================
   BUSCAR JOGO NO BANCO DE DADOS
========================================================= */
$nome = $_GET['nome'] ?? "";

$sqlJogo = "SELECT j.*, c.Nome_Categoria FROM jogo j 
            LEFT JOIN categoria c ON j.ID_Categoria = c.ID_Categoria 
            WHERE j.Nome = ? LIMIT 1";

$stmt = $conexao->prepare($sqlJogo);
if (!$stmt) die("<h2 style='color:white; text-align:center;'>Erro ao consultar o jogo: " . $conexao->error . "</h2>");

$stmt->bind_param("s", $nome);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("<h2 style='color:white; text-align:center; margin-top:50px;'>Jogo não encontrado no banco de dados!</h2>");
}

$jogo = $resultado->fetch_assoc();
$stmt->close();

$idJogo = $jogo['ID_jogo'];
$preco = $jogo['Preco_Unitario'] ?? 0;
$capa = $jogo['Capa'] ?? '';
$descricao = !empty($jogo['Descricao']) ? $jogo['Descricao'] : "Explore o mundo de " . htmlspecialchars($nome) . ".";
$categoria = !empty($jogo['Nome_Categoria']) ? $jogo['Nome_Categoria'] : "Geral";
$video = $jogo['Video_Demonstrativo'] ?? '';
$temVideo = (!empty($video) && $video !== '0' && $video !== 'link_video');

$idUsuario = isset($_SESSION['ID_Usuario']) ? (int)$_SESSION['ID_Usuario'] : null;

/* =========================================================
   ENVIAR COMENTÁRIO E NOTA
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_comentario'])) {
    $comentario = trim($_POST['comentario'] ?? '');
    $nota = (int)($_POST['nota'] ?? 5);
    if($nota < 1) $nota = 1;
    if($nota > 5) $nota = 5;

    if ($comentario !== '') {
        $comentario = substr($comentario, 0, 1000);
        $stmtComentario = $conexao->prepare("INSERT INTO comentarios (ID_usuario, ID_jogo, Comentario, Nota, Data_Comentario) VALUES (?, ?, ?, ?, NOW())");
        if ($stmtComentario) {
            $stmtComentario->bind_param("iisi", $idUsuario, $idJogo, $comentario, $nota);
            $stmtComentario->execute();
            $stmtComentario->close();
        }
        header("Location: tela_de_jogo.php?nome=" . urlencode($nome));
        exit;
    }
}

/* =========================================================
   BUSCAR COMENTÁRIOS E CALCULAR MÉDIA
========================================================= */
$comentarios = [];
$somaNotas = 0;
$totalAvaliacoes = 0;

$sqlComentarios = "SELECT c.Comentario, c.Data_Comentario, c.Nota, COALESCE(u.Nome_Exibicao, u.Nome, 'Visitante') AS NomeUsuario, u.Foto_Perfil 
                   FROM comentarios c LEFT JOIN usuario u ON u.ID_usuario = c.ID_usuario 
                   WHERE c.ID_jogo = ? ORDER BY c.ID_Comentario DESC";
$stmtComentarios = $conexao->prepare($sqlComentarios);

if ($stmtComentarios) {
    $stmtComentarios->bind_param("i", $idJogo);
    $stmtComentarios->execute();
    $resComentarios = $stmtComentarios->get_result();
    while ($linha = $resComentarios->fetch_assoc()) {
        $comentarios[] = $linha;
        $nota = (int)($linha['Nota'] ?? 5);
        $somaNotas += $nota;
        $totalAvaliacoes++;
    }
    $stmtComentarios->close();
}

$media = $totalAvaliacoes > 0 ? round($somaNotas / $totalAvaliacoes, 1) : 0;

function desenharEstrelas($nota) {
    $nota = round($nota);
    return str_repeat('★', $nota) . str_repeat('☆', 5 - $nota);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($nome) ?> - Legends Games</title>
<style>
* { box-sizing: border-box; }
body { margin: 0; font-family: Arial, sans-serif; background: radial-gradient(circle at top, #1b2838, #05070a); color: white; }
header { padding: 20px; background: #0d0f13; border-bottom: 2px solid gold; display: flex; justify-content: space-between; align-items: center; }
.logo { color: gold; font-size: 28px; font-weight: bold; }
.voltar { color: gold; text-decoration: none; background: #222; padding: 10px 15px; border-radius: 8px; font-weight:bold;}
.voltar:hover { background: gold; color: black; }
h1 { width: 90%; margin: 35px auto; color: gold; font-size: 40px; display: flex; align-items: center; justify-content: space-between;}
.container { width: 90%; margin: auto; display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
.galeria { position: relative; width: 100%; height: 500px; background: #000; border-radius: 15px; overflow: hidden; }
.slide { display: none; width: 100%; height: 100%; }
.slide.ativo { display: block; }
.slide img, .slide iframe { width: 100%; height: 100%; object-fit: cover; border: 0; }
.seta { position: absolute; top: 50%; transform: translateY(-50%); width: 50px; height: 70px; background: rgba(0,0,0,.75); color: gold; border: 0; font-size: 35px; cursor: pointer; z-index: 10; }
.seta:hover { background: gold; color: black; }
.esquerda { left: 10px; }
.direita { right: 10px; }
.contador { position: absolute; bottom: 15px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,.8); color: gold; padding: 8px 15px; border-radius: 20px; z-index: 10; }
.miniaturas { display: flex; gap: 10px; margin-top: 15px; overflow-x: auto; }
.miniatura { width: 120px; height: 70px; object-fit: cover; border-radius: 8px; cursor: pointer; opacity: .6; border: 2px solid transparent; flex-shrink: 0; }
.miniatura:hover, .miniatura.selecionada { opacity: 1; border-color: gold; }
.box { background: #14181f; padding: 25px; border-radius: 15px; height: max-content; border: 1px solid #333;}
.preco { color: gold; font-size: 30px; font-weight: bold; }
.estrelas { color: gold; font-size: 26px; margin: 10px 0; }
.texto-media { font-size: 16px; color: #ccc; font-weight: normal; vertical-align: middle;}
.comprar { display: block; text-align: center; background: gold; color: black; padding: 14px; margin: 20px 0; border-radius: 10px; text-decoration: none; font-weight: bold; border:none; width: 100%; cursor: pointer; font-size: 16px;}
.comprar:hover { background: white; }
.ver-carrinho { display: block; text-align: center; background: #222; color: gold; border: 1px solid gold; padding: 12px; border-radius: 10px; text-decoration: none; font-weight: bold; }
.ver-carrinho:hover { background: gold; color: black; }
.comentarios { width: 90%; margin: 30px auto 50px; background: #14181f; padding: 25px; border-radius: 15px; border: 1px solid #333;}
.comentarios h2 { color: gold; margin-top: 0; border-bottom: 1px solid #333; padding-bottom:10px;}
textarea { width: 100%; background: #222; color: white; border: 1px solid #555; padding: 12px; border-radius: 8px; font-family: Arial; margin-bottom: 10px; height: 80px; resize: none; }
textarea:focus { outline: none; border-color: gold; }
.enviar { padding: 12px 25px; background: gold; color: black; border: 0; border-radius: 8px; font-weight: bold; cursor: pointer; }
.enviar:hover { background: white; }
.comentario { background: #222; padding: 18px; margin-top: 15px; border-radius: 10px; border-left: 4px solid gold; display: flex; gap: 15px;}
.avatar-comentario { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid gold; flex-shrink: 0;}
.conteudo-comentario { flex: 1; }
.usuario-comentario { color: gold; font-weight: bold; margin-bottom: 5px; display: block;}
.nota-comentario { color: gold; font-size: 14px; margin-bottom: 8px; }

/* NOVO SISTEMA DE ESTRELAS CLICÁVEIS */
.avaliacao-estrelas { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 5px; margin-bottom: 15px; }
.avaliacao-estrelas input { display: none; }
.avaliacao-estrelas label { font-size: 35px; color: #444; cursor: pointer; transition: color 0.2s; }
.avaliacao-estrelas input:checked ~ label, 
.avaliacao-estrelas label:hover, 
.avaliacao-estrelas label:hover ~ label { color: gold; }

@media(max-width: 800px) { .container { grid-template-columns: 1fr; } .galeria { height: 350px; } }
</style>
</head>
<body>

<header>
    <div class="logo">Legends_Games</div>
    <a href="tela_inicial.php" class="voltar">⬅ Voltar à Loja</a>
</header>

<h1>
    <?= htmlspecialchars($nome) ?>
    <?php if($totalAvaliacoes > 0): ?>
        <div style="font-size:24px; color:gold;">
            <?= desenharEstrelas($media) ?> <span style="font-size:16px; color:#aaa; font-weight:normal;">(<?= $media ?>/5)</span>
        </div>
    <?php endif; ?>
</h1>

<div class="container">
    <div>
        <div class="galeria">
            <div class="slide ativo">
                <img src="<?= htmlspecialchars($capa) ?>" onerror="this.src='https://via.placeholder.com/800x500/111111/FFD700?text=Capa'">
            </div>
            
            <?php if ($temVideo) { ?>
            <div class="slide">
                <iframe src="<?= htmlspecialchars($video) ?>" allowfullscreen></iframe>
            </div>
            <?php } ?>

            <?php if ($temVideo) { ?>
                <button type="button" class="seta esquerda" onclick="anterior()">❮</button>
                <button type="button" class="seta direita" onclick="proximo()">❯</button>
                <div class="contador" id="contador">1 / 2</div>
            <?php } ?>
        </div>

        <div class="miniaturas">
            <img class="miniatura selecionada" src="<?= htmlspecialchars($capa) ?>" onclick="irPara(0)" onerror="this.src='https://via.placeholder.com/120x70/111111/FFD700?text=Capa'">
            <?php if ($temVideo) { ?>
                <div class="miniatura" onclick="irPara(1)" style="background:#111; color:gold; display:flex; align-items:center; justify-content:center; font-weight:bold;">▶ VÍDEO</div>
            <?php } ?>
        </div>
    </div>

    <div class="box">
        <div class="preco">R$ <?= number_format($preco, 2, ',', '.') ?></div>
        
        <div class="estrelas">
            <?php if($totalAvaliacoes > 0): ?>
                <?= desenharEstrelas($media) ?> <span class="texto-media"><?= $totalAvaliacoes ?> avaliação(ões)</span>
            <?php else: ?>
                <span class="texto-media" style="color:#888;">Nenhuma avaliação ainda.</span>
            <?php endif; ?>
        </div>
        
        <form method="POST" action="carrinho.php?adicionar=1&nome=<?= urlencode($nome) ?>">
            <input type="hidden" name="preco" value="<?= $preco ?>">
            <input type="hidden" name="img" value="<?= htmlspecialchars($capa) ?>">
            <button type="submit" class="comprar">🛒 Adicionar ao Carrinho</button>
        </form>

        <a href="carrinho.php" class="ver-carrinho">🛒 Ver meu carrinho</a>
        
        <p><b>Categoria:</b> <?= htmlspecialchars($categoria) ?></p>
        <hr style="border:1px solid #333; margin: 15px 0;">
        <h3 style="color:gold; margin-top:0;">🎮 Sobre o jogo</h3>
        <p style="line-height:1.5; color:#ccc;"><?= htmlspecialchars($descricao) ?></p>
    </div>
</div>

<div class="comentarios">
    <h2>💬 Avaliações e Comentários</h2>
    
    <?php if($idUsuario): ?>
    <form method="POST" style="margin-bottom: 30px; background: #1a1e24; padding: 20px; border-radius: 10px;">
        <label style="color: gold; font-weight: bold; display:block; margin-bottom: 5px;">Que nota você dá para este jogo?</label>
        
        <!-- ESTRELAS INTERATIVAS -->
        <div class="avaliacao-estrelas">
            <input type="radio" id="star5" name="nota" value="5" required>
            <label for="star5" title="5 estrelas">★</label>
            <input type="radio" id="star4" name="nota" value="4">
            <label for="star4" title="4 estrelas">★</label>
            <input type="radio" id="star3" name="nota" value="3">
            <label for="star3" title="3 estrelas">★</label>
            <input type="radio" id="star2" name="nota" value="2">
            <label for="star2" title="2 estrelas">★</label>
            <input type="radio" id="star1" name="nota" value="1">
            <label for="star1" title="1 estrela">★</label>
        </div>

        <textarea name="comentario" placeholder="Escreva o que você achou do jogo..." required></textarea>
        <button type="submit" name="enviar_comentario" class="enviar">💬 Enviar Avaliação</button>
    </form>
    <?php else: ?>
    <p style="color: #aaa; background: #1a1e24; padding: 20px; border-radius: 10px;">Faça <a href="login.php" style="color: gold;">login</a> para avaliar este jogo.</p>
    <?php endif; ?>

    <div class="lista-comentarios">
        <?php if (count($comentarios) > 0): ?>
            <?php foreach ($comentarios as $c): ?>
                <?php $fotoUser = !empty($c['Foto_Perfil']) ? $c['Foto_Perfil'] : 'https://via.placeholder.com/50/222222/FFD700?text=U'; ?>
                <div class="comentario">
                    <img src="<?= htmlspecialchars($fotoUser) ?>" class="avatar-comentario" alt="Avatar">
                    <div class="conteudo-comentario">
                        <span class="usuario-comentario">
                            <?= htmlspecialchars($c['NomeUsuario'] ?? 'Visitante') ?> 
                            <small style="color:#888; font-weight:normal; font-size:12px; margin-left:5px;">- <?= date('d/m/Y H:i', strtotime($c['Data_Comentario'])) ?></small>
                        </span>
                        <div class="nota-comentario"><?= desenharEstrelas($c['Nota']) ?></div>
                        <div class="texto-comentario" style="line-height:1.4;"><?= htmlspecialchars($c['Comentario'] ?? '') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; color: #888; padding: 30px;">Seja o primeiro a avaliar este jogo!</div>
        <?php endif; ?>
    </div>
</div>

<script>
let atual = 0;
const slides = document.querySelectorAll(".slide");
const miniaturas = document.querySelectorAll(".miniatura");
const total = slides.length;

function mostrar(numero) {
    if(total <= 1) return;
    if (numero < 0) numero = total - 1;
    if (numero >= total) numero = 0;
    atual = numero;

    slides.forEach(slide => slide.classList.remove("ativo"));
    miniaturas.forEach(mini => mini.classList.remove("selecionada"));

    slides[atual].classList.add("ativo");
    if (miniaturas[atual]) miniaturas[atual].classList.add("selecionada");
    
    const contador = document.getElementById("contador");
    if(contador) contador.innerText = (atual + 1) + " / " + total;
}

function proximo() { mostrar(atual + 1); }
function anterior() { mostrar(atual - 1); }
function irPara(numero) { mostrar(numero); }
</script>

</body>
</html>
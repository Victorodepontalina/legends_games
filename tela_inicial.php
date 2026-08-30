<?php
session_start();
require_once 'conexao.php';

$conexao->query("CREATE TABLE IF NOT EXISTS favoritos (
    ID_Favorito INT AUTO_INCREMENT PRIMARY KEY,
    ID_usuario INT NOT NULL,
    ID_jogo INT NOT NULL,
    UNIQUE KEY (ID_usuario, ID_jogo),
    FOREIGN KEY (ID_usuario) REFERENCES usuario(ID_usuario) ON DELETE CASCADE,
    FOREIGN KEY (ID_jogo) REFERENCES jogo(ID_jogo) ON DELETE CASCADE
)");

$conexao->query("ALTER TABLE jogo ADD COLUMN IF NOT EXISTS Badge VARCHAR(50) DEFAULT ''");

// ADICIONAR AO CARRINHO
if (isset($_POST['adicionar'])) {
    $nome = $_POST['nome'];
    $preco = (float)$_POST['preco'];
    $img = $_POST['img'];
    if (!isset($_SESSION['carrinho'])) $_SESSION['carrinho'] = [];
    $existe = false;
    foreach ($_SESSION['carrinho'] as &$item) {
        if ($item['nome'] == $nome) { $item['quantidade']++; $existe = true; break; }
    }
    if (!$existe) $_SESSION['carrinho'][] = ["nome" => $nome, "preco" => $preco, "img" => $img, "quantidade" => 1];
    header("Location: tela_inicial.php");
    exit;
}

// ADICIONAR / REMOVER FAVORITO
if (isset($_POST['favoritar'])) {
    if (!isset($_SESSION['ID_Usuario'])) { header("Location: login.php"); exit; }
    $id_jogo_fav = (int)$_POST['id_jogo'];
    $id_user = (int)$_SESSION['ID_Usuario'];
    $check = $conexao->prepare("SELECT ID_Favorito FROM favoritos WHERE ID_usuario = ? AND ID_jogo = ?");
    $check->bind_param("ii", $id_user, $id_jogo_fav);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $del = $conexao->prepare("DELETE FROM favoritos WHERE ID_usuario = ? AND ID_jogo = ?");
        $del->bind_param("ii", $id_user, $id_jogo_fav);
        $del->execute();
    } else {
        $ins = $conexao->prepare("INSERT INTO favoritos (ID_usuario, ID_jogo) VALUES (?, ?)");
        $ins->bind_param("ii", $id_user, $id_jogo_fav);
        $ins->execute();
    }
    header("Location: tela_inicial.php");
    exit;
}

$foto_perfil = 'https://via.placeholder.com/45/222222/FFD700?text=User';
$nome_usuario = '';
$meus_favoritos = [];

if (isset($_SESSION['ID_Usuario'])) {
    $id_u = (int)$_SESSION['ID_Usuario'];
    $stmtUser = $conexao->prepare("SELECT Nome, Nome_Exibicao, Foto_Perfil FROM usuario WHERE ID_usuario = ?");
    $stmtUser->bind_param("i", $id_u);
    $stmtUser->execute();
    $resUser = $stmtUser->get_result()->fetch_assoc();
    if ($resUser) {
        $nome_usuario = !empty($resUser['Nome_Exibicao']) ? $resUser['Nome_Exibicao'] : explode(' ', trim($resUser['Nome']))[0];
        if (!empty($resUser['Foto_Perfil'])) $foto_perfil = $resUser['Foto_Perfil'];
    }
    $stmtUser->close();
    $resFav = $conexao->query("SELECT ID_jogo FROM favoritos WHERE ID_usuario = $id_u");
    while ($f = $resFav->fetch_assoc()) $meus_favoritos[] = $f['ID_jogo'];
}

$categorias = [];
$resCat = $conexao->query("SELECT * FROM categoria ORDER BY Nome_Categoria ASC");
if ($resCat) while ($row = $resCat->fetch_assoc()) $categorias[] = $row;

// CAPTURAR FILTROS E ORDENAÇÃO DA URL
$busca = $_GET['busca'] ?? '';
$filtro_categoria = $_GET['categoria'] ?? '';
$ordem = $_GET['ordem'] ?? '';

$sqlJogos = "SELECT ID_jogo, Nome, Preco_Unitario, Capa, Badge FROM jogo WHERE 1=1";
$params = [];
$tipos = "";

if ($busca !== '') {
    $sqlJogos .= " AND Nome LIKE ?";
    $params[] = "%" . $busca . "%";
    $tipos .= "s";
}
if ($filtro_categoria !== '') {
    $sqlJogos .= " AND ID_Categoria = ?";
    $params[] = $filtro_categoria;
    $tipos .= "i";
}

// APLICAR ORDENAÇÃO DINÂMICA
if ($ordem === 'menor_preco') {
    $sqlJogos .= " ORDER BY Preco_Unitario ASC";
} elseif ($ordem === 'maior_preco') {
    $sqlJogos .= " ORDER BY Preco_Unitario DESC";
} elseif ($ordem === 'az') {
    $sqlJogos .= " ORDER BY Nome ASC";
} elseif ($ordem === 'za') {
    $sqlJogos .= " ORDER BY Nome DESC";
} else {
    $sqlJogos .= " ORDER BY ID_jogo DESC"; // Mais recentes por padrão
}

$stmt = $conexao->prepare($sqlJogos);
if (!empty($params)) $stmt->bind_param($tipos, ...$params);
$stmt->execute();
$resultado = $stmt->get_result();
$jogos_banco = [];
if ($resultado) while ($row = $resultado->fetch_assoc()) $jogos_banco[] = $row;
$stmt->close();

$jogos_destaque = [];
$resDestaque = $conexao->query("SELECT ID_jogo, Nome, Preco_Unitario, Capa FROM jogo ORDER BY ID_jogo DESC LIMIT 3");
if ($resDestaque) while ($row = $resDestaque->fetch_assoc()) $jogos_destaque[] = $row;

$qtdCarrinho = 0;
if (isset($_SESSION['carrinho'])) {
    foreach ($_SESSION['carrinho'] as $item) $qtdCarrinho += $item['quantidade'];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Legends_Games</title>
<style>
* { box-sizing: border-box; }
body { margin: 0; font-family: Arial; background: radial-gradient(circle at top,#1b2838,#0f141a); color: #FFD700; }
.topo { display: flex; justify-content: center; align-items: center; position: relative; padding: 20px; background: #111; }
.logo { font-size: 42px; font-weight: bold; border: 3px solid gold; padding: 10px 30px; border-radius: 12px; }
.perfil-topo { position: absolute; left: 20px; display: flex; align-items: center; gap: 12px; text-decoration: none; color: white; transition: 0.3s; }
.perfil-topo:hover { color: gold; transform: scale(1.05); }
.perfil-topo img { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid gold; }
.auth { position: absolute; right: 20px; }
.auth button, .auth a.btn { padding: 10px 20px; border: 0; border-radius: 10px; font-weight: bold; cursor: pointer; text-decoration: none;}
.login { background: #222; color: gold; }
.cadastro { background: gold; color: black; }
.admin-btn { background: #550000; color: white; margin-right: 10px; border: 1px solid red; }
.admin-btn:hover { background: red; color: white; }
.container { display: flex; }
.menu { width: 200px; background: #111; padding: 20px; margin: 20px; border-radius: 10px; height: fit-content; }
.menu li { margin: 20px 0; list-style: none; }
.menu a { color: inherit; text-decoration: none; font-weight: bold; }
.menu a:hover { color: white; }
.conteudo { flex: 1; padding: 20px; }
.titulo { text-align: center; border: 2px solid gold; padding: 10px; border-radius: 10px; margin-bottom: 30px;}
.games { display: flex; gap: 25px; flex-wrap: wrap; justify-content: center; }
.card { width: 240px; background: #222; border-radius: 15px; overflow: hidden; transition: .3s; position: relative;}
.card:hover { transform: scale(1.04); box-shadow: 0 0 15px rgba(255, 215, 0, 0.4); }
.card img { width: 100%; height: 140px; object-fit: cover; }
.card-content { padding: 15px; }
.card h3 { color: white; margin-top: 0; font-size: 18px; height: 40px; overflow: hidden; }
.preco { color: gold; font-size: 20px; font-weight: bold; margin: 10px 0; }
.detalhes { display: block; background: #333; color: white; text-align: center; padding: 10px; text-decoration: none; border-radius: 8px; margin-top: 8px; transition: 0.3s; }
.detalhes:hover { background: #555; }
.add { width: 100%; padding: 10px; background: gold; color: black; border: 0; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 8px; transition: 0.3s; }
.add:hover { background: white; }
.btn-fav { position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.6); border: none; font-size: 24px; cursor: pointer; border-radius: 50%; width: 40px; height: 40px; display: flex; justify-content: center; align-items: center; transition: 0.2s; z-index: 20;}
.btn-fav:hover { background: rgba(0,0,0,0.9); transform: scale(1.1); }
.badge-faixa { position: absolute; top: 15px; left: -35px; background: #ff4d4d; color: white; padding: 5px 40px; font-size: 12px; font-weight: bold; text-transform: uppercase; transform: rotate(-45deg); box-shadow: 0 2px 4px rgba(0,0,0,0.5); z-index: 10; letter-spacing: 1px; pointer-events: none;}
.badge-novo { background: #00ff88; color: black; }
.badge-hot { background: #ff9900; color: black; }
.carrinho { color: gold; text-decoration: none; font-weight: bold; margin-right: 15px; }

/* BARRA DE BUSCA E FILTROS */
.barra-busca { display: flex; justify-content: center; flex-wrap: wrap; gap: 10px; margin-bottom: 30px; background: #111; padding: 15px; border-radius: 10px; }
.barra-busca input, .barra-busca select { padding: 12px; border-radius: 8px; border: 1px solid #444; background: #222; color: white; font-size: 15px; }
.barra-busca input { flex: 2; min-width: 200px; max-width: 400px; }
.barra-busca select { flex: 1; min-width: 150px; }
.barra-busca button { padding: 12px 25px; background: gold; color: black; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 15px; }
.barra-busca button:hover { background: white; }
.limpar-busca { color: #aaa; text-decoration: none; display: flex; align-items: center; padding: 0 10px; }
.limpar-busca:hover { color: gold; }

.hero-banner { position: relative; width: 100%; max-width: 1000px; height: 350px; margin: 0 auto 40px; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.5); border: 2px solid #333; }
.hero-slide { display: none; width: 100%; height: 100%; position: relative; }
.hero-slide.ativo { display: block; animation: fade 1s; }
@keyframes fade { from {opacity: 0.5} to {opacity: 1} }
.hero-slide img { width: 100%; height: 100%; object-fit: cover; }
.hero-overlay { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.95)); padding: 40px 30px 20px; display: flex; justify-content: space-between; align-items: flex-end; }
.hero-info h2 { color: white; font-size: 32px; margin: 0 0 5px; text-shadow: 2px 2px 4px #000; }
.hero-info p { color: gold; font-size: 22px; font-weight: bold; margin: 0; }
.hero-btn { background: gold; color: black; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; transition: 0.3s; }
.hero-btn:hover { background: white; }
.hero-nav { position: absolute; top: 50%; width: 100%; display: flex; justify-content: space-between; transform: translateY(-50%); padding: 0 15px; pointer-events: none; }
.hero-seta { background: rgba(0,0,0,0.7); color: gold; border: 2px solid gold; width: 45px; height: 45px; border-radius: 50%; font-size: 20px; cursor: pointer; pointer-events: auto; transition: 0.3s; display: flex; align-items: center; justify-content: center;}
.hero-seta:hover { background: gold; color: black; transform: scale(1.1); }
</style>
</head>
<body>

<header class="topo">
    <?php if (isset($_SESSION['ID_Usuario'])): ?>
        <a href="Usuario.php" class="perfil-topo">
            <img src="<?= htmlspecialchars($foto_perfil) ?>" alt="Avatar">
            <span><?= htmlspecialchars($nome_usuario) ?></span>
        </a>
    <?php endif; ?>
    <div class="logo">Legends_Games</div>
    <div class="auth">
        <?php 
        if (isset($_SESSION['ID_Usuario'])) {
            $stmtAd = $conexao->prepare("SELECT Nivel_Acesso FROM usuario WHERE ID_usuario = ?");
            $stmtAd->bind_param("i", $_SESSION['ID_Usuario']);
            $stmtAd->execute();
            $resAd = $stmtAd->get_result()->fetch_assoc();
            if ($resAd && $resAd['Nivel_Acesso'] == 1) echo '<a href="admin_jogos.php" class="btn admin-btn">⚙️ Admin</a>';
            $stmtAd->close();
        }
        ?>
        <a href="carrinho.php" class="carrinho">🛒 Carrinho (<?= $qtdCarrinho ?>)</a>
        <?php if (!isset($_SESSION['ID_Usuario'])): ?>
            <a href="login.php"><button class="login">Login</button></a>
            <a href="cadastro.php"><button class="cadastro">Cadastrar</button></a>
        <?php else: ?>
             <a href="logout.php"><button class="login">Sair</button></a>
        <?php endif; ?>
    </div>
</header>

<div class="container">
<aside class="menu">
    <ul>
        <li><a href="Usuario.php">👤 Minha Conta</a></li>
        <li><a href="biblioteca.php">📚 Biblioteca</a></li>
        <li><a href="favoritos.php">❤️ Favoritos</a></li>
        <li><a href="configuração.php">⚙️ Configurações</a></li>
    </ul>
</aside>

<main class="conteudo">

    <form class="barra-busca" method="GET" action="tela_inicial.php">
        <input type="text" name="busca" placeholder="Buscar jogo..." value="<?= htmlspecialchars($busca) ?>">
        
        <select name="categoria">
            <option value="">Todas as Categorias</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['ID_Categoria'] ?>" <?= ($filtro_categoria == $cat['ID_Categoria']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['Nome_Categoria']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <select name="ordem" onchange="this.form.submit()">
            <option value="" <?= $ordem === '' ? 'selected' : '' ?>>Mais Recentes</option>
            <option value="menor_preco" <?= $ordem === 'menor_preco' ? 'selected' : '' ?>>Menor Preço</option>
            <option value="maior_preco" <?= $ordem === 'maior_preco' ? 'selected' : '' ?>>Maior Preço</option>
            <option value="az" <?= $ordem === 'az' ? 'selected' : '' ?>>Ordem Alfabética (A-Z)</option>
            <option value="za" <?= $ordem === 'za' ? 'selected' : '' ?>>Ordem Alfabética (Z-A)</option>
        </select>

        <button type="submit">🔍 Buscar</button>
        <?php if ($busca !== '' || $filtro_categoria !== '' || $ordem !== ''): ?>
            <a href="tela_inicial.php" class="limpar-busca">✖ Limpar</a>
        <?php endif; ?>
    </form>

    <?php if (($busca === '' && $filtro_categoria === '' && $ordem === '') && count($jogos_destaque) > 0): ?>
    <div class="hero-banner">
        <?php foreach($jogos_destaque as $i => $jd): ?>
            <div class="hero-slide <?= $i === 0 ? 'ativo' : '' ?>">
                <img src="<?= htmlspecialchars($jd['Capa']) ?>" onerror="this.src='https://via.placeholder.com/1000x350'">
                <div class="hero-overlay">
                    <div class="hero-info">
                        <h2><?= htmlspecialchars($jd['Nome']) ?></h2>
                        <p>R$ <?= number_format($jd['Preco_Unitario'], 2, ',', '.') ?></p>
                    </div>
                    <a href="tela_de_jogo.php?nome=<?= urlencode($jd['Nome']) ?>" class="hero-btn">🎮 Comprar Agora</a>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if(count($jogos_destaque) > 1): ?>
        <div class="hero-nav">
            <button type="button" class="hero-seta" onclick="mudarSlide(-1)">❮</button>
            <button type="button" class="hero-seta" onclick="mudarSlide(1)">❯</button>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <h1 class="titulo">NOSSOS JOGOS</h1>

    <div class="games">
        <?php if (count($jogos_banco) > 0) {
            foreach ($jogos_banco as $g) { 
                $eh_favorito = in_array($g['ID_jogo'], $meus_favoritos);
                $emblema = "";
                if (!empty($g['Badge'])) {
                    if ($g['Badge'] === 'oferta') $emblema = '<div class="badge-faixa">💥 Oferta</div>';
                    elseif ($g['Badge'] === 'novo') $emblema = '<div class="badge-faixa badge-novo">✨ Novo</div>';
                    elseif ($g['Badge'] === 'hot') $emblema = '<div class="badge-faixa badge-hot">🔥 Top Vendas</div>';
                }
            ?>
            <div class="card">
                <?= $emblema ?>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="id_jogo" value="<?= $g['ID_jogo'] ?>">
                    <button type="submit" name="favoritar" class="btn-fav" title="Favoritos">
                        <?= $eh_favorito ? '❤️' : '🤍' ?>
                    </button>
                </form>
                <img src="<?= htmlspecialchars($g['Capa']) ?>" onerror="this.src='https://via.placeholder.com/300x150'">
                <div class="card-content">
                    <h3><?= htmlspecialchars($g['Nome']) ?></h3>
                    <p class="preco">R$ <?= number_format($g['Preco_Unitario'], 2, ',', '.') ?></p>
                    <a class="detalhes" href="tela_de_jogo.php?nome=<?= urlencode($g['Nome']) ?>">🎮 Ver jogo</a>
                    <form method="post">
                        <input type="hidden" name="nome" value="<?= htmlspecialchars($g['Nome']) ?>">
                        <input type="hidden" name="preco" value="<?= $g['Preco_Unitario'] ?>">
                        <input type="hidden" name="img" value="<?= htmlspecialchars($g['Capa']) ?>">
                        <button class="add" name="adicionar">🛒 Adicionar</button>
                    </form>
                </div>
            </div>
        <?php } } else { echo "<p style='color: white; text-align: center; width: 100%;'>Nenhum jogo encontrado para esta busca.</p>"; } ?>
    </div>
</main>
</div>

<script>
let slideAtual = 0;
const slides = document.querySelectorAll('.hero-slide');
let timerCarrossel;

function mostrarSlide(index) {
    if(slides.length <= 1) return;
    slides.forEach(s => s.classList.remove('ativo'));
    if (index >= slides.length) slideAtual = 0;
    else if (index < 0) slideAtual = slides.length - 1;
    else slideAtual = index;
    slides[slideAtual].classList.add('ativo');
}

function mudarSlide(direcao) {
    mostrarSlide(slideAtual + direcao);
    resetarTimer();
}

function resetarTimer() {
    clearInterval(timerCarrossel);
    timerCarrossel = setInterval(() => mudarSlide(1), 5000);
}

if(slides.length > 1) resetarTimer();
</script>

</body>
</html>
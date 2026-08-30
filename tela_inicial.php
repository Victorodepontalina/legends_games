<?php
session_start();
require_once 'conexao.php';

// ADICIONAR AO CARRINHO
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
        $_SESSION['carrinho'][] = ["nome" => $nome, "preco" => $preco, "img" => $img, "quantidade" => 1];
    }
    header("Location: tela_inicial.php");
    exit;
}

// BUSCAR CATEGORIAS PARA O FILTRO
$categorias = [];
$resCat = $conexao->query("SELECT * FROM categoria ORDER BY Nome_Categoria ASC");
if ($resCat && $resCat->num_rows > 0) {
    while ($row = $resCat->fetch_assoc()) {
        $categorias[] = $row;
    }
}

// CAPTURAR OS FILTROS DA URL
$busca = $_GET['busca'] ?? '';
$filtro_categoria = $_GET['categoria'] ?? '';

// MONTAR A CONSULTA DINÂMICA
$sqlJogos = "SELECT ID_jogo, Nome, Preco_Unitario, Capa FROM jogo WHERE 1=1";
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

$sqlJogos .= " ORDER BY ID_jogo DESC";

// PREPARAR E EXECUTAR
$stmt = $conexao->prepare($sqlJogos);
if (!empty($params)) {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();

$jogos_banco = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $jogos_banco[] = $row;
    }
}
$stmt->close();

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
* { box-sizing: border-box; }
body { margin: 0; font-family: Arial; background: radial-gradient(circle at top,#1b2838,#0f141a); color: #FFD700; }
.topo { display: flex; justify-content: center; align-items: center; position: relative; padding: 20px; background: #111; }
.logo { font-size: 42px; font-weight: bold; border: 3px solid gold; padding: 10px 30px; border-radius: 12px; }
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
.titulo { text-align: center; border: 2px solid gold; padding: 10px; border-radius: 10px; }
.games { display: flex; gap: 25px; flex-wrap: wrap; justify-content: center; }
.card { width: 240px; background: #222; border-radius: 15px; overflow: hidden; transition: .3s; }
.card:hover { transform: scale(1.04); box-shadow: 0 0 15px rgba(255, 215, 0, 0.4); }
.card img { width: 100%; height: 140px; object-fit: cover; }
.card-content { padding: 15px; }
.card h3 { color: white; margin-top: 0; font-size: 18px; height: 40px; overflow: hidden; }
.preco { color: gold; font-size: 20px; font-weight: bold; margin: 10px 0; }
.detalhes { display: block; background: #333; color: white; text-align: center; padding: 10px; text-decoration: none; border-radius: 8px; margin-top: 8px; transition: 0.3s; }
.detalhes:hover { background: #555; }
.add { width: 100%; padding: 10px; background: gold; color: black; border: 0; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 8px; transition: 0.3s; }
.add:hover { background: white; }
.carrinho { color: gold; text-decoration: none; font-weight: bold; margin-right: 15px; }

/* ESTILOS DA BARRA DE BUSCA */
.barra-busca { display: flex; justify-content: center; gap: 10px; margin-bottom: 30px; background: #111; padding: 15px; border-radius: 10px; }
.barra-busca input[type="text"] { flex: 1; max-width: 400px; padding: 12px; border-radius: 8px; border: 1px solid #444; background: #222; color: white; font-size: 16px; }
.barra-busca select { padding: 12px; border-radius: 8px; border: 1px solid #444; background: #222; color: white; font-size: 16px; }
.barra-busca button { padding: 12px 25px; background: gold; color: black; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 16px; }
.barra-busca button:hover { background: white; }
.limpar-busca { color: #aaa; text-decoration: none; display: flex; align-items: center; padding: 0 10px; }
.limpar-busca:hover { color: gold; }
</style>
</head>
<body>

<header class="topo">
    <div class="logo">Legends_Games</div>
    <div class="auth">
        <!-- Verifica se é Admin para mostrar o botão do painel -->
        <?php 
        if (isset($_SESSION['ID_Usuario'])) {
            $stmtAd = $conexao->prepare("SELECT Nivel_Acesso FROM usuario WHERE ID_usuario = ?");
            $stmtAd->bind_param("i", $_SESSION['ID_Usuario']);
            $stmtAd->execute();
            $resAd = $stmtAd->get_result()->fetch_assoc();
            if ($resAd && $resAd['Nivel_Acesso'] == 1) {
                echo '<a href="admin_jogos.php" class="btn admin-btn">⚙️ Admin</a>';
            }
            $stmtAd->close();
        }
        ?>

        <a href="carrinho.php" class="carrinho">🛒 Carrinho (<?= $qtdCarrinho ?>)</a>
        <?php if (!isset($_SESSION['ID_Usuario'])) { ?>
            <a href="login.php"><button class="login">Login</button></a>
            <a href="cadastro.php"><button class="cadastro">Cadastrar</button></a>
        <?php } else { ?>
             <a href="logout.php"><button class="login">Sair</button></a>
        <?php } ?>
    </div>
</header>

<div class="container">
<aside class="menu">
    <ul>
        <li><a href="Usuario.php">👤 Minha Conta</a></li>
        <li><a href="biblioteca.php">📚 Biblioteca</a></li>
        <li><a href="configuração.php">⚙️ Configurações</a></li>
    </ul>
</aside>

<main class="conteudo">
    <h1 class="titulo">NOSSOS JOGOS</h1>

    <!-- FORMULÁRIO DE BUSCA E FILTRO -->
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
        <button type="submit">🔍 Buscar</button>
        <?php if ($busca !== '' || $filtro_categoria !== ''): ?>
            <a href="tela_inicial.php" class="limpar-busca">✖ Limpar</a>
        <?php endif; ?>
    </form>

    <div class="games">
        <?php if (count($jogos_banco) > 0) {
            foreach ($jogos_banco as $g) { ?>
            <div class="card">
                <img src="<?= htmlspecialchars($g['Capa']) ?>" onerror="this.src='https://via.placeholder.com/300x150/111111/FFD700?text=Sem+Capa'">
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

</body>
</html>
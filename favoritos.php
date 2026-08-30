<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['ID_Usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = (int)$_SESSION['ID_Usuario'];

// REMOVER DOS FAVORITOS
if (isset($_POST['remover_favorito'])) {
    $id_jogo = (int)$_POST['id_jogo'];
    $stmtDel = $conexao->prepare("DELETE FROM favoritos WHERE ID_usuario = ? AND ID_jogo = ?");
    $stmtDel->bind_param("ii", $id_usuario, $id_jogo);
    $stmtDel->execute();
    header("Location: favoritos.php");
    exit;
}

// BUSCAR OS FAVORITOS DO USUÁRIO
$favoritos = [];
$sql = "SELECT f.ID_Favorito, j.ID_jogo, j.Nome, j.Preco_Unitario, j.Capa 
        FROM favoritos f 
        INNER JOIN jogo j ON f.ID_jogo = j.ID_jogo 
        WHERE f.ID_usuario = ? ORDER BY f.ID_Favorito DESC";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $favoritos[] = $row;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Meus Favoritos - Legends Games</title>
<style>
* { box-sizing: border-box; }
body { margin: 0; font-family: Arial; background: radial-gradient(circle at top,#1b2838,#0f141a); color: #FFD700; min-height: 100vh;}
header { padding: 20px; background: #0d0f13; border-bottom: 2px solid gold; display: flex; justify-content: space-between; align-items: center; }
.logo { color: gold; font-size: 28px; font-weight: bold; }
.voltar { background: #222; color: gold; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold; }
.voltar:hover { background: gold; color: black; }
.container { width: 90%; max-width: 1200px; margin: 40px auto; }
h1 { text-align: center; color: gold; margin-bottom: 30px; font-size: 36px;}
.grid-fav { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 25px; }
.card { background: #222; border-radius: 15px; overflow: hidden; position: relative; border: 1px solid #333;}
.card img { width: 100%; height: 140px; object-fit: cover; }
.card-content { padding: 15px; }
.card h3 { color: white; margin-top: 0; font-size: 18px; height: 40px; overflow: hidden; }
.preco { color: gold; font-size: 20px; font-weight: bold; margin: 10px 0; }
.botoes { display: flex; gap: 10px; margin-top: 10px; }
.btn-ver { flex: 1; background: #333; color: white; text-align: center; padding: 10px; text-decoration: none; border-radius: 8px; font-weight: bold;}
.btn-ver:hover { background: #555; }
.btn-remover { background: #8b1e24; color: white; padding: 10px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
.btn-remover:hover { background: red; }
.vazio { text-align: center; padding: 50px; background: #14181f; border-radius: 15px; border: 1px dashed #333; grid-column: 1 / -1; }
</style>
</head>
<body>

<header>
    <div class="logo">Legends_Games</div>
    <a href="tela_inicial.php" class="voltar">⬅ Voltar à Loja</a>
</header>

<div class="container">
    <h1>❤️ Lista de Desejos</h1>
    
    <div class="grid-fav">
        <?php if (count($favoritos) > 0): ?>
            <?php foreach ($favoritos as $f): ?>
                <div class="card">
                    <img src="<?= htmlspecialchars($f['Capa']) ?>" onerror="this.src='https://via.placeholder.com/240x140'">
                    <div class="card-content">
                        <h3><?= htmlspecialchars($f['Nome']) ?></h3>
                        <p class="preco">R$ <?= number_format($f['Preco_Unitario'], 2, ',', '.') ?></p>
                        
                        <div class="botoes">
                            <a href="tela_de_jogo.php?nome=<?= urlencode($f['Nome']) ?>" class="btn-ver">🎮 Ver Jogo</a>
                            <form method="post">
                                <input type="hidden" name="id_jogo" value="<?= $f['ID_jogo'] ?>">
                                <button type="submit" name="remover_favorito" class="btn-remover">✖</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="vazio">
                <h2 style="color: white; margin-top: 0;">Sua lista de desejos está vazia!</h2>
                <p style="color: #aaa;">Explore a loja e clique no coração para salvar seus jogos favoritos.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
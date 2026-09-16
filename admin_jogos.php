<?php
session_start();
require_once 'conexao.php';

// Verificação de segurança (Admin)
if (!isset($_SESSION['ID_Usuario'])) {
    header("Location: login.php");
    exit;
}
$id_usuario = (int)$_SESSION['ID_Usuario'];
$stmtAd = $conexao->prepare("SELECT Nivel_Acesso FROM usuario WHERE ID_usuario = ?");
$stmtAd->bind_param("i", $id_usuario);
$stmtAd->execute();
$resAd = $stmtAd->get_result()->fetch_assoc();
$stmtAd->close();

if (!$resAd || $resAd['Nivel_Acesso'] != 1) {
    die("<h2 style='color:white; text-align:center;'>Acesso Negado. Você não é um administrador.</h2>");
}

// --- LÓGICA DE CATEGORIAS ---
if (isset($_POST['adicionar_categoria'])) {
    $nome_cat = trim($_POST['nome_categoria']);
    if (!empty($nome_cat)) {
        $insCat = $conexao->prepare("INSERT INTO categoria (Nome_Categoria) VALUES (?)");
        $insCat->bind_param("s", $nome_cat);
        $insCat->execute();
    }
    header("Location: admin_jogos.php");
    exit;
}

if (isset($_POST['excluir_categoria'])) {
    $id_cat = (int)$_POST['id_categoria'];
    $conexao->query("DELETE FROM categoria WHERE ID_Categoria = $id_cat");
    header("Location: admin_jogos.php");
    exit;
}

// --- LÓGICA DE JOGOS ---

// ADICIONAR JOGO
if (isset($_POST['adicionar_jogo'])) {
    $nome = $_POST['nome'];
    $desc = $_POST['descricao'];
    $preco = (float)$_POST['preco'];
    $capa = $_POST['capa'];
    $video = $_POST['video'];
    $cat = (int)$_POST['categoria'];
    $badge = $_POST['badge'];

    $ins = $conexao->prepare("INSERT INTO jogo (Nome, Descricao, Preco_Unitario, Capa, Video_Demonstrativo, ID_Categoria, Badge) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $ins->bind_param("ssdssis", $nome, $desc, $preco, $capa, $video, $cat, $badge);
    $ins->execute();
    header("Location: admin_jogos.php");
    exit;
}

// EDITAR JOGO
if (isset($_POST['editar_jogo'])) {
    $id = (int)$_POST['id_jogo'];
    $nome = $_POST['nome'];
    $preco = (float)$_POST['preco'];
    $badge = $_POST['badge'];
    $capa = $_POST['capa'];
    $video = $_POST['video'];
    $cat = (int)$_POST['categoria_edit'];

    $up = $conexao->prepare("UPDATE jogo SET Nome=?, Preco_Unitario=?, Badge=?, Capa=?, Video_Demonstrativo=?, ID_Categoria=? WHERE ID_jogo=?");
    $up->bind_param("sdsssii", $nome, $preco, $badge, $capa, $video, $cat, $id);
    $up->execute();
    
    header("Location: admin_jogos.php");
    exit;
}

// EXCLUIR JOGO
if (isset($_POST['excluir_jogo'])) {
    $id = (int)$_POST['id_jogo'];
    $del = $conexao->prepare("DELETE FROM jogo WHERE ID_jogo=?");
    $del->bind_param("i", $id);
    $del->execute();
    header("Location: admin_jogos.php");
    exit;
}

// --- LÓGICA DE CUPONS ---
$conexao->query("CREATE TABLE IF NOT EXISTS cupons (
    ID_Cupom INT AUTO_INCREMENT PRIMARY KEY,
    Codigo VARCHAR(50) NOT NULL UNIQUE,
    Desconto DECIMAL(5,2) NOT NULL,
    Status ENUM('Ativo', 'Inativo') DEFAULT 'Ativo'
)");

if (isset($_POST['adicionar_cupom'])) {
    $codigo = strtoupper(trim($_POST['codigo_cupom']));
    $desconto = (float)$_POST['desconto_cupom'];
    $insC = $conexao->prepare("INSERT INTO cupons (Codigo, Desconto) VALUES (?, ?)");
    $insC->bind_param("sd", $codigo, $desconto);
    $insC->execute();
    header("Location: admin_jogos.php");
    exit;
}

if (isset($_POST['excluir_cupom'])) {
    $id_c = (int)$_POST['id_cupom'];
    $delC = $conexao->prepare("DELETE FROM cupons WHERE ID_Cupom=?");
    $delC->bind_param("i", $id_c);
    $delC->execute();
    header("Location: admin_jogos.php");
    exit;
}

// Buscar dados para preencher a tela
$jogos = [];
$resJ = $conexao->query("SELECT * FROM jogo ORDER BY ID_jogo DESC");
if ($resJ) while($r = $resJ->fetch_assoc()) $jogos[] = $r;

$categorias = [];
$resCat = $conexao->query("SELECT * FROM categoria");
if ($resCat) while($r = $resCat->fetch_assoc()) $categorias[] = $r;

$cupons = [];
$resCup = $conexao->query("SELECT * FROM cupons ORDER BY ID_Cupom DESC");
if ($resCup) while($r = $resCup->fetch_assoc()) $cupons[] = $r;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Painel Admin - Legends Games</title>
<style>
* { box-sizing: border-box; }
body { margin: 0; font-family: Arial; background: #0f141a; color: white; padding: 20px; }
header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid gold; padding-bottom: 20px; margin-bottom: 30px; }
.logo { color: gold; font-size: 28px; font-weight: bold; }
.voltar { background: #222; color: gold; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold; }
.voltar:hover { background: gold; color: black; }
.container { display: grid; grid-template-columns: 1fr; gap: 30px; max-width: 1200px; margin: auto; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
.box { background: #14181f; padding: 25px; border-radius: 15px; border: 1px solid #333; }
h2 { color: gold; margin-top: 0; border-bottom: 1px solid #333; padding-bottom: 10px; }
table { width: 100%; border-collapse: collapse; margin-top: 15px; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid #333; vertical-align: middle; }
th { color: gold; background: #1a1e24; }
input, select, textarea { width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px; border: 1px solid #444; background: #222; color: white; }
.btn { padding: 8px 15px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; }
.btn-salvar { background: gold; color: black; }
.btn-salvar:hover { background: white; }
.btn-excluir { background: #ff4d4d; color: white; }
.btn-excluir:hover { background: red; }
.img-capa { width: 60px; height: 80px; object-fit: cover; border-radius: 5px; }
.flex-form { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.full-width { grid-column: span 2; }
@media (max-width: 900px) { .grid-2 { grid-template-columns: 1fr; } }
</style>
</head>
<body>

<header>
    <div class="logo">Legends_Games - Painel Admin</div>
    <a href="tela_inicial.php" class="voltar">⬅ Voltar à Loja</a>
</header>

<div class="container">
    
    <div class="grid-2">
        <!-- SESSÃO DE CATEGORIAS -->
        <div class="box">
            <h2>📁 Categorias</h2>
            <form method="POST" style="display: flex; gap: 10px; align-items: flex-end; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label>Nova Categoria</label>
                    <input type="text" name="nome_categoria" required placeholder="Ex: Corrida, RPG...">
                </div>
                <button type="submit" name="adicionar_categoria" class="btn btn-salvar" style="padding: 10px;">➕ Adicionar</button>
            </form>
            <div style="max-height: 250px; overflow-y: auto;">
                <table>
                    <tr>
                        <th>Nome</th>
                        <th width="50">Ação</th>
                    </tr>
                    <?php foreach($categorias as $cat): ?>
                    <tr>
                        <td style="font-weight: bold;"><?= htmlspecialchars($cat['Nome_Categoria']) ?></td>
                        <td>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="id_categoria" value="<?= $cat['ID_Categoria'] ?>">
                                <button type="submit" name="excluir_categoria" class="btn btn-excluir" onclick="return confirm('ATENÇÃO: Excluir uma categoria apagará os jogos nela se não tiverem backup de ID. Deseja continuar?')">X</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <!-- SESSÃO DE CUPONS -->
        <div class="box">
            <h2>🎟️ Cupons de Desconto</h2>
            <form method="POST" style="display: flex; gap: 10px; align-items: flex-end; margin-bottom: 15px;">
                <div style="flex: 2;">
                    <label>Código (Ex: NINJA20)</label>
                    <input type="text" name="codigo_cupom" required placeholder="Código">
                </div>
                <div style="flex: 1;">
                    <label>Desc. (%)</label>
                    <input type="number" step="0.1" name="desconto_cupom" required placeholder="%">
                </div>
                <button type="submit" name="adicionar_cupom" class="btn btn-salvar" style="padding: 10px;">➕ Criar</button>
            </form>
            <div style="max-height: 250px; overflow-y: auto;">
                <table>
                    <tr>
                        <th>Código</th>
                        <th>Desc.</th>
                        <th width="50">Ação</th>
                    </tr>
                    <?php foreach($cupons as $c): ?>
                    <tr>
                        <td style="font-weight: bold; color: #00ff88;"><?= htmlspecialchars($c['Codigo']) ?></td>
                        <td><?= $c['Desconto'] ?>%</td>
                        <td>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="id_cupom" value="<?= $c['ID_Cupom'] ?>">
                                <button type="submit" name="excluir_cupom" class="btn btn-excluir">X</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- SESSÃO DE ADICIONAR JOGO -->
    <div class="box">
        <h2>🎮 Adicionar Novo Jogo</h2>
        <form method="POST" class="flex-form">
            <div>
                <label>Nome do Jogo</label>
                <input type="text" name="nome" required>
            </div>
            <div>
                <label>Preço (R$)</label>
                <input type="number" step="0.01" name="preco" required>
            </div>
            <div>
                <label>Link da Capa (Imagem)</label>
                <input type="text" name="capa" required>
            </div>
            <div>
                <label>Link do Vídeo (YouTube Embed)</label>
                <input type="text" name="video" placeholder="Ex: https://www.youtube.com/embed/...">
            </div>
            <div>
                <label>Categoria</label>
                <select name="categoria" required>
                    <?php foreach($categorias as $cat): ?>
                        <option value="<?= $cat['ID_Categoria'] ?>"><?= htmlspecialchars($cat['Nome_Categoria']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Emblema (Badge)</label>
                <select name="badge">
                    <option value="">Nenhum</option>
                    <option value="novo">✨ Novo</option>
                    <option value="oferta">💥 Oferta</option>
                    <option value="hot">🔥 Top Vendas</option>
                </select>
            </div>
            <div class="full-width">
                <label>Descrição</label>
                <textarea name="descricao" rows="3" required></textarea>
            </div>
            <div class="full-width">
                <button type="submit" name="adicionar_jogo" class="btn btn-salvar" style="width: 100%; padding: 15px; font-size: 16px;">➕ Adicionar ao Catálogo</button>
            </div>
        </form>
    </div>

    <!-- SESSÃO DE LISTAR E EDITAR JOGOS -->
    <div class="box">
        <h2>🕹️ Catálogo de Jogos (<?= count($jogos) ?>)</h2>
        <div style="overflow-x: auto;">
            <table>
                <tr>
                    <th width="80">Capa</th>
                    <th>Dados do Jogo (Nome, Preço, Capa, Vídeo)</th>
                    <th width="200">Filtros (Categoria e Emblema)</th>
                    <th width="160">Ações</th>
                </tr>
                <?php foreach($jogos as $j): ?>
                <tr>
                    <td>
                        <img src="<?= htmlspecialchars($j['Capa']) ?>" class="img-capa" onerror="this.src='https://via.placeholder.com/60x80'">
                    </td>
                    <td>
                        <form method="POST" style="display:flex; flex-direction:column; gap:5px;">
                            <input type="hidden" name="id_jogo" value="<?= $j['ID_jogo'] ?>">
                            
                            <input type="text" name="nome" value="<?= htmlspecialchars($j['Nome']) ?>" title="Nome do Jogo" required>
                            
                            <div style="display:flex; align-items:center; gap:5px;">
                                <span>R$</span>
                                <input type="number" step="0.01" name="preco" value="<?= $j['Preco_Unitario'] ?>" style="width: 100px; margin-top:0;" title="Preço" required>
                            </div>

                            <input type="text" name="capa" value="<?= htmlspecialchars($j['Capa']) ?>" placeholder="Link da Capa" title="Imagem da Capa">
                            
                            <input type="text" name="video" value="<?= htmlspecialchars($j['Video_Demonstrativo']) ?>" placeholder="Link do Vídeo (Embed)" title="Vídeo de Demonstração">
                    </td>
                    <td>
                            <select name="categoria_edit" title="Categoria" style="margin-bottom: 5px;">
                                <?php foreach($categorias as $cat): ?>
                                    <option value="<?= $cat['ID_Categoria'] ?>" <?= ($j['ID_Categoria'] == $cat['ID_Categoria']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['Nome_Categoria']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <select name="badge" title="Emblema">
                                <option value="" <?= $j['Badge'] == '' ? 'selected' : '' ?>>Sem Emblema</option>
                                <option value="novo" <?= $j['Badge'] == 'novo' ? 'selected' : '' ?>>✨ Novo</option>
                                <option value="oferta" <?= $j['Badge'] == 'oferta' ? 'selected' : '' ?>>💥 Oferta</option>
                                <option value="hot" <?= $j['Badge'] == 'hot' ? 'selected' : '' ?>>🔥 Top Vendas</option>
                            </select>
                    </td>
                    <td>
                            <button type="submit" name="editar_jogo" class="btn btn-salvar" style="margin-bottom: 5px;">Salvar Edição</button>
                        </form>
                        
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id_jogo" value="<?= $j['ID_jogo'] ?>">
                            <button type="submit" name="excluir_jogo" class="btn btn-excluir" onclick="return confirm('Tem certeza que deseja excluir <?= addslashes($j['Nome']) ?>?')">Excluir Jogo</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

</div>

</body>
</html>
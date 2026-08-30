<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['ID_Usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = (int)$_SESSION['ID_Usuario'];
$stmtAdmin = $conexao->prepare("SELECT Nivel_Acesso FROM usuario WHERE ID_usuario = ?");
$stmtAdmin->bind_param("i", $id_usuario);
$stmtAdmin->execute();
$resAdmin = $stmtAdmin->get_result()->fetch_assoc();

if (!$resAdmin || $resAdmin['Nivel_Acesso'] != 1) {
    header("Location: tela_inicial.php");
    exit;
}
$stmtAdmin->close();

// Criar tabela de cupons automaticamente
$conexao->query("CREATE TABLE IF NOT EXISTS cupom (
    ID_Cupom INT AUTO_INCREMENT PRIMARY KEY,
    Codigo VARCHAR(50) UNIQUE NOT NULL,
    Desconto INT NOT NULL,
    Ativo INT DEFAULT 1
)");

$mensagem = "";

/* =========================================================
   AÇÕES DO CRUD (JOGOS E CUPONS)
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // JOGOS
    if ($acao === 'adicionar') {
        $nome = trim($_POST['nome']);
        $descricao = trim($_POST['descricao']);
        $preco = (float)$_POST['preco'];
        $capa = trim($_POST['capa']);
        $video = trim($_POST['video']);
        $categoria = (int)$_POST['categoria'];

        $sql = "INSERT INTO jogo (Nome, Descricao, Preco_Unitario, Capa, Video_Demonstrativo, ID_Categoria) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssdssi", $nome, $descricao, $preco, $capa, $video, $categoria);
            if ($stmt->execute()) $mensagem = "<div class='sucesso'>Jogo adicionado!</div>";
            else $mensagem = "<div class='erro'>Erro: " . $stmt->error . "</div>";
            $stmt->close();
        }
    } elseif ($acao === 'excluir') {
        $id = (int)$_POST['id_jogo'];
        $stmt = $conexao->prepare("DELETE FROM jogo WHERE ID_jogo = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $mensagem = "<div class='sucesso'>Jogo excluído!</div>";
        }
    } elseif ($acao === 'editar') {
        $id = (int)$_POST['id_jogo'];
        $nome = trim($_POST['nome']);
        $preco = (float)$_POST['preco'];
        $stmt = $conexao->prepare("UPDATE jogo SET Nome = ?, Preco_Unitario = ? WHERE ID_jogo = ?");
        if ($stmt) {
            $stmt->bind_param("sdi", $nome, $preco, $id);
            $stmt->execute();
            $stmt->close();
            $mensagem = "<div class='sucesso'>Jogo atualizado!</div>";
        }
    }
    
    // CUPONS
    elseif ($acao === 'adicionar_cupom') {
        $codigo = strtoupper(trim($_POST['codigo_cupom']));
        $desconto = (int)$_POST['desconto'];
        $stmt = $conexao->prepare("INSERT INTO cupom (Codigo, Desconto) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("si", $codigo, $desconto);
            if ($stmt->execute()) $mensagem = "<div class='sucesso'>Cupom criado!</div>";
            else $mensagem = "<div class='erro'>Erro ou cupom já existe.</div>";
            $stmt->close();
        }
    } elseif ($acao === 'excluir_cupom') {
        $id = (int)$_POST['id_cupom'];
        $stmt = $conexao->prepare("DELETE FROM cupom WHERE ID_Cupom = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $mensagem = "<div class='sucesso'>Cupom removido!</div>";
        }
    }
}

// BUSCAR DADOS
$jogos = [];
$resJogos = $conexao->query("SELECT j.*, c.Nome_Categoria FROM jogo j LEFT JOIN categoria c ON j.ID_Categoria = c.ID_Categoria ORDER BY j.ID_jogo DESC");
if ($resJogos) while($row = $resJogos->fetch_assoc()) $jogos[] = $row;

$categorias = [];
$resCat = $conexao->query("SELECT * FROM categoria");
if ($resCat) while($row = $resCat->fetch_assoc()) $categorias[] = $row;

$cupons = [];
$resCupons = $conexao->query("SELECT * FROM cupom ORDER BY ID_Cupom DESC");
if ($resCupons) while($row = $resCupons->fetch_assoc()) $cupons[] = $row;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Admin - Legends Games</title>
<style>
* { box-sizing: border-box; }
body { margin: 0; font-family: Arial; background: radial-gradient(circle at top, #1b2838, #05070a); color: white; padding: 20px;}
.header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid gold; padding-bottom: 20px; margin-bottom: 30px;}
.header h1 { color: gold; margin: 0; }
.voltar { background: #222; color: gold; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold; }
.voltar:hover { background: gold; color: black; }
.container { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; align-items: start;}
.box { background: #14181f; padding: 25px; border-radius: 15px; border: 1px solid #333; margin-bottom: 30px;}
.box h2 { color: gold; margin-top: 0; }
input, select, textarea { width: 100%; padding: 12px; margin-top: 8px; margin-bottom: 15px; border-radius: 8px; border: 1px solid #444; background: #252a32; color: white;}
input:focus, select:focus, textarea:focus { outline: none; border-color: gold; }
button { width: 100%; padding: 14px; background: gold; color: black; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 16px;}
button:hover { background: white; }
.tabela-dados { width: 100%; border-collapse: collapse; margin-top: 15px; }
.tabela-dados th, .tabela-dados td { padding: 12px; text-align: left; border-bottom: 1px solid #333; }
.tabela-dados th { color: gold; }
.tabela-dados img { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; }
.btn-pequeno { padding: 8px 12px; font-size: 13px; width: auto; margin: 2px;}
.btn-excluir { background: #ff4d4d; color: white; }
.btn-excluir:hover { background: #cc0000; }
.sucesso { background: #10251b; color: #00ff88; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; border: 1px solid #00ff88;}
.erro { background: #8b1e24; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
</style>
</head>
<body>

<div class="header">
    <h1>⚙️ Painel de Administração</h1>
    <a href="tela_inicial.php" class="voltar">⬅ Voltar à Loja</a>
</div>

<?= $mensagem ?>

<div class="container">
    <div>
        <!-- CADASTRAR JOGO -->
        <div class="box">
            <h2>➕ Adicionar Novo Jogo</h2>
            <form method="POST">
                <input type="hidden" name="acao" value="adicionar">
                <label>Nome do Jogo</label>
                <input type="text" name="nome" required>
                <label>Preço (R$)</label>
                <input type="number" name="preco" step="0.01" required>
                <label>Categoria</label>
                <select name="categoria" required>
                    <option value="">Selecione uma categoria...</option>
                    <?php foreach($categorias as $cat): ?>
                        <option value="<?= $cat['ID_Categoria'] ?>"><?= htmlspecialchars($cat['Nome_Categoria']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Caminho da Capa</label>
                <input type="text" name="capa" placeholder="imagens/nome.jpg" required>
                <label>Link do Trailer</label>
                <input type="text" name="video" placeholder="YouTube Embed link" required>
                <label>Descrição</label>
                <textarea name="descricao" rows="4" required></textarea>
                <button type="submit">Cadastrar Jogo</button>
            </form>
        </div>

        <!-- GERENCIAR CUPONS -->
        <div class="box">
            <h2>🏷️ Gerenciar Cupons</h2>
            <form method="POST" style="display: flex; gap: 10px; margin-bottom: 20px;">
                <input type="hidden" name="acao" value="adicionar_cupom">
                <input type="text" name="codigo_cupom" placeholder="Código (Ex: NINJA20)" style="margin:0;" required>
                <input type="number" name="desconto" placeholder="% Desconto" style="margin:0; width: 120px;" required min="1" max="100">
                <button type="submit" style="margin:0; width: auto; padding: 0 20px;">Criar</button>
            </form>

            <table class="tabela-dados">
                <thead><tr><th>Código</th><th>Desconto</th><th>Ação</th></tr></thead>
                <tbody>
                    <?php foreach($cupons as $c): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($c['Codigo']) ?></strong></td>
                            <td><?= $c['Desconto'] ?>% OFF</td>
                            <td>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="acao" value="excluir_cupom">
                                    <input type="hidden" name="id_cupom" value="<?= $c['ID_Cupom'] ?>">
                                    <button type="submit" class="btn-pequeno btn-excluir">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- LISTA DE JOGOS -->
    <div class="box" style="overflow-x: auto;">
        <h2>🎮 Jogos Cadastrados</h2>
        <table class="tabela-dados">
            <thead>
                <tr><th>Capa</th><th>Nome</th><th>Preço</th><th>Categoria</th><th>Ações</th></tr>
            </thead>
            <tbody>
                <?php foreach($jogos as $j): ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($j['Capa']) ?>" onerror="this.src='https://via.placeholder.com/50'"></td>
                        <td>
                            <form method="POST" style="display:flex; gap:5px; align-items:center;">
                                <input type="hidden" name="acao" value="editar">
                                <input type="hidden" name="id_jogo" value="<?= $j['ID_jogo'] ?>">
                                <input type="text" name="nome" value="<?= htmlspecialchars($j['Nome']) ?>" style="margin:0; padding:6px; width: 150px;">
                        </td>
                        <td>
                                R$ <input type="number" name="preco" step="0.01" value="<?= $j['Preco_Unitario'] ?>" style="margin:0; padding:6px; width: 80px;">
                        </td>
                        <td><?= htmlspecialchars($j['Nome_Categoria'] ?? 'Sem Categoria') ?></td>
                        <td>
                                <button type="submit" class="btn-pequeno">Salvar</button>
                            </form>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Excluir <?= htmlspecialchars($j['Nome']) ?>?');">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id_jogo" value="<?= $j['ID_jogo'] ?>">
                                <button type="submit" class="btn-pequeno btn-excluir">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
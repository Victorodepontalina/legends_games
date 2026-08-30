<?php
session_start();
require_once 'conexao.php';

// Proteção Avançada: Verifica se está logado e se é Admin (Nível 1)
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
    // Se não for admin, é expulso para a tela inicial
    header("Location: tela_inicial.php");
    exit;
}
$stmtAdmin->close();

$mensagem = "";

/* =========================================================
   AÇÕES DO CRUD (CRIAR, ATUALIZAR E DELETAR)
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // 1. ADICIONAR JOGO
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
            if ($stmt->execute()) {
                $mensagem = "<div class='sucesso'>Jogo adicionado com sucesso!</div>";
            } else {
                $mensagem = "<div class='erro'>Erro ao adicionar: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    } 
    // 2. EXCLUIR JOGO
    elseif ($acao === 'excluir') {
        $id = (int)$_POST['id_jogo'];
        $stmt = $conexao->prepare("DELETE FROM jogo WHERE ID_jogo = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $mensagem = "<div class='sucesso'>Jogo excluído com sucesso!</div>";
            }
            $stmt->close();
        }
    }
    // 3. EDITAR PREÇO/NOME
    elseif ($acao === 'editar') {
        $id = (int)$_POST['id_jogo'];
        $nome = trim($_POST['nome']);
        $preco = (float)$_POST['preco'];
        
        $stmt = $conexao->prepare("UPDATE jogo SET Nome = ?, Preco_Unitario = ? WHERE ID_jogo = ?");
        if ($stmt) {
            $stmt->bind_param("sdi", $nome, $preco, $id);
            if ($stmt->execute()) {
                $mensagem = "<div class='sucesso'>Jogo atualizado com sucesso!</div>";
            }
            $stmt->close();
        }
    }
}

/* =========================================================
   BUSCAR DADOS PARA EXIBIR NA TELA
========================================================= */
$jogos = [];
$resJogos = $conexao->query("SELECT j.*, c.Nome_Categoria FROM jogo j LEFT JOIN categoria c ON j.ID_Categoria = c.ID_Categoria ORDER BY j.ID_jogo DESC");
if ($resJogos && $resJogos->num_rows > 0) {
    while($row = $resJogos->fetch_assoc()){
        $jogos[] = $row;
    }
}

$categorias = [];
$resCat = $conexao->query("SELECT * FROM categoria");
if ($resCat && $resCat->num_rows > 0) {
    while($row = $resCat->fetch_assoc()){
        $categorias[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Painel Admin - Legends Games</title>
<style>
* { box-sizing: border-box; }
body { margin: 0; font-family: Arial, sans-serif; background: radial-gradient(circle at top, #1b2838, #05070a); color: white; padding: 20px;}
.header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid gold; padding-bottom: 20px; margin-bottom: 30px;}
.header h1 { color: gold; margin: 0; }
.voltar { background: #222; color: gold; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold; }
.voltar:hover { background: gold; color: black; }
.container { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
.box { background: #14181f; padding: 25px; border-radius: 15px; border: 1px solid #333; }
.box h2 { color: gold; margin-top: 0; }
input, select, textarea { width: 100%; padding: 12px; margin-top: 8px; margin-bottom: 15px; border-radius: 8px; border: 1px solid #444; background: #252a32; color: white; font-family: Arial;}
input:focus, select:focus, textarea:focus { outline: none; border-color: gold; }
button { width: 100%; padding: 14px; background: gold; color: black; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 16px;}
button:hover { background: white; }
.tabela-jogos { width: 100%; border-collapse: collapse; margin-top: 15px; }
.tabela-jogos th, .tabela-jogos td { padding: 12px; text-align: left; border-bottom: 1px solid #333; }
.tabela-jogos th { color: gold; }
.tabela-jogos img { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; }
.btn-pequeno { padding: 8px 12px; font-size: 13px; width: auto; margin: 2px;}
.btn-excluir { background: #ff4d4d; color: white; }
.btn-excluir:hover { background: #cc0000; }
.sucesso { background: #10251b; color: #00ff88; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; border: 1px solid #00ff88;}
.erro { background: #8b1e24; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
.form-linha { display: flex; gap: 10px; }
</style>
</head>
<body>

<div class="header">
    <h1>⚙️ Painel de Administração</h1>
    <a href="tela_inicial.php" class="voltar">⬅ Voltar à Loja</a>
</div>

<?= $mensagem ?>

<div class="container">
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
            
            <label>Caminho da Capa (Ex: imagens/jogo.jpg)</label>
            <input type="text" name="capa" placeholder="imagens/nome_do_arquivo.jpg" required>
            
            <label>Link do Trailer (YouTube Embed)</label>
            <input type="text" name="video" placeholder="https://www.youtube.com/embed/..." required>
            
            <label>Descrição</label>
            <textarea name="descricao" rows="4" required></textarea>
            
            <button type="submit">Cadastrar Jogo</button>
        </form>
    </div>

    <div class="box" style="overflow-x: auto;">
        <h2>🎮 Jogos Cadastrados</h2>
        <table class="tabela-jogos">
            <thead>
                <tr>
                    <th>Capa</th>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Categoria</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($jogos) > 0): ?>
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
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir <?= htmlspecialchars($j['Nome']) ?>?');">
                                    <input type="hidden" name="acao" value="excluir">
                                    <input type="hidden" name="id_jogo" value="<?= $j['ID_jogo'] ?>">
                                    <button type="submit" class="btn-pequeno btn-excluir">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">Nenhum jogo encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
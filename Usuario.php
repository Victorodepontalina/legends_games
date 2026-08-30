<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['ID_Usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = (int)$_SESSION['ID_Usuario'];
$mensagem = "";

// Auto-configuração do Banco de Dados
$conexao->query("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS Nome_Exibicao VARCHAR(100) DEFAULT NULL");
$conexao->query("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS Foto_Perfil VARCHAR(255) DEFAULT NULL");
$conexao->query("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS Data_Cadastro DATETIME DEFAULT CURRENT_TIMESTAMP");

$diretorio_uploads = 'uploads/';
if (!is_dir($diretorio_uploads)) mkdir($diretorio_uploads, 0777, true);

// ATUALIZAR PERFIL E AVATAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_perfil'])) {
    $nome = trim($_POST['nome']);
    $nome_exibicao = trim($_POST['nome_exibicao']);
    $email = trim($_POST['email']);
    $caminho_foto = null;

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($extensao, $extensoes_permitidas)) {
            $novo_nome = "avatar_" . $id_usuario . "_" . time() . "." . $extensao;
            $destino = $diretorio_uploads . $novo_nome;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destino)) $caminho_foto = $destino;
        } else {
            $mensagem = "<div class='erro'>Formato inválido. Use JPG, PNG ou GIF.</div>";
        }
    }

    if ($caminho_foto) {
        $stmt = $conexao->prepare("UPDATE usuario SET Nome = ?, Nome_Exibicao = ?, Email = ?, Foto_Perfil = ? WHERE ID_usuario = ?");
        $stmt->bind_param("ssssi", $nome, $nome_exibicao, $email, $caminho_foto, $id_usuario);
    } else {
        $stmt = $conexao->prepare("UPDATE usuario SET Nome = ?, Nome_Exibicao = ?, Email = ? WHERE ID_usuario = ?");
        $stmt->bind_param("sssi", $nome, $nome_exibicao, $email, $id_usuario);
    }

    if (isset($stmt) && $stmt->execute()) $mensagem = "<div class='sucesso'>Perfil atualizado com sucesso!</div>";
    elseif (!isset($mensagem) || $mensagem === '') $mensagem = "<div class='erro'>Erro ao atualizar. Tente novamente.</div>";
    if (isset($stmt)) $stmt->close();
}

// ALTERAR SENHA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_senha'])) {
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirma_senha = $_POST['confirma_senha'];

    if ($nova_senha !== $confirma_senha) {
        $mensagem = "<div class='erro'>As novas senhas não coincidem!</div>";
    } else {
        $stmt = $conexao->prepare("SELECT Senha FROM usuario WHERE ID_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (password_verify($senha_atual, $res['Senha'])) {
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmtUp = $conexao->prepare("UPDATE usuario SET Senha = ? WHERE ID_usuario = ?");
            $stmtUp->bind_param("si", $senha_hash, $id_usuario);
            if ($stmtUp->execute()) $mensagem = "<div class='sucesso'>Senha alterada com segurança!</div>";
            $stmtUp->close();
        } else {
            $mensagem = "<div class='erro'>A senha atual está incorreta!</div>";
        }
    }
}

// BUSCAR DADOS DO USUÁRIO
$stmtDados = $conexao->prepare("SELECT Nome, Nome_Exibicao, Email, Nivel_Acesso, Foto_Perfil, Data_Cadastro FROM usuario WHERE ID_usuario = ?");
$stmtDados->bind_param("i", $id_usuario);
$stmtDados->execute();
$usuario = $stmtDados->get_result()->fetch_assoc();
$stmtDados->close();

$foto_atual = !empty($usuario['Foto_Perfil']) ? $usuario['Foto_Perfil'] : 'https://via.placeholder.com/150/222222/FFD700?text=Foto';

// ESTATÍSTICAS DE GAMIFICAÇÃO (Biblioteca)
$total_jogos = 0;
$horas_jogadas = 0;
$stmtBib = $conexao->prepare("SELECT COUNT(ID_jogo) as TotalJogos, SUM(Horas_Jogadas) as TotalHoras FROM biblioteca WHERE ID_usuario = ?");
if ($stmtBib) {
    $stmtBib->bind_param("i", $id_usuario);
    $stmtBib->execute();
    $resBib = $stmtBib->get_result()->fetch_assoc();
    $total_jogos = (int)$resBib['TotalJogos'];
    $horas_jogadas = (int)$resBib['TotalHoras'];
    $stmtBib->close();
}

// LÓGICA DE PATENTES (RANKS)
$patente_nome = "Novato";
$patente_cor = "#aaa";
$patente_icone = "🔰";

// 👑 REGRA VIP: Se for Admin, ganha a Patente Suprema
if (isset($usuario['Nivel_Acesso']) && $usuario['Nivel_Acesso'] == 1) {
    $patente_nome = "Patente Suprema";
    $patente_cor = "#ff2a2a"; // Vermelho neon imponente
    $patente_icone = "⚡";
} 
// Restante das regras para usuários normais
elseif ($total_jogos >= 10) {
    $patente_nome = "Lenda Dourada";
    $patente_cor = "gold";
    $patente_icone = "👑";
} elseif ($total_jogos >= 5) {
    $patente_nome = "Colecionador Elite";
    $patente_cor = "#00ff88";
    $patente_icone = "💎";
} elseif ($total_jogos >= 1) {
    $patente_nome = "Explorador";
    $patente_cor = "#ff9900";
    $patente_icone = "⚔️";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Meu Perfil - Legends Games</title>
<style>
* { box-sizing: border-box; }
body { margin: 0; font-family: Arial, sans-serif; background: radial-gradient(circle at top, #1b2838, #05070a); color: white; padding: 20px;}
header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid gold; padding-bottom: 20px; margin-bottom: 30px;}
.logo { color: gold; font-size: 28px; font-weight: bold; }
.voltar { background: #222; color: gold; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold; }
.voltar:hover { background: gold; color: black; }
.container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; max-width: 1000px; margin: auto; }
.box { background: #14181f; padding: 30px; border-radius: 15px; border: 1px solid #333; }
.box h2 { color: gold; margin-top: 0; border-bottom: 1px solid #333; padding-bottom: 10px; }
label { display: block; margin-top: 15px; font-weight: bold; color: #ccc; }
input { width: 100%; padding: 12px; margin-top: 8px; border-radius: 8px; border: 1px solid #444; background: #252a32; color: white; font-size: 15px;}
input[type="file"] { padding: 8px; background: #111; cursor: pointer; }
input:focus { outline: none; border-color: gold; }
button { width: 100%; padding: 14px; margin-top: 25px; background: gold; color: black; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 16px;}
button:hover { background: white; }
.sucesso { background: #10251b; color: #00ff88; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; border: 1px solid #00ff88; text-align: center; max-width: 1000px; margin: 0 auto 20px;}
.erro { background: #8b1e24; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; text-align: center; max-width: 1000px; margin: 0 auto 20px;}
.tag-admin { display: inline-block; background: red; color: white; padding: 5px 10px; border-radius: 5px; font-size: 12px; margin-left: 10px; vertical-align: middle; }

/* Estilos do Avatar e Gamificação */
.perfil-topo-gamificado { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; background: #111; padding: 20px; border-radius: 15px; border: 1px solid #333;}
.avatar-preview { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid <?= $patente_cor ?>; background: #222; }
.info-gamer h1 { margin: 0 0 5px; color: white; }
.info-gamer p { margin: 0; color: #aaa; font-size: 14px; }
.rank-badge { display: inline-block; background: #222; border: 1px solid <?= $patente_cor ?>; color: <?= $patente_cor ?>; padding: 5px 10px; border-radius: 20px; font-weight: bold; font-size: 14px; margin-top: 8px;}
.stats-grid { display: flex; gap: 15px; margin-top: 15px; }
.stat-box { background: #1a1e24; padding: 15px; border-radius: 10px; text-align: center; flex: 1; border: 1px solid #333;}
.stat-box strong { display: block; font-size: 24px; color: gold; }
.stat-box span { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 1px;}

@media (max-width: 768px) { .container { grid-template-columns: 1fr; } .perfil-topo-gamificado { flex-direction: column; text-align: center; } }
</style>
</head>
<body>

<header>
    <div class="logo">Legends_Games</div>
    <a href="tela_inicial.php" class="voltar">⬅ Voltar à Loja</a>
</header>

<?= $mensagem ?>

<!-- PAINEL GAMIFICADO -->
<div class="perfil-topo-gamificado" style="max-width: 1000px; margin: 0 auto 30px;">
    <img src="<?= htmlspecialchars($foto_atual) ?>" class="avatar-preview" alt="Foto de Perfil">
    <div class="info-gamer">
        <h1><?= htmlspecialchars($usuario['Nome_Exibicao'] ?? $usuario['Nome']) ?> <?php if($usuario['Nivel_Acesso'] == 1) echo '<span class="tag-admin">Admin</span>'; ?></h1>
        <p>Membro desde: <?= isset($usuario['Data_Cadastro']) ? date('d/m/Y', strtotime($usuario['Data_Cadastro'])) : 'Sempre' ?></p>
        <div class="rank-badge"><?= $patente_icone ?> Patente: <?= $patente_nome ?></div>
        
        <div class="stats-grid">
            <div class="stat-box">
                <strong><?= $total_jogos ?></strong>
                <span>Jogos na Biblioteca</span>
            </div>
            <div class="stat-box">
                <strong><?= $horas_jogadas ?>h</strong>
                <span>Horas Jogadas</span>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="box">
        <h2>👤 Editar Dados</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Trocar Avatar (JPG, PNG)</label>
            <input type="file" name="avatar" accept="image/png, image/jpeg, image/jpg, image/gif">

            <label>Nome Completo</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($usuario['Nome'] ?? '') ?>" required>

            <label>Nome de Exibição (Gamer Tag)</label>
            <input type="text" name="nome_exibicao" placeholder="Como os outros te verão" value="<?= htmlspecialchars($usuario['Nome_Exibicao'] ?? '') ?>">

            <label>E-mail</label>
            <input type="email" name="email" value="<?= htmlspecialchars($usuario['Email'] ?? '') ?>" required>

            <button type="submit" name="atualizar_perfil">💾 Salvar Alterações</button>
        </form>
    </div>

    <div class="box">
        <h2>🔒 Segurança e Senha</h2>
        <form method="POST">
            <label>Senha Atual</label>
            <input type="password" name="senha_atual" placeholder="Digite sua senha atual" required>

            <label>Nova Senha</label>
            <input type="password" name="nova_senha" placeholder="Digite a nova senha" required>

            <label>Confirmar Nova Senha</label>
            <input type="password" name="confirma_senha" placeholder="Repita a nova senha" required>

            <button type="submit" name="atualizar_senha">🔑 Atualizar Senha</button>
        </form>
    </div>
</div>

</body>
</html>
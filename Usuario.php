<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['ID_Usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = (int)$_SESSION['ID_Usuario'];
$mensagem = "";

// 1. Prepara o terreno automaticamente (Cria coluna e pasta se não existirem)
$conexao->query("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS Nome_Exibicao VARCHAR(100) DEFAULT NULL");
$conexao->query("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS Foto_Perfil VARCHAR(255) DEFAULT NULL");

$diretorio_uploads = 'uploads/';
if (!is_dir($diretorio_uploads)) {
    mkdir($diretorio_uploads, 0777, true);
}

/* =========================================================
   2. ATUALIZAR PERFIL E AVATAR
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_perfil'])) {
    $nome = trim($_POST['nome']);
    $nome_exibicao = trim($_POST['nome_exibicao']);
    $email = trim($_POST['email']);
    $caminho_foto = null;

    // Lógica de Upload da Foto
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($extensao, $extensoes_permitidas)) {
            // Cria um nome único para não substituir fotos de outros usuários
            $novo_nome = "avatar_" . $id_usuario . "_" . time() . "." . $extensao;
            $destino = $diretorio_uploads . $novo_nome;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destino)) {
                $caminho_foto = $destino;
            }
        } else {
            $mensagem = "<div class='erro'>Formato de imagem inválido. Use JPG, PNG ou GIF.</div>";
        }
    }

    // Atualiza o banco (com ou sem foto nova)
    if ($caminho_foto) {
        $stmt = $conexao->prepare("UPDATE usuario SET Nome = ?, Nome_Exibicao = ?, Email = ?, Foto_Perfil = ? WHERE ID_usuario = ?");
        $stmt->bind_param("ssssi", $nome, $nome_exibicao, $email, $caminho_foto, $id_usuario);
    } else {
        $stmt = $conexao->prepare("UPDATE usuario SET Nome = ?, Nome_Exibicao = ?, Email = ? WHERE ID_usuario = ?");
        $stmt->bind_param("sssi", $nome, $nome_exibicao, $email, $id_usuario);
    }

    if (isset($stmt) && $stmt->execute()) {
        $mensagem = "<div class='sucesso'>Perfil atualizado com sucesso!</div>";
    } elseif (!isset($mensagem) || $mensagem === '') {
        $mensagem = "<div class='erro'>Erro ao atualizar. Tente novamente.</div>";
    }
    if (isset($stmt)) $stmt->close();
}

/* =========================================================
   3. ALTERAR SENHA
========================================================= */
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
            if ($stmtUp->execute()) {
                $mensagem = "<div class='sucesso'>Senha alterada com segurança!</div>";
            }
            $stmtUp->close();
        } else {
            $mensagem = "<div class='erro'>A senha atual está incorreta!</div>";
        }
    }
}

/* =========================================================
   BUSCAR DADOS ATUAIS
========================================================= */
$stmtDados = $conexao->prepare("SELECT Nome, Nome_Exibicao, Email, Nivel_Acesso, Foto_Perfil FROM usuario WHERE ID_usuario = ?");
$stmtDados->bind_param("i", $id_usuario);
$stmtDados->execute();
$usuario = $stmtDados->get_result()->fetch_assoc();
$stmtDados->close();

$foto_atual = !empty($usuario['Foto_Perfil']) ? $usuario['Foto_Perfil'] : 'https://via.placeholder.com/150/222222/FFD700?text=Foto';
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

/* Estilos do Avatar */
.avatar-container { display: flex; flex-direction: column; align-items: center; margin-bottom: 20px; }
.avatar-preview { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid gold; margin-bottom: 15px; background: #222; }

@media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
</style>
</head>
<body>

<header>
    <div class="logo">Legends_Games</div>
    <a href="tela_inicial.php" class="voltar">⬅ Voltar à Loja</a>
</header>

<?= $mensagem ?>

<div class="container">
    <div class="box">
        <h2>👤 Meus Dados <?php if($usuario['Nivel_Acesso'] == 1) echo '<span class="tag-admin">Administrador</span>'; ?></h2>
        
        <!-- O enctype="multipart/form-data" é OBRIGATÓRIO para enviar arquivos -->
        <form method="POST" enctype="multipart/form-data">
            
            <div class="avatar-container">
                <img src="<?= htmlspecialchars($foto_atual) ?>" class="avatar-preview" alt="Foto de Perfil">
                <input type="file" name="avatar" accept="image/png, image/jpeg, image/jpg, image/gif">
            </div>

            <label>Nome Completo</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($usuario['Nome'] ?? '') ?>" required>

            <label>Nome de Exibição (Fórum/Comentários)</label>
            <input type="text" name="nome_exibicao" placeholder="Como os outros te verão" value="<?= htmlspecialchars($usuario['Nome_Exibicao'] ?? '') ?>">

            <label>E-mail</label>
            <input type="email" name="email" value="<?= htmlspecialchars($usuario['Email'] ?? '') ?>" required>

            <button type="submit" name="atualizar_perfil">💾 Salvar Alterações</button>
        </form>
    </div>

    <div class="box">
        <h2>🔒 Alterar Senha</h2>
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
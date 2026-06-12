<?php
// Inclui a conexão com o banco
include "conexao.php";
session_start();

// Simula usuário logado (substitua pela lógica do seu login)
$id_usuario = 1;

// Inicializa mensagem
$mensagem = "";

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_email = trim($_POST['email']);
    $nova_senha = trim($_POST['senha']);

    if ($novo_email != "" && $nova_senha != "") {
        if (!filter_var($novo_email, FILTER_VALIDATE_EMAIL)) {
            $mensagem = "Email inválido!";
        } else {
            // Cria hash da nova senha
            $hash_senha = password_hash($nova_senha, PASSWORD_DEFAULT);

            // Atualiza email e senha no banco
            $stmt = mysqli_prepare($conexao, "UPDATE usuario SET Email = ?, Senha = ? WHERE ID_usuario = ?");
            mysqli_stmt_bind_param($stmt, "ssi", $novo_email, $hash_senha, $id_usuario);

            if (mysqli_stmt_execute($stmt)) {
                $mensagem = "Email e senha atualizados com sucesso!";
            } else {
                $mensagem = "Erro ao atualizar dados: " . mysqli_error($conexao);
            }

            mysqli_stmt_close($stmt);
        }
    } else {
        $mensagem = "Preencha todos os campos!";
    }
}

// Busca email atual do usuário
$stmt = mysqli_prepare($conexao, "SELECT Email FROM usuario WHERE ID_usuario = ?");
mysqli_stmt_bind_param($stmt, "i", $id_usuario);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $email_atual);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Configuração do Usuário</title>
<style>
body{
    font-family:Segoe UI;
    background:#111;
    color:#ffd700;
    padding:20px;
}
form{
    background:rgba(0,0,0,0.8);
    padding:20px;
    border-radius:10px;
    max-width:400px;
    margin:auto;
    border:2px solid #ffd700;
}
input{
    width:100%;
    padding:10px;
    margin-bottom:10px;
    border:none;
    border-radius:5px;
    outline:none;
}
button{
    width:100%;
    padding:10px;
    background:#ffd700;
    border:none;
    border-radius:5px;
    font-weight:bold;
    cursor:pointer;
}
button:hover{
    background:#fff;
    color:#000;
}
.mensagem{
    text-align:center;
    margin-bottom:10px;
    color:#ffcc00;
}
</style>
</head>
<body>

<h1 style="text-align:center;">Configuração do Usuário</h1>

<?php if($mensagem != ""): ?>
<p class="mensagem"><?= htmlspecialchars($mensagem) ?></p>
<?php endif; ?>

<form method="post">
    <label>Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($email_atual) ?>" required>

    <label>Nova Senha:</label>
    <input type="password" name="senha" placeholder="Digite a nova senha" required>

    <button type="submit">Atualizar</button>
</form>

</body>
</html>
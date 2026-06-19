<?php
include "conexao.php";
include "mail.php";
session_start();

// =========================
// USUÁRIO LOGADO
// =========================
if (!isset($_SESSION["ID_Usuario"])) {
    header("Location: login.php");
    exit;
}

$id_usuario = (int)$_SESSION["ID_Usuario"];
$mensagem = "";

// =========================
// ABA ATUAL
// =========================
if (!isset($_SESSION["aba"])) {
    $_SESSION["aba"] = "dados";
}

$aba = $_SESSION["aba"];

// =========================
// ETAPA SENHA
// =========================
if (!isset($_SESSION["etapa_senha"])) {
    $_SESSION["etapa_senha"] = 1;
}

$etapa = $_SESSION["etapa_senha"];

// =========================
// BUSCAR USUÁRIO
// =========================
$sql = "SELECT Email, CPF, Celular, Senha FROM usuario WHERE ID_usuario = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_usuario);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $email, $cpf, $celular, $senha_hash);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// =========================
// ATUALIZAR DADOS
// =========================
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") == "dados") {

    $novo_email = trim($_POST["email"]);
    $novo_cpf = trim($_POST["cpf"]);
    $novo_celular = trim($_POST["celular"]);
    $senha_confirmacao = $_POST["senha_confirmacao"];

    if (!password_verify($senha_confirmacao, $senha_hash)) {
        $mensagem = "Senha incorreta.";
    } else {

        $sql = "UPDATE usuario SET Email=?, CPF=?, Celular=? WHERE ID_usuario=?";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $novo_email, $novo_cpf, $novo_celular, $id_usuario);

        if (mysqli_stmt_execute($stmt)) {
            $mensagem = "Dados atualizados com sucesso!";
            $email = $novo_email;
            $cpf = $novo_cpf;
            $celular = $novo_celular;
        } else {
            $mensagem = "Erro ao atualizar dados.";
        }

        mysqli_stmt_close($stmt);
    }
}

// =========================
// RECUPERAÇÃO DE SENHA
// =========================

// ENVIAR CÓDIGO (AGORA COM PHPMailer)
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") == "enviar_codigo") {

    $_SESSION["aba"] = "senha";

    $email_input = trim($_POST["email"]);

    $sql = "SELECT ID_usuario FROM usuario WHERE Email = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email_input);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id_user);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$id_user) {
        $mensagem = "E-mail não encontrado.";
    } else {

        $_SESSION["rec_id"] = $id_user;
        $_SESSION["codigo_email"] = rand(100000, 999999);
        $_SESSION["etapa_senha"] = 2;

        // ✔ AGORA USA PHPMailer
        enviarEmail(
            $email_input,
            "Código de verificação",
            "Seu código é: <b>" . $_SESSION["codigo_email"] . "</b>"
        );

        header("Location: " . $_SERVER["PHP_SELF"]);
        exit;
    }
}

// VERIFICAR CÓDIGO
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") == "verificar_codigo") {

    $_SESSION["aba"] = "senha";

    if ($_POST["codigo"] == $_SESSION["codigo_email"]) {
        $_SESSION["etapa_senha"] = 3;
    } else {
        $mensagem = "Código inválido.";
    }
}

// TROCAR SENHA
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") == "trocar_senha") {

    $_SESSION["aba"] = "senha";

    if ($_POST["nova_senha"] != $_POST["confirmar_senha"]) {
        $mensagem = "Senhas não coincidem.";
    } else {

        $hash = password_hash($_POST["nova_senha"], PASSWORD_DEFAULT);

        $sql = "UPDATE usuario SET Senha=? WHERE ID_usuario=?";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "si", $hash, $_SESSION["rec_id"]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        unset($_SESSION["etapa_senha"]);
        unset($_SESSION["codigo_email"]);
        unset($_SESSION["rec_id"]);

        $mensagem = "Senha alterada com sucesso!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Configurações</title>

<style>
body{
    background:#111;
    color:#ffd700;
    font-family:Arial;
    margin:0;
    padding:20px;
}

.container{
    max-width:700px;
    margin:auto;
}

.voltar{
    display:inline-block;
    padding:10px 18px;
    background:#1a1a1a;
    border:2px solid #ffd700;
    color:#ffd700;
    text-decoration:none;
    font-weight:bold;
    border-radius:10px;
}

.card{
    background:#1a1a1a;
    border:2px solid #ffd700;
    border-radius:10px;
    padding:20px;
    margin-top:20px;
}

label{
    display:block;
    margin-top:10px;
    font-weight:bold;
}

input{
    width:100%;
    padding:10px;
    margin-top:5px;
    border-radius:6px;
    border:1px solid #333;
    background:#0f0f0f;
    color:white;
}

button{
    width:100%;
    padding:12px;
    margin-top:15px;
    background:#ffd700;
    border:none;
    font-weight:bold;
    cursor:pointer;
}

.menu{
    display:flex;
    gap:10px;
}

.menu button{
    flex:1;
}

.mensagem{
    text-align:center;
    margin:10px 0;
}
</style>
</head>

<body>

<div class="container">

<a class="voltar" href="biblioteca.php">⬅ Voltar</a>

<h1>Configurações</h1>

<p class="mensagem"><?= htmlspecialchars($mensagem) ?></p>

<div class="menu">
    <button onclick="show('dados')">Dados</button>
    <button onclick="show('senha')">Senha</button>
</div>

<!-- DADOS -->
<div class="card" id="dados" style="<?= $aba=='dados'?'':'display:none' ?>">

<h2>Dados</h2>

<form method="POST">
<input type="hidden" name="acao" value="dados">

<label>Email</label>
<input name="email" value="<?= htmlspecialchars($email) ?>">

<label>CPF</label>
<input name="cpf" value="<?= htmlspecialchars($cpf) ?>">

<label>Celular</label>
<input name="celular" value="<?= htmlspecialchars($celular) ?>">

<label>Senha atual</label>
<input type="password" name="senha_confirmacao">

<button>Salvar</button>
</form>

</div>

<!-- SENHA -->
<div class="card" id="senha" style="<?= $aba=='senha'?'':'display:none' ?>">

<h2>Recuperar Senha</h2>

<?php if($etapa == 1): ?>

<form method="POST">
<input type="hidden" name="acao" value="enviar_codigo">

<label>Email</label>
<input type="email" name="email">

<button>Enviar Código</button>
</form>

<?php elseif($etapa == 2): ?>

<form method="POST">
<input type="hidden" name="acao" value="verificar_codigo">

<label>Código</label>
<input name="codigo">

<button>Verificar</button>
</form>

<?php elseif($etapa == 3): ?>

<form method="POST">
<input type="hidden" name="acao" value="trocar_senha">

<label>Nova senha</label>
<input type="password" name="nova_senha">

<label>Confirmar senha</label>
<input type="password" name="confirmar_senha">

<button>Alterar senha</button>
</form>

<?php endif; ?>

</div>

</div>

<script>
function show(tab){
    document.getElementById("dados").style.display = "none";
    document.getElementById("senha").style.display = "none";
    document.getElementById(tab).style.display = "block";
}
</script>

</body>
</html>
<?php
include "conexao.php";
session_start();

$id_usuario = 1;
$mensagem = "";

// =========================
// CONTROLAR ETAPA
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


// ===========================
// ATUALIZAR DADOS
// ===========================
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
            $mensagem = "Dados atualizados com sucesso.";
            $email = $novo_email;
            $cpf = $novo_cpf;
            $celular = $novo_celular;
        } else {
            $mensagem = "Erro ao atualizar dados.";
        }

        mysqli_stmt_close($stmt);
    }
}


// ===========================
// ETAPA 1 - ENVIAR CÓDIGO (EMAIL DO BANCO)
// ===========================
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") == "enviar_codigo") {

    $_SESSION["codigo_email"] = rand(100000, 999999);
    $_SESSION["etapa_senha"] = 2;

    mail(
        $email,
        "Código de verificação",
        "Seu código é: " . $_SESSION["codigo_email"]
    );

    header("Location: " . $_SERVER["PHP_SELF"]);
    exit;
}


// ===========================
// ETAPA 2 - VERIFICAR CÓDIGO
// ===========================
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") == "verificar_codigo") {

    $codigo = trim($_POST["codigo"]);

    if ($codigo != $_SESSION["codigo_email"]) {
        $mensagem = "Código inválido.";
    } else {

        $_SESSION["etapa_senha"] = 3;

        header("Location: " . $_SERVER["PHP_SELF"]);
        exit;
    }
}


// ===========================
// ETAPA 3 - TROCAR SENHA
// ===========================
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") == "trocar_senha") {

    $nova_senha = $_POST["nova_senha"];
    $confirmar = $_POST["confirmar_senha"];

    if ($nova_senha != $confirmar) {
        $mensagem = "Senhas não coincidem.";
    } elseif (strlen($nova_senha) < 6) {
        $mensagem = "Senha muito curta.";
    } else {

        $hash = password_hash($nova_senha, PASSWORD_DEFAULT);

        $sql = "UPDATE usuario SET Senha=? WHERE ID_usuario=?";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "si", $hash, $id_usuario);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        unset($_SESSION["etapa_senha"]);
        unset($_SESSION["codigo_email"]);

        header("Location: " . $_SERVER["PHP_SELF"]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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

/* VOLTAR */
.voltar{
    position:absolute;
    top:10px;
    left:10px;
    color:#ffd700;
    text-decoration:none;
}

/* CARD */
.card{
    background:#1a1a1a;
    border:2px solid #ffd700;
    border-radius:10px;
    padding:20px;
    margin-top:20px;
}

input,button{
    width:100%;
    padding:10px;
    margin-top:10px;
    border:none;
    border-radius:5px;
}

button{
    background:#ffd700;
    font-weight:bold;
    cursor:pointer;
}

button:hover{
    background:white;
}

.mensagem{
    text-align:center;
    margin-bottom:10px;
}

.menu{
    display:flex;
    gap:10px;
}

.menu button{
    flex:1;
}

</style>
</head>

<body>

<div class="container">

<a class="voltar" href="biblioteca.php">⬅ Voltar</a>

<h1>Configurações da Conta</h1>

<?php if(!empty($mensagem)): ?>
<div class="mensagem"><?php echo htmlspecialchars($mensagem); ?></div>
<?php endif; ?>

<!-- MENU -->
<div class="menu">
    <button onclick="showTab('dados')">Dados</button>
    <button onclick="showTab('senha')">Senha</button>
</div>

<!-- DADOS -->
<div class="card" id="tab-dados">

<h2>Dados</h2>

<form method="POST">
<input type="hidden" name="acao" value="dados">

<label>Email</label>
<input name="email" value="<?php echo htmlspecialchars($email); ?>">

<label>CPF</label>
<input name="cpf" value="<?php echo htmlspecialchars($cpf); ?>">

<label>Celular</label>
<input name="celular" value="<?php echo htmlspecialchars($celular); ?>">

<label>Confirme sua senha</label>
<input type="password" name="senha_confirmacao" required>

<button>Salvar</button>
</form>

</div>

<!-- SENHA -->
<div class="card" id="tab-senha" style="display:none;">

<h2>Trocar Senha</h2>

<?php if($etapa == 1): ?>

<!-- ETAPA 1 -->
<form method="POST">
<input type="hidden" name="acao" value="enviar_codigo">

<p>Clique abaixo para receber o código no seu e-mail cadastrado.</p>

<button>Enviar Código</button>
</form>

<?php elseif($etapa == 2): ?>

<!-- ETAPA 2 -->
<form method="POST">
<input type="hidden" name="acao" value="verificar_codigo">

<label>Código recebido</label>
<input name="codigo" required>

<button>Verificar Código</button>
</form>

<?php elseif($etapa == 3): ?>

<!-- ETAPA 3 -->
<form method="POST">
<input type="hidden" name="acao" value="trocar_senha">

<label>Nova Senha</label>
<input type="password" name="nova_senha" required>

<label>Confirmar Senha</label>
<input type="password" name="confirmar_senha" required>

<button>Alterar Senha</button>
</form>

<?php endif; ?>

</div>

</div>

<script>
function showTab(tab){
    document.getElementById("tab-dados").style.display = "none";
    document.getElementById("tab-senha").style.display = "none";
    document.getElementById("tab-" + tab).style.display = "block";
}
</script>

</body>
</html>
<?php
include "conexao.php";

session_start();

// Troque depois pelo usuário logado
$id_usuario = 1;

$mensagem = "";

// Buscar dados do usuário
$sql = "SELECT Email, CPF, Celular, Senha
        FROM usuario
        WHERE ID_usuario = ?";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_usuario);
mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result(
    $stmt,
    $email,
    $cpf,
    $celular,
    $senha_hash
);

mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);


// ===========================
// ATUALIZAR DADOS
// ===========================
if (
    $_SERVER["REQUEST_METHOD"] == "POST" &&
    isset($_POST["acao"]) &&
    $_POST["acao"] == "dados"
) {

    $novo_email = trim($_POST["email"]);
    $novo_cpf = trim($_POST["cpf"]);
    $novo_celular = trim($_POST["celular"]);
    $senha_confirmacao = $_POST["senha_confirmacao"];

    if (!password_verify($senha_confirmacao, $senha_hash)) {

        $mensagem = "Senha incorreta.";

    } else {

        $sql = "
        UPDATE usuario
        SET
            Email = ?,
            CPF = ?,
            Celular = ?
        WHERE ID_usuario = ?";

        $stmt = mysqli_prepare($conexao, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "sssi",
            $novo_email,
            $novo_cpf,
            $novo_celular,
            $id_usuario
        );

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
// ALTERAR SENHA
// ===========================
if (
    $_SERVER["REQUEST_METHOD"] == "POST" &&
    isset($_POST["acao"]) &&
    $_POST["acao"] == "senha"
) {

    $senha_atual = $_POST["senha_atual"];
    $nova_senha = $_POST["nova_senha"];
    $confirmar_senha = $_POST["confirmar_senha"];

    if (!password_verify($senha_atual, $senha_hash)) {

        $mensagem = "Senha atual incorreta.";

    } elseif ($nova_senha != $confirmar_senha) {

        $mensagem = "As senhas não coincidem.";

    } elseif (strlen($nova_senha) < 6) {

        $mensagem = "A senha deve ter pelo menos 6 caracteres.";

    } else {

        $novo_hash = password_hash(
            $nova_senha,
            PASSWORD_DEFAULT
        );

        $sql = "
        UPDATE usuario
        SET Senha = ?
        WHERE ID_usuario = ?";

        $stmt = mysqli_prepare($conexao, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $novo_hash,
            $id_usuario
        );

        if (mysqli_stmt_execute($stmt)) {

            $mensagem = "Senha alterada com sucesso.";

        } else {

            $mensagem = "Erro ao alterar senha.";
        }

        mysqli_stmt_close($stmt);
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
    font-family:Arial,sans-serif;
    margin:0;
    padding:20px;
}

.container{
    max-width:700px;
    margin:auto;
}

.card{
    background:#1a1a1a;
    border:2px solid #ffd700;
    border-radius:10px;
    padding:20px;
    margin-bottom:25px;
}

h1,h2{
    text-align:center;
}

label{
    display:block;
    margin-top:10px;
    margin-bottom:5px;
}

input{
    width:100%;
    padding:10px;
    border:none;
    border-radius:5px;
    box-sizing:border-box;
}

button{
    width:100%;
    margin-top:15px;
    padding:12px;
    border:none;
    border-radius:5px;
    background:#ffd700;
    color:#000;
    font-weight:bold;
    cursor:pointer;
}

button:hover{
    background:#fff;
}

.mensagem{
    text-align:center;
    color:white;
    margin-bottom:20px;
}

</style>
</head>
<body>

<div class="container">

<h1>Configurações da Conta</h1>

<?php if(!empty($mensagem)): ?>
<div class="mensagem">
    <?php echo htmlspecialchars($mensagem); ?>
</div>
<?php endif; ?>


<!-- DADOS CADASTRAIS -->
<div class="card">

<h2>Dados Cadastrais</h2>

<form method="POST">

<input type="hidden" name="acao" value="dados">

<label>Email</label>
<input
type="email"
name="email"
value="<?php echo htmlspecialchars($email); ?>"
required>

<label>CPF</label>
<input
type="text"
name="cpf"
value="<?php echo htmlspecialchars($cpf); ?>">

<label>Celular</label>
<input
type="text"
name="celular"
value="<?php echo htmlspecialchars($celular); ?>">

<label>Confirme sua senha</label>
<input
type="password"
name="senha_confirmacao"
required>

<button type="submit">
Salvar Dados
</button>

</form>

</div>


<!-- ALTERAR SENHA -->
<div class="card">

<h2>Alterar Senha</h2>

<form method="POST">

<input type="hidden" name="acao" value="senha">

<label>Senha Atual</label>
<input
type="password"
name="senha_atual"
required>

<label>Nova Senha</label>
<input
type="password"
name="nova_senha"
required>

<label>Confirmar Nova Senha</label>
<input
type="password"
name="confirmar_senha"
required>

<button type="submit">
Trocar Senha
</button>

</form>

</div>

</div>

</body>
</html>
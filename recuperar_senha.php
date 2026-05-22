<?php
session_start();

// =======================
// CONEXÃO DIRETA
// =======================
$servidor = "localhost";
$usuario = "root";
$senhaBanco = "";
$banco = "legends_games_1";

$conexao = new mysqli($servidor, $usuario, $senhaBanco, $banco);
$conexao->set_charset("utf8");

if ($conexao->connect_error) {
    die("Erro na conexão: " . $conexao->connect_error);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/src/Exception.php';
require __DIR__ . '/src/PHPMailer.php';
require __DIR__ . '/src/SMTP.php';

$step = $_GET["step"] ?? 1;
$msg = "";

/* =========================
   ENVIAR CÓDIGO
========================= */
if (isset($_POST["enviar"])) {

    $email = trim($_POST["email"]);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Email inválido!";
        $step = 1;
    } else {

        $codigo = rand(100000, 999999);

        $stmt = $conexao->prepare("
            UPDATE usuarios 
            SET codigo_reset=? 
            WHERE contato=?
        ");
        $stmt->bind_param("ss", $codigo, $email);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {

            $_SESSION["reset_email"] = $email;
            $_SESSION["reset_codigo"] = $codigo;

            $mail = new PHPMailer(true);

            try {

                $mail->SMTPDebug = 0;
                $mail->Timeout = 15;

                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;

                $mail->Username = 'mfelipelopesfs@gmail.com';
                $mail->Password = 'vgivswzajvarabvo';

                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                $mail->CharSet = 'UTF-8';

                $mail->setFrom('SEUEMAIL@gmail.com', 'Sistema Trabalho');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Código de recuperação';

                $mail->Body = "
                    <h2>Recuperação de senha</h2>
                    <p>Seu código é:</p>
                    <h1 style='color:blue'>$codigo</h1>
                ";

                $mail->send();

                $msg = "Código enviado com sucesso!";
                $step = 2;

            } catch (Exception $e) {

                $msg = "Erro real: " . $mail->ErrorInfo;
            }

        } else {
            $msg = "Email não encontrado!";
        }
    }
}

/* =========================
   VERIFICAR CÓDIGO
========================= */
if (isset($_POST["verificar"])) {

    $codigo = trim($_POST["codigo"]);

    if ($codigo == ($_SESSION["reset_codigo"] ?? "")) {
        $step = 3;
    } else {
        $msg = "Código inválido!";
        $step = 2;
    }
}

/* =========================
   RESET SENHA
========================= */
if (isset($_POST["reset"])) {

    $email = $_SESSION["reset_email"] ?? "";

    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);

    $stmt = $conexao->prepare("
        UPDATE usuarios 
        SET senha=?, codigo_reset=NULL 
        WHERE contato=?
    ");
    $stmt->bind_param("ss", $senha, $email);
    $stmt->execute();

    unset($_SESSION["reset_email"]);
    unset($_SESSION["reset_codigo"]);

    echo "<script>
        alert('Senha alterada com sucesso!');
        window.location='login.php';
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Recuperar Senha</title>

<style>

body{
    margin:0;
    padding:0;

    background:
    radial-gradient(circle at center, #07111f, #02060d);

    color:#FFD700;

    font-family:Arial;

    display:flex;
    justify-content:center;
    align-items:center;

    height:100vh;
}

.box{

    background:#0b1726;

    padding:30px;

    border-radius:20px;

    width:320px;

    text-align:center;

    border:2px solid #f5a623;

    box-shadow:
    0 0 25px rgba(245,166,35,0.7);
}

.logo{
    width:280px;
    margin-bottom:20px;
    border-radius:10px;
}

h2{
    color:#f5a623;
}

input,button{

    width:100%;

    padding:12px;

    margin-top:10px;

    border:none;

    border-radius:10px;

    box-sizing:border-box;
}

input{
    background:#091521;
    color:#fff;
    border:1px solid #2c4c6e;
    outline:none;
}

button{

    background:
    linear-gradient(90deg, #ff8c00, #ff3c00);

    color:#000;

    font-weight:bold;

    cursor:pointer;
}

.msg{
    margin-top:15px;
    color:white;
}

a{
    display:block;
    margin-top:10px;
    color:#f5a623;
    text-decoration:none;
}

a:hover{
    color:white;
}

</style>
</head>

<body>

<div class="box">


<?php if($step == 1): ?>

<h2> Recuperar Senha</h2>

<form method="POST">
    <input type="email" name="email" placeholder="Digite seu email" required>
    <button name="enviar">Enviar código</button>
</form>

<?php elseif($step == 2): ?>

<h2>📨 Verificar Código</h2>

<form method="POST">
    <input type="text" name="codigo" placeholder="Código" required>
    <button name="verificar">Verificar</button>
</form>

<?php elseif($step == 3): ?>

<h2>🔑 Nova Senha</h2>

<form method="POST">
    <input type="password" name="senha" placeholder="Nova senha" required>
    <button name="reset">Salvar</button>
</form>

<?php endif; ?>

<div class="msg"><?= $msg ?></div>

<a href="login.php">← Voltar login</a>

</div>

</body>
</html>
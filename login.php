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

$erro = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $user = trim($_POST["user"]);
    $pass = $_POST["pass"];

    $userLimpo = preg_replace('/[^0-9a-zA-Z@._]/', '', $user);

    // 🔥 ALTERADO AQUI: usuarios -> usuario
$stmt = $conexao->prepare("SELECT Senha, Email FROM usuario WHERE Email=? LIMIT 1");

    if(!$stmt){
        die("Erro SQL: " . $conexao->error);
    }

    $stmt->bind_param("s", $userLimpo);
    $stmt->execute();

    $result = $stmt->get_result();

    if($row = $result->fetch_assoc()){

        $hash = $row["senha"];
        $contato = $row["contato"];

        if(password_verify($pass, $hash)){

            $_SESSION["logado"] = true;
            $_SESSION["usuario"] = $contato;

            header("Location: tela_inicial.php");
            exit;

        } else {
            $erro = "Senha incorreta!";
        }

    } else {
        $erro = "Usuário não encontrado!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Login</title>

<style>

body{
    margin:0;
    padding:0;

    background:
    radial-gradient(circle at center, #07111f, #02060d);

    color:#FFD700;

    display:flex;
    justify-content:center;
    align-items:center;

    height:100vh;

    font-family:Arial;
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

input{

    width:100%;

    padding:12px;

    margin-top:10px;

    border-radius:10px;

    border:1px solid #2c4c6e;

    background-color:#091521;

    color:white;

    outline:none;

    box-sizing:border-box;
}

button{

    width:100%;

    padding:12px;

    margin-top:20px;

    background:
    linear-gradient(90deg, #ff8c00, #ff3c00);

    border:none;

    border-radius:30px;

    color:black;

    font-weight:bold;

    cursor:pointer;
}

a{
    color:#f5a623;
    display:block;
    margin-top:10px;
    text-decoration:none;
}

a:hover{
    color:#ffffff;
}

.erro{
    color:red;
    margin-top:10px;
}

</style>

</head>

<body>

<div class="box">

<img src="imagens/Criatura_Maldita.jpeg" class="logo">

<h2>🎮 Login</h2>

<form method="POST">

<input
name="user"
placeholder="Email, CPF ou Celular"
>

<input
name="pass"
type="password"
placeholder="Senha"
>

<button>Entrar</button> 

</form>

<p class="erro"><?php echo $erro; ?></p>

<a href="cadastro.php">Criar conta</a>

<a href="recuperar_senha.php">
Esqueci minha senha
</a>

</div>

</body>
</html>
<?php
session_start();

require_once 'conexao.php';

$erro = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $user = trim($_POST["user"]);
    $pass = $_POST["pass"];

    // Limpa os caracteres especiais se for CPF ou Celular
    $userLimpo = preg_replace('/[^0-9a-zA-Z@._]/', '', $user);

    // 🔥 Busca por Email, CPF ou Celular na tabela oficial "usuario"
    $sql = "SELECT ID_usuario, Nome, Email, Senha FROM usuario WHERE Email=? OR cpf=? OR celular=? LIMIT 1";
    $stmt = $conexao->prepare($sql);

    if(!$stmt){
        die("Erro SQL: " . $conexao->error);
    }

    // Passamos a mesma variável 3 vezes para checar os 3 campos
    $stmt->bind_param("sss", $userLimpo, $userLimpo, $userLimpo);
    $stmt->execute();

    $result = $stmt->get_result();

    if($row = $result->fetch_assoc()){

        $idUsuario = $row["ID_usuario"];
        $hash = $row["Senha"];
        $contato = $row["Email"];

        // Verifica se a senha digitada bate com a criptografada no banco
        if(password_verify($pass, $hash)){

            $_SESSION["logado"] = true;
            $_SESSION["usuario"] = $contato;

            // Define a Sessão com o ID correto para o resto do site funcionar
            $_SESSION["ID_Usuario"] = $idUsuario;

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
    background: radial-gradient(circle at center, #07111f, #02060d);
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
    box-shadow:0 0 25px rgba(245,166,35,0.7);
}

.logo{
    width:280px;
    margin-bottom:20px;
    border-radius:10px;
}

h2{ color:#f5a623; }

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
    background:linear-gradient(90deg, #ff8c00, #ff3c00);
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

a:hover{ color:#ffffff; }

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

<input name="user" placeholder="Email, CPF ou Celular" required>

<input name="pass" type="password" placeholder="Senha" required>

<button>Entrar</button> 

</form>

<p class="erro"><?php echo $erro; ?></p>

<a href="cadastro.php">Criar conta</a>

<a href="recuperar_senha.php">Esqueci minha senha</a>

</div>

</body>
</html>
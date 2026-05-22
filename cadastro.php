<?php

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

$msg = "";

// =======================
// VALIDA SENHA FORTE
// =======================
function senhaForte($senha){
    return preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{6,}$/', $senha);
}

// CPF
function validarCPF($cpf){

    $cpf = preg_replace('/[^0-9]/','',$cpf);

    if(strlen($cpf)!=11) return false;

    if(preg_match('/(\d)\1{10}/',$cpf)) return false;

    for($t=9;$t<11;$t++){

        for($d=0,$c=0;$c<$t;$c++){
            $d += $cpf[$c] * (($t+1)-$c);
        }

        $d = ((10*$d)%11)%10;

        if($cpf[$t] != $d) return false;
    }

    return true;
}

// EMAIL
function validarEmailReal($email){
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// CELULAR
function validarCelular($celular){

    $celular = preg_replace('/[^0-9]/','',$celular);

    return preg_match('/^[1-9]{2}9[0-9]{8}$/',$celular);
}

// =======================
// CADASTRO
// =======================
if(isset($_POST['cadastrar'])){

    $contato = trim($_POST['contato']);
    $senha   = $_POST['senha'];

    if(empty($contato) || empty($senha)){

        $msg = "<p class='error'>Preencha todos os campos!</p>";

    }
    elseif(!senhaForte($senha)){

        $msg = "<p class='error'>Senha fraca! Use letras, números e símbolo.</p>";

    }
    else {

        $email = "";
        $cpf = "";
        $celular = "";

        if(preg_match('/^[0-9]{11}$/',$contato) && validarCPF($contato)){

            $cpf = $contato;

        }
        elseif(validarEmailReal($contato)){

            $email = $contato;

        }
        elseif(validarCelular($contato)){

            $celular = $contato;

        }
        else {

            $msg = "<p class='error'>CPF, Email ou Celular inválido!</p>";
        }

        if(empty($msg)){

            $stmt = $conexao->prepare("SELECT ID_usuario FROM usuario WHERE Email=? OR cpf=? OR celular=?");

            if(!$stmt){
                die("Erro SELECT: ".$conexao->error);
            }

            $stmt->bind_param("sss",$email,$cpf,$celular);
            $stmt->execute();
            $stmt->store_result();

            if($stmt->num_rows > 0){

                $msg = "<p class='error'>Já cadastrado!</p>";

            } else {

                $senhaCript = password_hash($senha, PASSWORD_DEFAULT);

                $nomePadrao = "Usuário";

                $sql = "INSERT INTO usuario (Nome, Email, Senha, cpf, celular)
                        VALUES (?,?,?,?,?)";

                $stmt = $conexao->prepare($sql);

                if(!$stmt){
                    die("Erro INSERT SQL: ".$conexao->error);
                }

                $stmt->bind_param(
                    "sssss",
                    $nomePadrao,
                    $email,
                    $senhaCript,
                    $cpf,
                    $celular
                );

                if($stmt->execute()){

                    $msg = "<p class='success'>Cadastro realizado com sucesso!</p>";

                } else {

                    $msg = "<p class='error'>Erro ao cadastrar!</p>";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<title>Cadastro Legends Games</title>

<style>

body{
    margin:0;
    padding:0;

    background:
    radial-gradient(circle at center, #07111f, #02060d);

    font-family: Arial, sans-serif;

    height:100vh;

    display:flex;
    justify-content:center;
    align-items:center;
}

.card{

    background:#0b1726;

    padding:40px;

    border-radius:20px;

    width:350px;

    text-align:center;

    border:2px solid #f5a623;

    box-shadow:
    0 0 25px rgba(245,166,35,0.7);
}

h1{
    color:#f5a623;
    margin-bottom:20px;
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

.voltar{

    display:block;

    margin-top:15px;

    padding:12px;

    background:#444;

    color:white;

    text-decoration:none;

    border-radius:30px;

    text-align:center;
}

.voltar:hover{
    background:#666;
}

.error{
    color:red;
    margin-top:10px;
}

.success{
    color:#00ff88;
    margin-top:10px;
}

.logo{
    width:300px;
    margin-bottom:20px;
    border-radius:10px;
}

</style>

</head>

<body>

<div class="card">

<img src="imagens/Criatura_Maldita.jpeg" class="logo">

<h1>Cadastro Seguro</h1>

<?php if(!empty($msg)) echo $msg; ?>

<form method="post">

<input
type="text"
name="contato"
placeholder="CPF, E-mail ou Celular"
required
>

<input
type="password"
name="senha"
placeholder="Senha forte"
required
>

<button name="cadastrar">
Cadastrar
</button>

</form>

<a class="voltar" href="login.php">
← Voltar para o login
</a>

</div>

</body>
</html>
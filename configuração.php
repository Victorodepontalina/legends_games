<?php

include "conexao.php";
session_start();

if(!isset($_SESSION["ID_Usuario"])){
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION["ID_Usuario"];
$mensagem = "";


// BUSCAR USUARIO

$sql = "SELECT 
Email,
CPF,
Celular,
Nome_Exibicao,
Biografia,
Foto,
Tema,
Idioma,
Notificacoes

FROM usuario

WHERE ID_usuario=?";


$stmt = mysqli_prepare($conexao,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$id_usuario
);

mysqli_stmt_execute($stmt);


mysqli_stmt_bind_result(
$stmt,
$email,
$cpf,
$celular,
$nome,
$bio,
$foto,
$tema,
$idioma,
$notificacoes
);


mysqli_stmt_fetch($stmt);

mysqli_stmt_close($stmt);



// SALVAR DADOS

if($_SERVER["REQUEST_METHOD"]=="POST" && $_POST["acao"]=="dados"){

$email = $_POST["email"];
$cpf = $_POST["cpf"];
$celular = $_POST["celular"];


$sql="UPDATE usuario SET

Email=?,
CPF=?,
Celular=?

WHERE ID_usuario=?";


$stmt=mysqli_prepare($conexao,$sql);


mysqli_stmt_bind_param(
$stmt,
"sssi",
$email,
$cpf,
$celular,
$id_usuario
);


mysqli_stmt_execute($stmt);


$mensagem="Dados salvos!";

}



// SALVAR PERFIL

if($_SERVER["REQUEST_METHOD"]=="POST" && $_POST["acao"]=="perfil"){


$nome=$_POST["nome"];
$bio=$_POST["bio"];
$tema=$_POST["tema"];
$idioma=$_POST["idioma"];


$notificacoes =
isset($_POST["notificacoes"]) ? 1 : 0;



// SALVAR FOTO

if(isset($_FILES["foto"]) && $_FILES["foto"]["error"]==0){


if(!is_dir("uploads")){
mkdir("uploads");
}


$foto_nome =
time()."_".$_FILES["foto"]["name"];


$caminho =
"uploads/".$foto_nome;



move_uploaded_file(
$_FILES["foto"]["tmp_name"],
$caminho
);


$foto=$caminho;

}



$sql="UPDATE usuario SET

Nome_Exibicao=?,
Biografia=?,
Foto=?,
Tema=?,
Idioma=?,
Notificacoes=?

WHERE ID_usuario=?";


$stmt=mysqli_prepare($conexao,$sql);


mysqli_stmt_bind_param(
$stmt,
"ssssssi",
$nome,
$bio,
$foto,
$tema,
$idioma,
$notificacoes,
$id_usuario
);


mysqli_stmt_execute($stmt);


$mensagem="Perfil atualizado!";


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
color:white;
font-family:Arial;
padding:20px;
}

.container{
max-width:600px;
margin:auto;
}

h1,h2{
color:#ffd700;
}

.card{
background:#222;
padding:20px;
margin-top:20px;
border-radius:10px;
}

label{
display:block;
margin-top:10px;
}

input,
textarea,
select{

width:90%;
padding:10px;
margin-top:5px;
background:#000;
color:white;
border:1px solid #555;
border-radius:5px;

}


textarea{
height:100px;
}


button{

width:90%;
padding:10px;
margin-top:15px;
background:#ffd700;
border:0;
font-weight:bold;
cursor:pointer;

}


img{

width:100px;
height:100px;
border-radius:50%;
object-fit:cover;

}


a{

color:#ffd700;
text-decoration:none;

}


.msg{

color:#ffd700;
text-align:center;

}

</style>

</head>


<body>


<div class="container">


<a href="tela_inicial.php">
⬅ Voltar
</a>


<h1>
⚙ Configurações
</h1>


<p class="msg">
<?=htmlspecialchars($mensagem)?>
</p>



<div class="card">

<h2>
📄 Dados
</h2>


<form method="POST">


<input type="hidden" name="acao" value="dados">


<label>Email</label>

<input
type="email"
name="email"
value="<?=htmlspecialchars($email)?>"
>


<label>CPF</label>

<input
name="cpf"
value="<?=htmlspecialchars($cpf)?>"
>


<label>Celular</label>

<input
name="celular"
value="<?=htmlspecialchars($celular)?>"
>


<button>
Salvar Dados
</button>


</form>

</div>



<div class="card">


<h2>
👤 Perfil
</h2>


<form method="POST" enctype="multipart/form-data">


<input 
type="hidden"
name="acao"
value="perfil"
>



<?php if(!empty($foto)){ ?>

<img src="<?=htmlspecialchars($foto)?>">

<?php } ?>

<label>Nome de exibição</label>

<input
name="nome"
value="<?=htmlspecialchars($nome)?>"
>


<label>Biografia</label>

<textarea name="bio"><?=htmlspecialchars($bio)?></textarea>

</select>



<label>Idioma</label>

<select name="idioma">

<option value="pt-BR">
Português
</option>

<option value="en">
English
</option>

<option value="es">
Español
</option>

</select>



<label>

<input
type="checkbox"
name="notificacoes"
<?= $notificacoes ? "checked":"" ?>
>

Receber notificações

</label>



<button>
Salvar Perfil
</button>


</form>


</div>


</div>


</body>

</html>

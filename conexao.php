<?php
$host = "localhost";
$user = "root";
$pass = "";
$banco = "legends_games";
$conexao = mysqli_connect($host, $user,$pass,$banco);
mysqli_select_db($conexao,$banco);
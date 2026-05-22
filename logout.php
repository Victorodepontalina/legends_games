<?php
session_start();

// remove todas as variáveis da sessão
session_unset();

// destrói a sessão
session_destroy();

// volta para o login
header("Location: login.php");
exit;
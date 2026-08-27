<?php

session_start();

/* =========================================================
   CONEXÃO COM O BANCO
========================================================= */

$host = "localhost";
$user = "root";
$pass = "";
$db   = "legends_games_1";

$conn = new mysqli(
    $host,
    $user,
    $pass,
    $db
);

if ($conn->connect_error) {
    die(
        "Erro de conexão com o banco: " .
        $conn->connect_error
    );
}

$conn->set_charset("utf8mb4");


/* =========================================================
   CRIAR CARRINHO NA SESSÃO
========================================================= */

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}


/* =========================================================
   ADICIONAR JOGO AO CARRINHO
========================================================= */

if (
    isset($_GET['adicionar']) &&
    $_GET['adicionar'] == 1 &&
    isset($_GET['nome'])
) {

    $nome = trim($_GET['nome']);

    $preco = isset($_GET['preco'])
        ? (float)$_GET['preco']
        : 0;

    $img = isset($_GET['img'])
        ? $_GET['img']
        : '';

    $encontrado = false;

    foreach (
        $_SESSION['carrinho'] as $chave => $item
    ) {

        if ($item['nome'] === $nome) {

            $_SESSION['carrinho'][$chave]['quantidade']++;

            $encontrado = true;

            break;
        }
    }

    if (!$encontrado) {

        $_SESSION['carrinho'][] = [

            'nome' => $nome,

            'preco' => $preco,

            'img' => $img,

            'quantidade' => 1
        ];
    }

    header("Location: carrinho.php");

    exit;
}


/* =========================================================
   REMOVER JOGO
========================================================= */

if (isset($_GET['remover'])) {

    $indice = (int)$_GET['remover'];

    if (isset($_SESSION['carrinho'][$indice])) {

        unset(
            $_SESSION['carrinho'][$indice]
        );

        $_SESSION['carrinho'] = array_values(
            $_SESSION['carrinho']
        );
    }

    header("Location: carrinho.php");

    exit;
}


/* =========================================================
   AUMENTAR QUANTIDADE
========================================================= */

if (isset($_GET['aumentar'])) {

    $indice = (int)$_GET['aumentar'];

    if (isset($_SESSION['carrinho'][$indice])) {

        $_SESSION['carrinho'][$indice]['quantidade']++;
    }

    header("Location: carrinho.php");

    exit;
}


/* =========================================================
   DIMINUIR QUANTIDADE
========================================================= */

if (isset($_GET['diminuir'])) {

    $indice = (int)$_GET['diminuir'];

    if (isset($_SESSION['carrinho'][$indice])) {

        $_SESSION['carrinho'][$indice]['quantidade']--;

        if (
            $_SESSION['carrinho'][$indice]['quantidade'] <= 0
        ) {

            unset(
                $_SESSION['carrinho'][$indice]
            );

            $_SESSION['carrinho'] = array_values(
                $_SESSION['carrinho']
            );
        }
    }

    header("Location: carrinho.php");

    exit;
}


/* =========================================================
   LIMPAR CARRINHO
========================================================= */

if (isset($_GET['limpar'])) {

    $_SESSION['carrinho'] = [];

    header("Location: carrinho.php");

    exit;
}


/* =========================================================
   CALCULAR TOTAL
========================================================= */

$total = 0;

foreach (
    $_SESSION['carrinho'] as $item
) {

    $total +=
        $item['preco'] *
        $item['quantidade'];
}

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Carrinho - Legends Games
    </title>


    <style>

        * {
            box-sizing: border-box;
        }

        body {

            margin: 0;

            font-family: Arial, sans-serif;

            background:
                radial-gradient(
                    circle at top,
                    #1b2838,
                    #05070a
                );

            color: white;

            min-height: 100vh;
        }


        /* CABEÇALHO */

        header {

            background: #0d0f13;

            border-bottom: 2px solid gold;

            padding: 20px;

            display: flex;

            justify-content: space-between;

            align-items: center;
        }


        .logo {

            color: gold;

            font-size: 28px;

            font-weight: bold;
        }


        .voltar {

            background: #222;

            color: gold;

            text-decoration: none;

            padding: 12px 18px;

            border-radius: 8px;

            border: 1px solid gold;
        }


        .voltar:hover {

            background: gold;

            color: black;
        }


        /* CONTAINER */

        .container {

            width: 90%;

            max-width: 1100px;

            margin: 40px auto;
        }


        h1 {

            color: gold;

            margin-bottom: 30px;
        }


        /* ITEM */

        .item {

            background: #14181f;

            border-radius: 15px;

            padding: 20px;

            margin-bottom: 20px;

            display: flex;

            align-items: center;

            gap: 20px;

            border-left: 4px solid gold;
        }


        .item img {

            width: 160px;

            height: 90px;

            object-fit: cover;

            border-radius: 10px;

            background: #000;
        }


        .informacoes {

            flex: 1;
        }


        .informacoes h2 {

            margin: 0 0 10px;

            color: white;
        }


        .preco {

            color: gold;

            font-size: 20px;

            font-weight: bold;
        }


        /* QUANTIDADE */

        .quantidade {

            display: flex;

            align-items: center;

            gap: 10px;
        }


        .botao-quantidade {

            width: 35px;

            height: 35px;

            border: none;

            border-radius: 6px;

            background: #333;

            color: gold;

            font-size: 20px;

            text-decoration: none;

            display: flex;

            align-items: center;

            justify-content: center;
        }


        .botao-quantidade:hover {

            background: gold;

            color: black;
        }


        .numero {

            min-width: 25px;

            text-align: center;

            font-weight: bold;
        }


        /* REMOVER */

        .remover {

            background: #9b2226;

            color: white;

            text-decoration: none;

            padding: 10px 15px;

            border-radius: 8px;
        }


        .remover:hover {

            background: #d62828;
        }


        /* TOTAL */

        .total {

            margin-top: 30px;

            background: #14181f;

            padding: 25px;

            border-radius: 15px;

            text-align: right;

            border: 1px solid #333;
        }


        .total h2 {

            color: gold;

            font-size: 28px;
        }


        .botoes {

            display: flex;

            justify-content: flex-end;

            gap: 15px;

            margin-top: 20px;

            flex-wrap: wrap;
        }


        .continuar {

            background: #222;

            color: gold;

            border: 1px solid gold;

            text-decoration: none;

            padding: 14px 20px;

            border-radius: 8px;

            font-weight: bold;
        }


        .continuar:hover {

            background: gold;

            color: black;
        }


        .limpar {

            background: #9b2226;

            color: white;

            text-decoration: none;

            padding: 14px 20px;

            border-radius: 8px;

            font-weight: bold;
        }


        .finalizar {

            background: gold;

            color: black;

            text-decoration: none;

            padding: 14px 20px;

            border-radius: 8px;

            font-weight: bold;
        }


        .finalizar:hover {

            background: white;
        }


        /* VAZIO */

        .vazio {

            background: #14181f;

            padding: 50px;

            border-radius: 15px;

            text-align: center;
        }


        .vazio h2 {

            color: gold;
        }


        .vazio a {

            display: inline-block;

            margin-top: 20px;

            background: gold;

            color: black;

            padding: 14px 25px;

            text-decoration: none;

            border-radius: 8px;

            font-weight: bold;
        }


        /* RESPONSIVO */

        @media(max-width: 700px) {

            .item {

                flex-direction: column;

                align-items: flex-start;
            }


            .item img {

                width: 100%;

                height: 180px;
            }


            .total {

                text-align: left;
            }


            .botoes {

                justify-content: flex-start;
            }

        }

    </style>

</head>


<body>


<header>

    <div class="logo">

        🎮 Legends Games

    </div>


    <a
        href="tela_inicial.php"
        class="voltar"
    >

        ⬅ Continuar comprando

    </a>

</header>


<div class="container">


    <h1>

        🛒 Meu Carrinho

    </h1>


    <?php if (count($_SESSION['carrinho']) > 0): ?>


        <?php foreach (
            $_SESSION['carrinho'] as $indice => $item
        ): ?>


            <div class="item">


                <img
                    src="<?= htmlspecialchars($item['img']) ?>"
                    alt="<?= htmlspecialchars($item['nome']) ?>"
                    onerror="this.src='https://via.placeholder.com/300x200/111111/FFD700?text=Sem+Imagem'"
                >


                <div class="informacoes">

                    <h2>

                        <?= htmlspecialchars($item['nome']) ?>

                    </h2>


                    <div class="preco">

                        R$

                        <?= number_format(
                            $item['preco'],
                            2,
                            ',',
                            '.'
                        ) ?>

                    </div>


                    <br>


                    <strong>

                        Subtotal:

                    </strong>


                    R$

                    <?= number_format(
                        $item['preco'] *
                        $item['quantidade'],
                        2,
                        ',',
                        '.'
                    ) ?>


                </div>


                <div class="quantidade">


                    <a
                        href="carrinho.php?diminuir=<?= $indice ?>"
                        class="botao-quantidade"
                    >

                        −

                    </a>


                    <div class="numero">

                        <?= $item['quantidade'] ?>

                    </div>


                    <a
                        href="carrinho.php?aumentar=<?= $indice ?>"
                        class="botao-quantidade"
                    >

                        +

                    </a>


                </div>


                <a
                    href="carrinho.php?remover=<?= $indice ?>"
                    class="remover"
                    onclick="return confirm('Remover este jogo do carrinho?')"
                >

                    🗑 Remover

                </a>


            </div>


        <?php endforeach; ?>


        <div class="total">


            <h2>

                Total: R$

                <?= number_format(
                    $total,
                    2,
                    ',',
                    '.'
                ) ?>

            </h2>


            <div class="botoes">


                <a
                    href="tela_inicial.php"
                    class="continuar"
                >

                    ← Continuar comprando

                </a>


                <a
                    href="carrinho.php?limpar=1"
                    class="limpar"
                    onclick="return confirm('Deseja limpar todo o carrinho?')"
                >

                    🗑 Limpar carrinho

                </a>


                <!--
                    AGORA O BOTÃO VAI PARA A TELA DE PAGAMENTO
                -->

                <a
                    href="tela_pagento.php"
                    class="finalizar"
                >

                    ✓ Finalizar compra

                </a>


            </div>


        </div>


    <?php else: ?>


        <div class="vazio">


            <h2>

                🛒 Seu carrinho está vazio

            </h2>


            <p>

                Adicione alguns jogos incríveis!

            </p>


            <a
                href="tela_inicial.php"
            >

                🎮 Ver jogos

            </a>


        </div>


    <?php endif; ?>


</div>


</body>

</html>

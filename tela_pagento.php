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
   USUÁRIO
========================================================= */

/*
   Enquanto o sistema de login não estiver ligado,
   estamos usando o usuário 29.
*/

$id_usuario = 29;


/* =========================================================
   GARANTIR QUE O CARRINHO EXISTE
========================================================= */

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}


/* =========================================================
   VARIÁVEIS
========================================================= */

$itens = [];

$total = 0;

$erro = "";

$sucesso = false;

$forma_pagamento = "";

$autenticacao = "";

$codigo_pix = "";

$id_pagamento = 0;

$quantidade_adicionados = 0;


/* =========================================================
   LER CARRINHO DA SESSÃO
========================================================= */

foreach ($_SESSION['carrinho'] as $indice => $item) {

    /*
       Verificar se o item possui os dados necessários.
    */

    $nome = isset($item['nome'])
        ? trim($item['nome'])
        : 'Jogo';

    $preco = isset($item['preco'])
        ? (float)$item['preco']
        : 0;

    $img = isset($item['img'])
        ? $item['img']
        : '';

    $quantidade = isset($item['quantidade'])
        ? (int)$item['quantidade']
        : 1;


    if ($quantidade < 1) {
        $quantidade = 1;
    }


    /*
       Procurar o ID do jogo pelo nome.

       Isso resolve o problema do seu carrinho antigo,
       que salva nome/preço/imagem mas não salva ID_jogo.
    */

    $id_jogo = 0;


    $sqlJogo = "
        SELECT *
        FROM jogo
        WHERE Nome = ?
        LIMIT 1
    ";

    $stmtJogo = $conn->prepare($sqlJogo);


    if ($stmtJogo) {

        $stmtJogo->bind_param(
            "s",
            $nome
        );

        $stmtJogo->execute();

        $resultadoJogo =
            $stmtJogo->get_result();


        if ($resultadoJogo->num_rows > 0) {

            $jogo = $resultadoJogo->fetch_assoc();

            $id_jogo =
                isset($jogo['ID_jogo'])
                    ? (int)$jogo['ID_jogo']
                    : 0;


            /*
               Se o preço da sessão estiver 0,
               tentar pegar o preço do banco.
            */

            if ($preco <= 0) {

                if (isset($jogo['Preco'])) {

                    $preco =
                        (float)$jogo['Preco'];

                } elseif (isset($jogo['preco'])) {

                    $preco =
                        (float)$jogo['preco'];

                } elseif (isset($jogo['Preco_Unitario'])) {

                    $preco =
                        (float)$jogo['Preco_Unitario'];

                } elseif (isset($jogo['preco_unitario'])) {

                    $preco =
                        (float)$jogo['preco_unitario'];

                } elseif (isset($jogo['Valor'])) {

                    $preco =
                        (float)$jogo['Valor'];

                } elseif (isset($jogo['valor'])) {

                    $preco =
                        (float)$jogo['valor'];
                }
            }
        }

        $stmtJogo->close();
    }


    /*
       Se não encontrou pelo campo Nome,
       tenta outras possibilidades.
    */

    if ($id_jogo <= 0) {

        $possiveisNomes = [
            'nome',
            'Nome_Jogo',
            'nome_jogo',
            'Titulo',
            'titulo'
        ];


        foreach ($possiveisNomes as $campo) {

            $sql = "
                SELECT *
                FROM jogo
                WHERE `$campo` = ?
                LIMIT 1
            ";

            $stmt = @$conn->prepare($sql);

            if (!$stmt) {
                continue;
            }


            $stmt->bind_param(
                "s",
                $nome
            );

            $stmt->execute();

            $resultado =
                $stmt->get_result();


            if ($resultado->num_rows > 0) {

                $jogo =
                    $resultado->fetch_assoc();


                if (isset($jogo['ID_jogo'])) {

                    $id_jogo =
                        (int)$jogo['ID_jogo'];
                }


                if ($preco <= 0) {

                    if (isset($jogo['Preco'])) {

                        $preco =
                            (float)$jogo['Preco'];

                    } elseif (isset($jogo['preco'])) {

                        $preco =
                            (float)$jogo['preco'];
                    }
                }


                $stmt->close();

                break;
            }


            $stmt->close();
        }
    }


    /*
       Subtotal.
    */

    $subtotal =
        $preco * $quantidade;


    $total += $subtotal;


    /*
       Guardar item.
    */

    $itens[] = [

        'indice' =>
            $indice,

        'id_jogo' =>
            $id_jogo,

        'nome' =>
            $nome,

        'preco' =>
            $preco,

        'img' =>
            $img,

        'quantidade' =>
            $quantidade,

        'subtotal' =>
            $subtotal
    ];
}


/* =========================================================
   PROCESSAR PAGAMENTO
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $forma_pagamento =
        trim(
            $_POST['forma_pagamento'] ?? ''
        );


    /* =====================================================
       VERIFICAR CARRINHO
    ===================================================== */

    if (count($itens) === 0) {

        $erro =
            "Seu carrinho está vazio. Volte ao carrinho e adicione um jogo.";

    }


    /* =====================================================
       VERIFICAR IDS
    ===================================================== */

    if ($erro === "") {

        foreach ($itens as $item) {

            if ((int)$item['id_jogo'] <= 0) {

                $erro =
                    "Não foi possível identificar o jogo \"" .
                    $item['nome'] .
                    "\" no banco de dados.";

                break;
            }
        }
    }


    /* =====================================================
       VALIDAR PAGAMENTO
    ===================================================== */

    if ($erro === "") {

        if (
            $forma_pagamento !== "Cartão" &&
            $forma_pagamento !== "PIX"
        ) {

            $erro =
                "Escolha uma forma de pagamento.";
        }
    }


    /* =====================================================
       VALIDAR CARTÃO
    ===================================================== */

    if (
        $erro === "" &&
        $forma_pagamento === "Cartão"
    ) {

        $nome_cartao =
            trim(
                $_POST['nome_cartao'] ?? ''
            );


        $numero_cartao =
            preg_replace(
                '/\D/',
                '',
                $_POST['numero_cartao'] ?? ''
            );


        $validade =
            trim(
                $_POST['validade'] ?? ''
            );


        $cvv =
            preg_replace(
                '/\D/',
                '',
                $_POST['cvv'] ?? ''
            );


        if ($nome_cartao === '') {

            $erro =
                "Informe o nome do titular do cartão.";

        } elseif (
            strlen($numero_cartao) < 13
        ) {

            $erro =
                "Número do cartão inválido.";

        } elseif (
            strlen($validade) < 5
        ) {

            $erro =
                "Informe a validade do cartão.";

        } elseif (
            strlen($cvv) < 3
        ) {

            $erro =
                "Informe o CVV do cartão.";
        }
    }


    /* =====================================================
       FINALIZAR
    ===================================================== */

    if ($erro === "") {

        try {

            /*
               Começar transação.
            */

            $conn->begin_transaction();


            /* =================================================
               GERAR AUTENTICAÇÃO
            ================================================= */

            $autenticacao =
                "LG-" .
                date("YmdHis") .
                "-" .
                strtoupper(
                    substr(
                        bin2hex(
                            random_bytes(5)
                        ),
                        0,
                        8
                    )
                );


            /* =================================================
               PAGAMENTO
            ================================================= */

            /*
               Verificar se a tabela pagamento possui
               Forma_Pagamento e Autenticacao.
            */

            $sqlPagamento = "
                INSERT INTO pagamento
                (
                    Forma_Pagamento,
                    Autenticacao
                )
                VALUES
                (
                    ?,
                    ?
                )
            ";


            $stmtPagamento =
                $conn->prepare(
                    $sqlPagamento
                );


            if (!$stmtPagamento) {

                throw new Exception(
                    "Erro ao preparar pagamento: " .
                    $conn->error
                );
            }


            $stmtPagamento->bind_param(
                "ss",
                $forma_pagamento,
                $autenticacao
            );


            if (!$stmtPagamento->execute()) {

                throw new Exception(
                    "Erro ao registrar pagamento: " .
                    $stmtPagamento->error
                );
            }


            $id_pagamento =
                $conn->insert_id;


            $stmtPagamento->close();


            /* =================================================
               ADICIONAR JOGOS À BIBLIOTECA
            ================================================= */

            foreach ($itens as $item) {

                $id_jogo =
                    (int)$item['id_jogo'];


                if ($id_jogo <= 0) {

                    throw new Exception(
                        "Não foi possível identificar o jogo: " .
                        $item['nome']
                    );
                }


                /* =============================================
                   VERIFICAR SE JÁ POSSUI
                ============================================= */

                $sqlVerifica = "
                    SELECT ID_Biblioteca
                    FROM biblioteca
                    WHERE
                        ID_usuario = ?
                        AND ID_jogo = ?
                    LIMIT 1
                ";


                $stmtVerifica =
                    $conn->prepare(
                        $sqlVerifica
                    );


                if (!$stmtVerifica) {

                    throw new Exception(
                        "Erro ao verificar biblioteca: " .
                        $conn->error
                    );
                }


                $stmtVerifica->bind_param(
                    "ii",
                    $id_usuario,
                    $id_jogo
                );


                $stmtVerifica->execute();


                $resultado =
                    $stmtVerifica->get_result();


                $jaPossui =
                    $resultado->num_rows > 0;


                $stmtVerifica->close();


                /* =============================================
                   ADICIONAR
                ============================================= */

                if (!$jaPossui) {

                    $sqlBiblioteca = "
                        INSERT INTO biblioteca
                        (
                            ID_usuario,
                            ID_jogo,
                            Data_Aquisicao,
                            Horas_Jogadas
                        )
                        VALUES
                        (
                            ?,
                            ?,
                            CURDATE(),
                            0
                        )
                    ";


                    $stmtBiblioteca =
                        $conn->prepare(
                            $sqlBiblioteca
                        );


                    if (!$stmtBiblioteca) {

                        throw new Exception(
                            "Erro ao preparar biblioteca: " .
                            $conn->error
                        );
                    }


                    $stmtBiblioteca->bind_param(
                        "ii",
                        $id_usuario,
                        $id_jogo
                    );


                    if (!$stmtBiblioteca->execute()) {

                        throw new Exception(
                            "Erro ao adicionar jogo à biblioteca: " .
                            $stmtBiblioteca->error
                        );
                    }


                    $quantidade_adicionados++;


                    $stmtBiblioteca->close();
                }
            }


            /* =================================================
               LIMPAR CARRINHO
            ================================================= */

            $sqlDelete = "
                DELETE FROM carrinho
                WHERE ID_usuario = ?
            ";


            $stmtDelete =
                $conn->prepare(
                    $sqlDelete
                );


            if ($stmtDelete) {

                $stmtDelete->bind_param(
                    "i",
                    $id_usuario
                );

                $stmtDelete->execute();

                $stmtDelete->close();
            }


            /* =================================================
               CONFIRMAR
            ================================================= */

            $conn->commit();


            /* =================================================
               GERAR PIX
            ================================================= */

            if ($forma_pagamento === "PIX") {

                $codigo_pix =
                    "LEGENDS-GAMES-" .
                    $id_pagamento .
                    "-" .
                    number_format(
                        $total,
                        2,
                        '',
                        ''
                    ) .
                    "-" .
                    strtoupper(
                        substr(
                            md5(
                                uniqid(
                                    "pix",
                                    true
                                )
                            ),
                            0,
                            8
                        )
                    );
            }


            /* =================================================
               SALVAR PAGAMENTO
            ================================================= */

            $_SESSION['pagamento'] = [

                'id' =>
                    $id_pagamento,

                'forma' =>
                    $forma_pagamento,

                'autenticacao' =>
                    $autenticacao,

                'total' =>
                    $total,

                'codigo_pix' =>
                    $codigo_pix,

                'quantidade_jogos' =>
                    count($itens)
            ];


            /*
               Agora sim limpar o carrinho da sessão.
            */

            $_SESSION['carrinho'] = [];


            $sucesso = true;


        } catch (Exception $e) {

            try {

                $conn->rollback();

            } catch (Exception $rollback) {

            }


            $erro =
                $e->getMessage();
        }
    }
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
Finalizar compra - Legends Games
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

.topo {

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 20px;

    background: #0d0f13;

    border-bottom: 2px solid gold;

    position: relative;
}

.logo {

    color: gold;

    font-size: 30px;

    font-weight: bold;
}

.voltar {

    position: absolute;

    left: 20px;

    color: gold;

    text-decoration: none;

    background: #222;

    border: 1px solid gold;

    padding: 10px 16px;

    border-radius: 8px;

    font-weight: bold;
}

.voltar:hover {

    background: gold;

    color: black;
}

.container {

    width: 90%;

    max-width: 1100px;

    margin: 40px auto;
}

.titulo {

    text-align: center;

    color: gold;

    font-size: 32px;

    margin-bottom: 30px;
}

.conteudo {

    display: grid;

    grid-template-columns:
        1fr 420px;

    gap: 25px;
}

.box {

    background: #14181f;

    padding: 25px;

    border-radius: 15px;

    border: 1px solid #333;

    box-shadow:
        0 0 20px
        rgba(0,0,0,.3);
}

.box h2 {

    color: gold;

    margin-top: 0;
}


/* PRODUTOS */

.produto {

    padding: 18px 0;

    border-bottom: 1px solid #333;
}

.produto h3 {

    margin: 0 0 8px;

    color: white;
}

.produto p {

    margin: 6px 0;

    color: #aaa;
}

.preco {

    color: gold;

    font-weight: bold;

    font-size: 18px;
}

.total {

    display: flex;

    justify-content: space-between;

    margin-top: 25px;

    padding-top: 20px;

    border-top: 2px solid gold;

    font-size: 25px;

    font-weight: bold;
}

.total span:last-child {

    color: gold;
}


/* FORMULÁRIO */

label {

    display: block;

    margin-top: 15px;

    margin-bottom: 7px;

    color: #ddd;
}

select,
input {

    width: 100%;

    padding: 13px;

    border-radius: 8px;

    border: 1px solid #444;

    background: #252a32;

    color: white;

    font-size: 15px;

    outline: none;
}

select:focus,
input:focus {

    border-color: gold;
}

.linha {

    display: flex;

    gap: 10px;
}

.linha div {

    flex: 1;
}


/* PIX */

#pix {

    display: none;

    margin-top: 20px;

    padding: 20px;

    background: #0d0f13;

    border-radius: 12px;

    text-align: center;

    border: 1px solid #333;
}

#qrCode {

    width: 220px;

    height: 220px;

    background: white;

    padding: 8px;

    border-radius: 10px;

    display: block;

    margin: 20px auto;
}

.codigo-pix {

    background: #000;

    color: #00ff88;

    padding: 15px;

    border-radius: 8px;

    word-break: break-all;

    font-size: 13px;

    margin-top: 15px;
}

.copiar {

    margin-top: 12px;

    padding: 10px 15px;

    border: 1px solid gold;

    background: #222;

    color: gold;

    border-radius: 8px;

    cursor: pointer;
}

.copiar:hover {

    background: gold;

    color: black;
}


/* BOTÃO */

.botao {

    width: 100%;

    margin-top: 25px;

    padding: 16px;

    border: none;

    border-radius: 10px;

    background:
        linear-gradient(
            90deg,
            gold,
            #ffcc00
        );

    color: black;

    font-size: 18px;

    font-weight: bold;

    cursor: pointer;
}

.botao:hover {

    background: white;
}


/* ERRO */

.erro {

    background: #8b1e24;

    padding: 15px;

    border-radius: 8px;

    margin-bottom: 20px;

    text-align: center;

    font-weight: bold;
}


/* SUCESSO */

.confirmacao {

    max-width: 700px;

    margin: 60px auto;

    background: #14181f;

    border: 1px solid #00ff88;

    border-radius: 20px;

    padding: 40px;

    text-align: center;

    box-shadow:
        0 0 30px
        rgba(0,255,136,.15);
}

.confirmacao h1 {

    color: #00ff88;

    margin-top: 0;

    font-size: 32px;
}

.confirmacao p {

    color: #ccc;

    line-height: 1.6;
}

.id {

    color: gold;

    font-size: 23px;

    font-weight: bold;

    margin: 20px;
}

.valor {

    color: gold;

    font-size: 32px;

    font-weight: bold;

    margin: 20px;
}

.autenticacao {

    background: #000;

    color: #00ff88;

    padding: 15px;

    border-radius: 8px;

    word-break: break-all;

    margin: 20px 0;
}

.sucesso-biblioteca {

    background: #10251b;

    color: #00ff88;

    padding: 15px;

    border-radius: 10px;

    margin-top: 20px;

    font-weight: bold;
}

.botao-loja {

    display: inline-block;

    margin-top: 25px;

    padding: 14px 25px;

    background: gold;

    color: black;

    text-decoration: none;

    border-radius: 8px;

    font-weight: bold;
}

.botao-loja:hover {

    background: white;
}


/* RESPONSIVO */

@media(max-width: 800px) {

    .conteudo {

        grid-template-columns: 1fr;
    }
}

@media(max-width: 500px) {

    .linha {

        flex-direction: column;

        gap: 0;
    }

    .confirmacao {

        padding: 25px;
    }

    .logo {

        font-size: 23px;
    }

    .voltar {

        left: 10px;

        padding: 8px 10px;

        font-size: 12px;
    }
}

</style>

</head>


<body>


<header class="topo">

    <!-- VOLTAR FUNCIONANDO -->

    <a
        href="carrinho.php"
        class="voltar"
    >
        ⬅ Voltar
    </a>


    <div class="logo">

        🎮 Legends Games

    </div>

</header>


<div class="container">


<?php if ($sucesso): ?>


    <div class="confirmacao">

        <h1>
            ✅ Compra finalizada!
        </h1>


        <p>
            Seu pagamento foi registrado
            com sucesso.
        </p>


        <div class="id">

            Pagamento nº
            <?= (int)$id_pagamento ?>

        </div>


        <p>
            Forma de pagamento:
        </p>


        <strong>

            <?= htmlspecialchars(
                $forma_pagamento
            ) ?>

        </strong>


        <div class="valor">

            R$

            <?= number_format(
                $total,
                2,
                ',',
                '.'
            ) ?>

        </div>


        <p>
            Código de autenticação:
        </p>


        <div class="autenticacao">

            <?= htmlspecialchars(
                $autenticacao
            ) ?>

        </div>


        <div class="sucesso-biblioteca">

            🎮

            <?= (int)$quantidade_adicionados ?>

            jogo(s) adicionado(s)
            à sua biblioteca!

        </div>


        <?php if (
            $forma_pagamento === "PIX"
        ): ?>

            <h2
                style="
                    color:#00ff88;
                    margin-top:30px;
                "
            >
                📱 PIX
            </h2>


            <p>
                Escaneie o QR Code abaixo.
            </p>


            <img
                id="qrConfirmacao"
                alt="QR Code PIX"
                style="
                    width:220px;
                    height:220px;
                    background:white;
                    padding:8px;
                    border-radius:10px;
                    margin:20px auto;
                    display:block;
                "
            >


            <div class="autenticacao">

                <?= htmlspecialchars(
                    $codigo_pix
                ) ?>

            </div>


            <button
                type="button"
                class="copiar"
                onclick="copiarConfirmacao()"
            >
                📋 Copiar código PIX
            </button>

        <?php endif; ?>


        <br>


        <a
            href="biblioteca.php"
            class="botao-loja"
        >
            🎮 Ir para minha biblioteca
        </a>


        <br>


        <a
            href="tela_inicial.php"
            class="botao-loja"
        >
            🏠 Voltar para a loja
        </a>

    </div>


<?php else: ?>


    <h1 class="titulo">

        💳 Finalizar pagamento

    </h1>


    <?php if ($erro !== ""): ?>

        <div class="erro">

            ⚠️

            <?= htmlspecialchars(
                $erro
            ) ?>

        </div>

    <?php endif; ?>


    <div class="conteudo">


        <!-- =================================================
             CARRINHO
        ================================================== -->

        <div class="box">

            <h2>
                🛒 Seu carrinho
            </h2>


            <?php if (count($itens) > 0): ?>


                <?php foreach ($itens as $item): ?>


                    <div class="produto">


                        <h3>

                            <?= htmlspecialchars(
                                $item['nome']
                            ) ?>

                        </h3>


                        <p>

                            Quantidade:

                            <?= (int)$item['quantidade'] ?>

                        </p>


                        <p>

                            Preço unitário:

                            R$

                            <?= number_format(
                                $item['preco'],
                                2,
                                ',',
                                '.'
                            ) ?>

                        </p>


                        <div class="preco">

                            Subtotal:

                            R$

                            <?= number_format(
                                $item['subtotal'],
                                2,
                                ',',
                                '.'
                            ) ?>

                        </div>


                    </div>


                <?php endforeach; ?>


            <?php else: ?>


                <div class="produto">

                    <h3>
                        🛒 Carrinho vazio
                    </h3>

                    <p>
                        Não existem jogos no carrinho.
                    </p>


                    <a
                        href="tela_inicial.php"
                        style="
                            display:inline-block;
                            margin-top:15px;
                            background:gold;
                            color:black;
                            padding:12px 18px;
                            border-radius:8px;
                            text-decoration:none;
                            font-weight:bold;
                        "
                    >
                        🎮 Ver jogos
                    </a>

                </div>


            <?php endif; ?>


            <div class="total">

                <span>
                    Total
                </span>

                <span>

                    R$

                    <?= number_format(
                        $total,
                        2,
                        ',',
                        '.'
                    ) ?>

                </span>

            </div>

        </div>


        <!-- =================================================
             PAGAMENTO
        ================================================== -->

        <div class="box">

            <h2>
                💳 Pagamento
            </h2>


            <?php if (count($itens) > 0): ?>


                <form
                    method="POST"
                    action="tela_pagento.php"
                    onsubmit="return validarFormulario()"
                >


                    <label>
                        Forma de pagamento
                    </label>


                    <select
                        name="forma_pagamento"
                        id="metodo"
                        onchange="mudarMetodo()"
                        required
                    >

                        <option value="Cartão">
                            💳 Cartão
                        </option>

                        <option value="PIX">
                            📱 PIX
                        </option>

                    </select>


                    <!-- CARTÃO -->

                    <div id="cartao">


                        <label>
                            Nome no cartão
                        </label>


                        <input
                            type="text"
                            name="nome_cartao"
                            id="nome_cartao"
                            placeholder="Nome do titular"
                        >


                        <label>
                            Número do cartão
                        </label>


                        <input
                            type="text"
                            name="numero_cartao"
                            id="numero_cartao"
                            placeholder="0000 0000 0000 0000"
                            maxlength="19"
                        >


                        <div class="linha">


                            <div>

                                <label>
                                    Validade
                                </label>


                                <input
                                    type="text"
                                    name="validade"
                                    id="validade"
                                    placeholder="MM/AA"
                                    maxlength="5"
                                >

                            </div>


                            <div>

                                <label>
                                    CVV
                                </label>


                                <input
                                    type="password"
                                    name="cvv"
                                    id="cvv"
                                    placeholder="123"
                                    maxlength="4"
                                >

                            </div>

                        </div>


                    </div>


                    <!-- PIX -->

                    <div id="pix">

                        <h3>
                            📱 Pagamento via PIX
                        </h3>


                        <p>
                            O código PIX será gerado
                            ao finalizar a compra.
                        </p>


                        <img
                            id="qrCode"
                            alt="QR Code PIX"
                        >


                        <div
                            class="codigo-pix"
                            id="codigoPix"
                        >
                        </div>


                        <button
                            type="button"
                            class="copiar"
                            onclick="copiarPix()"
                        >
                            📋 Copiar código
                        </button>

                    </div>


                    <button
                        type="submit"
                        class="botao"
                    >
                        ✅ Finalizar compra
                    </button>


                </form>


            <?php endif; ?>


        </div>


    </div>


<?php endif; ?>


</div>


<script>

/* =========================================================
   ALTERAR MÉTODO
========================================================= */

function mudarMetodo() {

    const metodo =
        document.getElementById("metodo");

    if (!metodo) {
        return;
    }


    const cartao =
        document.getElementById("cartao");


    const pix =
        document.getElementById("pix");


    if (metodo.value === "PIX") {

        cartao.style.display = "none";

        pix.style.display = "block";

        gerarPreviewPix();

    } else {

        cartao.style.display = "block";

        pix.style.display = "none";
    }
}


/* =========================================================
   PIX
========================================================= */

function gerarPreviewPix() {

    const valor =
        <?= json_encode(
            number_format(
                $total,
                2,
                '.',
                ''
            )
        ) ?>;


    const codigo =
        "LEGENDS-GAMES-PIX-" +
        valor +
        "-" +
        Math.floor(
            Math.random() * 99999999
        );


    const campo =
        document.getElementById(
            "codigoPix"
        );


    if (campo) {

        campo.innerText =
            codigo;
    }


    const qr =
        "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" +
        encodeURIComponent(codigo);


    const imagem =
        document.getElementById(
            "qrCode"
        );


    if (imagem) {

        imagem.src = qr;
    }
}


/* =========================================================
   COPIAR PIX
========================================================= */

function copiarPix() {

    const elemento =
        document.getElementById(
            "codigoPix"
        );


    if (!elemento) {
        return;
    }


    const codigo =
        elemento.innerText;


    if (!codigo) {

        alert(
            "Código PIX ainda não foi gerado."
        );

        return;
    }


    navigator.clipboard
        .writeText(codigo)
        .then(function() {

            alert(
                "Código PIX copiado!"
            );

        })
        .catch(function() {

            alert(
                "Não foi possível copiar o código."
            );
        });
}


/* =========================================================
   VALIDAR FORMULÁRIO
========================================================= */

function validarFormulario() {

    const metodo =
        document.getElementById(
            "metodo"
        );


    if (!metodo) {
        return false;
    }


    if (metodo.value === "PIX") {

        return true;
    }


    const nome =
        document.getElementById(
            "nome_cartao"
        ).value.trim();


    const numero =
        document.getElementById(
            "numero_cartao"
        ).value.replace(
            /\D/g,
            ""
        );


    const validade =
        document.getElementById(
            "validade"
        ).value.trim();


    const cvv =
        document.getElementById(
            "cvv"
        ).value.replace(
            /\D/g,
            ""
        );


    if (nome === "") {

        alert(
            "Informe o nome do titular."
        );

        return false;
    }


    if (numero.length < 13) {

        alert(
            "Número do cartão inválido."
        );

        return false;
    }


    if (validade.length < 5) {

        alert(
            "Informe a validade do cartão."
        );

        return false;
    }


    if (cvv.length < 3) {

        alert(
            "Informe o CVV."
        );

        return false;
    }


    return true;
}


/* =========================================================
   MÁSCARA CARTÃO
========================================================= */

const numeroCartao =
    document.getElementById(
        "numero_cartao"
    );


if (numeroCartao) {

    numeroCartao.addEventListener(
        "input",
        function() {

            let valor =
                this.value.replace(
                    /\D/g,
                    ""
                );


            valor =
                valor.substring(
                    0,
                    16
                );


            valor =
                valor.replace(
                    /(\d{4})(?=\d)/g,
                    "$1 "
                );


            this.value =
                valor;
        }
    );
}


/* =========================================================
   MÁSCARA VALIDADE
========================================================= */

const validade =
    document.getElementById(
        "validade"
    );


if (validade) {

    validade.addEventListener(
        "input",
        function() {

            let valor =
                this.value.replace(
                    /\D/g,
                    ""
                );


            valor =
                valor.substring(
                    0,
                    4
                );


            if (valor.length >= 3) {

                valor =
                    valor.substring(
                        0,
                        2
                    )
                    +
                    "/"
                    +
                    valor.substring(
                        2
                    );
            }


            this.value =
                valor;
        }
    );
}


/* =========================================================
   QR CODE DA CONFIRMAÇÃO
========================================================= */

<?php if (
    $sucesso &&
    $forma_pagamento === "PIX"
): ?>


const codigoConfirmacao =
    <?= json_encode(
        $codigo_pix
    ) ?>;


const qrConfirmacao =
    "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" +
    encodeURIComponent(
        codigoConfirmacao
    );


const imagemConfirmacao =
    document.getElementById(
        "qrConfirmacao"
    );


if (imagemConfirmacao) {

    imagemConfirmacao.src =
        qrConfirmacao;
}


function copiarConfirmacao() {

    navigator.clipboard
        .writeText(
            codigoConfirmacao
        )
        .then(function() {

            alert(
                "Código PIX copiado!"
            );

        })
        .catch(function() {

            alert(
                "Não foi possível copiar."
            );
        });
}


<?php endif; ?>

</script>


</body>

</html>

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
   CRIAR TABELA DE COMENTÁRIOS SE NÃO EXISTIR
========================================================= */

$sqlCriarComentarios = "

CREATE TABLE IF NOT EXISTS comentarios (

    ID_Comentario INT(11) NOT NULL AUTO_INCREMENT,

    ID_usuario INT(11) DEFAULT NULL,

    ID_jogo INT(11) NOT NULL,

    Comentario VARCHAR(1000) NOT NULL,

    Data_Comentario DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (ID_Comentario),

    KEY ID_usuario (ID_usuario),

    KEY ID_jogo (ID_jogo),

    CONSTRAINT comentarios_ibfk_usuario
        FOREIGN KEY (ID_usuario)
        REFERENCES usuario (ID_usuario)
        ON DELETE SET NULL,

    CONSTRAINT comentarios_ibfk_jogo
        FOREIGN KEY (ID_jogo)
        REFERENCES jogo (ID_jogo)
        ON DELETE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci
";

if (!$conn->query($sqlCriarComentarios)) {

    die(
        "Erro ao criar tabela comentarios: " .
        $conn->error
    );

}


/* =========================================================
   JOGOS
========================================================= */

$jogos = [

    "Minecraft" => [
        "99.90",
        "4.8",
        "Sobrevivência",
        [
            "imagens/minecraft_jogo.jpg",
            "imagens/minecraft2.jpg",
            "imagens/minecraft3.jpg"
        ],
        "https://www.youtube.com/embed/MmB9b5njVbA"
    ],

    "Red Dead Redemption 2" => [
        "282.90",
        "5.0",
        "Ação / Aventura",
        [
            "imagens/red_dead_redemption_2.jpg",
            "imagens/red_dead2.jpg",
            "imagens/red_dead3.jpg"
        ],
        "https://www.youtube.com/embed/eaW0tYpxyp0"
    ],

    "Elden Ring" => [
        "320.99",
        "4.5",
        "RPG / Ação",
        [
            "imagens/Elden_Ring.jpg",
            "imagens/elden_ring2.jpg",
            "imagens/elden_ring3.jpg"
        ],
        "https://www.youtube.com/embed/E3Huy2cdih0"
    ],

    "Cyberpunk 2077" => [
        "349.90",
        "4.8",
        "RPG / Ação",
        [
            "imagens/cyberpunk.jpg",
            "imagens/cyberpunk2.jpg",
            "imagens/cyberpunk3.jpg"
        ],
        "https://www.youtube.com/embed/8X2kIfS6fb8"
    ],

    "Call of Duty: Warzone" => [
        "349.90",
        "4.8",
        "FPS",
        [
            "imagens/Call_of_Duty.jpg",
            "imagens/cod2.jpg",
            "imagens/cod3.jpg"
        ],
        "https://www.youtube.com/embed/0E44DClsX5Q"
    ],

    "Call of Duty: Warzone 3" => [
        "349.90",
        "4.8",
        "FPS",
        [
            "imagens/Call_of_duty_3.png",
            "imagens/cod_warzone3_2.jpg",
            "imagens/cod_warzone3_3.jpg"
        ],
        "https://www.youtube.com/embed/0E44DClsX5Q"
    ],

    "DOOM Eternal" => [
        "219.90",
        "4.9",
        "FPS / Ação",
        [
            "imagens/Dom.jpg",
            "imagens/doom2.jpg",
            "imagens/doom3.jpg"
        ],
        "https://www.youtube.com/embed/_UuktemkCFI"
    ],

    "God Of War: Ragnarock" => [
        "159.90",
        "4.5",
        "Ação / Aventura",
        [
            "imagens/god_of_war.jpg",
            "imagens/god2.jpg",
            "imagens/god3.jpg"
        ],
        "https://www.youtube.com/embed/EE-4GvjKcfs"
    ],

    "Hades" => [
        "139.90",
        "4.6",
        "Roguelike",
        [
            "imagens/Hades.jpg",
            "imagens/hades2.jpg",
            "imagens/hades3.jpg"
        ],
        "https://www.youtube.com/embed/Bz8l935Bv0Y"
    ],

    "Horizon Forbidden West" => [
        "199.90",
        "4.8",
        "Ação / Aventura",
        [
            "imagens/horizon.jpg",
            "imagens/horizon2.jpg",
            "imagens/horizon3.jpg"
        ],
        "https://www.youtube.com/embed/Lq594XmpPBg"
    ],

    "No Man's Sky" => [
        "119.90",
        "4.4",
        "Exploração",
        [
            "imagens/não_sei.jpg",
            "imagens/no_mans_sky2.jpg",
            "imagens/no_mans_sky3.jpg"
        ],
        "https://www.youtube.com/embed/nLtmEjqzg7M"
    ],

    "Resident Evil 4" => [
        "179.90",
        "4.7",
        "Terror / Ação",
        [
            "imagens/Resident_Evil_4.jpg",
            "imagens/resident4_2.jpg",
            "imagens/resident4_3.jpg"
        ],
        "https://www.youtube.com/embed/Id2EaldBaWw"
    ]

];


/* =========================================================
   PEGAR NOME DO JOGO
========================================================= */

$nome = $_GET['nome'] ?? "Minecraft";

if (!isset($jogos[$nome])) {
    $nome = "Minecraft";
}

$jogo = $jogos[$nome];

$preco     = $jogo[0];
$nota      = $jogo[1];
$categoria = $jogo[2];
$imagens   = $jogo[3];
$video     = $jogo[4];


/* =========================================================
   VERIFICAR SE A CATEGORIA EXISTE
========================================================= */

$idCategoria = 1;

$stmtCategoria = $conn->prepare("
    SELECT ID_Categoria
    FROM categoria
    WHERE Nome_Categoria = ?
    LIMIT 1
");

if ($stmtCategoria) {

    $categoriaBanco = $categoria;

    $stmtCategoria->bind_param(
        "s",
        $categoriaBanco
    );

    $stmtCategoria->execute();

    $resultadoCategoria =
        $stmtCategoria->get_result();

    if ($linhaCategoria =
        $resultadoCategoria->fetch_assoc()
    ) {

        $idCategoria =
            (int)$linhaCategoria['ID_Categoria'];

    }

    $stmtCategoria->close();
}


/* =========================================================
   PEGAR ID DO JOGO NO BANCO
========================================================= */

$idJogo = 0;

$stmtJogo = $conn->prepare("
    SELECT ID_jogo
    FROM jogo
    WHERE Nome = ?
    LIMIT 1
");

if (!$stmtJogo) {

    die(
        "Erro ao procurar o jogo: " .
        $conn->error
    );

}

$stmtJogo->bind_param(
    "s",
    $nome
);

$stmtJogo->execute();

$resultadoJogo =
    $stmtJogo->get_result();

if ($linhaJogo =
    $resultadoJogo->fetch_assoc()
) {

    $idJogo =
        (int)$linhaJogo['ID_jogo'];

}

$stmtJogo->close();


/* =========================================================
   SE O JOGO NÃO EXISTIR, CADASTRAR AUTOMATICAMENTE
========================================================= */

if ($idJogo <= 0) {

    $descricao =
        "Jogo disponível na Legends Games.";

    $capa =
        $imagens[0];

    $precoBanco =
        (float)$preco;

    $classificacao =
        0;

    $videoBanco =
        $video;


    $stmtNovoJogo = $conn->prepare("
        INSERT INTO jogo
        (
            Nome,
            Descricao,
            Video_Demonstrativo,
            Capa,
            Preco_Unitario,
            Classificacao_Etaria,
            ID_Categoria
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmtNovoJogo) {

        die(
            "Erro ao preparar cadastro do jogo: " .
            $conn->error
        );

    }


    $stmtNovoJogo->bind_param(
        "ssssdii",
        $nome,
        $descricao,
        $videoBanco,
        $capa,
        $precoBanco,
        $classificacao,
        $idCategoria
    );

    if (!$stmtNovoJogo->execute()) {

        die(
            "Erro ao cadastrar jogo: " .
            $stmtNovoJogo->error
        );

    }

    $idJogo =
        (int)$stmtNovoJogo->insert_id;

    $stmtNovoJogo->close();

}


/* =========================================================
   PEGAR USUÁRIO LOGADO
========================================================= */

$idUsuario = null;

if (
    isset($_SESSION['ID_usuario'])
) {

    $idUsuario =
        (int)$_SESSION['ID_usuario'];

}
elseif (
    isset($_SESSION['id_usuario'])
) {

    $idUsuario =
        (int)$_SESSION['id_usuario'];

}
elseif (
    isset($_SESSION['usuario_id'])
) {

    $idUsuario =
        (int)$_SESSION['usuario_id'];

}


/* =========================================================
   ENVIAR COMENTÁRIO
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    &&
    isset($_POST['enviar_comentario'])
) {

    $comentario =
        trim(
            $_POST['comentario'] ?? ''
        );


    if ($idJogo <= 0) {

        die("
            <div style='
                background:#05070a;
                color:white;
                min-height:100vh;
                padding:40px;
                font-family:Arial;
            '>

                <h2 style='color:#ff4444'>
                    Erro
                </h2>

                <p>
                    O jogo
                    <b>" .
                    htmlspecialchars($nome) .
                    "</b>
                    não foi encontrado no banco de dados.
                </p>

            </div>
        ");

    }


    if ($comentario === '') {

        header(
            "Location: tela_de_jogo.php?nome=" .
            urlencode($nome)
        );

        exit;

    }


    /* =====================================================
       LIMITE DE CARACTERES
    ===================================================== */

    if (
        mb_strlen($comentario) > 1000
    ) {

        $comentario =
            mb_substr(
                $comentario,
                0,
                1000
            );

    }


    /* =====================================================
       SALVAR COMENTÁRIO
    ===================================================== */

    if (
        $idUsuario !== null
        &&
        $idUsuario > 0
    ) {

        $stmtComentario =
            $conn->prepare("
                INSERT INTO comentarios
                (
                    ID_usuario,
                    ID_jogo,
                    Comentario,
                    Data_Comentario
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    NOW()
                )
            ");

        if (!$stmtComentario) {

            die(
                "Erro ao preparar comentário: " .
                $conn->error
            );

        }

        $stmtComentario->bind_param(
            "iis",
            $idUsuario,
            $idJogo,
            $comentario
        );

    }
    else {

        $stmtComentario =
            $conn->prepare("
                INSERT INTO comentarios
                (
                    ID_usuario,
                    ID_jogo,
                    Comentario,
                    Data_Comentario
                )
                VALUES
                (
                    NULL,
                    ?,
                    ?,
                    NOW()
                )
            ");

        if (!$stmtComentario) {

            die(
                "Erro ao preparar comentário: " .
                $conn->error
            );

        }

        $stmtComentario->bind_param(
            "is",
            $idJogo,
            $comentario
        );

    }


    if (
        !$stmtComentario->execute()
    ) {

        die(
            "Erro ao salvar comentário: " .
            $stmtComentario->error
        );

    }

    $stmtComentario->close();


    header(
        "Location: tela_de_jogo.php?nome=" .
        urlencode($nome)
    );

    exit;

}


/* =========================================================
   BUSCAR TODOS OS COMENTÁRIOS DO JOGO
========================================================= */

$comentarios = [];

if ($idJogo > 0) {

    $stmtComentarios =
        $conn->prepare("
            SELECT
                c.ID_Comentario,
                c.ID_usuario,
                c.ID_jogo,
                c.Comentario,
                c.Data_Comentario,

                COALESCE(
                    u.Nome_Exibicao,
                    u.Nome,
                    'Usuário'
                ) AS NomeUsuario

            FROM comentarios c

            LEFT JOIN usuario u
                ON u.ID_usuario = c.ID_usuario

            WHERE c.ID_jogo = ?

            ORDER BY
                c.ID_Comentario DESC
        ");


    if (!$stmtComentarios) {

        die(
            "Erro ao buscar comentários: " .
            $conn->error
        );

    }


    $stmtComentarios->bind_param(
        "i",
        $idJogo
    );


    $stmtComentarios->execute();


    $resultadoComentarios =
        $stmtComentarios->get_result();


    while (
        $linha =
        $resultadoComentarios->fetch_assoc()
    ) {

        $comentarios[] =
            $linha;

    }


    $stmtComentarios->close();

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
<?= htmlspecialchars($nome) ?>
- Legends Games
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

}

header {

    padding: 20px;

    background: #0d0f13;

    border-bottom: 2px solid gold;

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

    color: gold;

    text-decoration: none;

    background: #222;

    padding: 10px 15px;

    border-radius: 8px;

}

.voltar:hover {

    background: gold;

    color: black;

}

h1 {

    width: 90%;

    margin: 35px auto;

    color: gold;

    font-size: 40px;

}

.container {

    width: 90%;

    margin: auto;

    display: grid;

    grid-template-columns: 2fr 1fr;

    gap: 30px;

}

.galeria {

    position: relative;

    width: 100%;

    height: 500px;

    background: #000;

    border-radius: 15px;

    overflow: hidden;

}

.slide {

    display: none;

    width: 100%;

    height: 100%;

}

.slide.ativo {

    display: block;

}

.slide img {

    width: 100%;

    height: 100%;

    object-fit: cover;

}

.slide iframe {

    width: 100%;

    height: 100%;

    border: 0;

}

.seta {

    position: absolute;

    top: 50%;

    transform: translateY(-50%);

    width: 50px;

    height: 70px;

    background: rgba(0,0,0,.75);

    color: gold;

    border: 0;

    font-size: 35px;

    cursor: pointer;

    z-index: 10;

}

.seta:hover {

    background: gold;

    color: black;

}

.esquerda {
    left: 10px;
}

.direita {
    right: 10px;
}

.contador {

    position: absolute;

    bottom: 15px;

    left: 50%;

    transform: translateX(-50%);

    background: rgba(0,0,0,.8);

    color: gold;

    padding: 8px 15px;

    border-radius: 20px;

    z-index: 10;

}

.miniaturas {

    display: flex;

    gap: 10px;

    margin-top: 15px;

    overflow-x: auto;

}

.miniatura {

    width: 120px;

    height: 70px;

    object-fit: cover;

    border-radius: 8px;

    cursor: pointer;

    opacity: .6;

    border: 2px solid transparent;

    flex-shrink: 0;

}

.miniatura:hover,
.miniatura.selecionada {

    opacity: 1;

    border-color: gold;

}

.box {

    background: #14181f;

    padding: 25px;

    border-radius: 15px;

    height: max-content;

}

.preco {

    color: gold;

    font-size: 30px;

    font-weight: bold;

}

.estrelas {

    color: gold;

    font-size: 22px;

    margin: 10px 0;

}

.comprar {

    display: block;

    text-align: center;

    background: gold;

    color: black;

    padding: 14px;

    margin: 20px 0;

    border-radius: 10px;

    text-decoration: none;

    font-weight: bold;

}

.comprar:hover {

    background: white;

}

.ver-carrinho {

    display: block;

    text-align: center;

    background: #222;

    color: gold;

    border: 1px solid gold;

    padding: 12px;

    border-radius: 10px;

    text-decoration: none;

    font-weight: bold;

}

.ver-carrinho:hover {

    background: gold;

    color: black;

}


/* =========================================================
   COMENTÁRIOS
========================================================= */

.comentarios {

    width: 90%;

    margin: 30px auto 50px;

    background: #14181f;

    padding: 25px;

    border-radius: 15px;

}

.comentarios h2 {

    color: gold;

    margin-top: 0;

}

.total-comentarios {

    color: #aaa;

    margin-bottom: 20px;

}


/* =========================================================
   CAIXA PARA ESCREVER COMENTÁRIO
========================================================= */

textarea {

    width: 100%;

    height: 60px;

    min-height: 60px;

    max-height: 300px;

    background: #222;

    color: white;

    border: 1px solid #555;

    padding: 12px;

    border-radius: 8px;

    resize: none;

    overflow-y: hidden;

    font-family: Arial, sans-serif;

    font-size: 15px;

    line-height: 1.5;

}

textarea:focus {

    outline: none;

    border-color: gold;

}


/* =========================================================
   BOTÃO ENVIAR
========================================================= */

.enviar {

    margin-top: 10px;

    padding: 12px 25px;

    background: gold;

    color: black;

    border: 0;

    border-radius: 8px;

    font-weight: bold;

    cursor: pointer;

    font-size: 15px;

}

.enviar:hover {

    background: white;

}


/* =========================================================
   LISTA DE COMENTÁRIOS
========================================================= */

.lista-comentarios {

    margin-top: 30px;

}

.comentario {

    background: #222;

    padding: 18px;

    margin-top: 15px;

    border-radius: 10px;

    border-left: 4px solid gold;

}

.comentario-cabecalho {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 10px;

    margin-bottom: 10px;

}

.usuario-comentario {

    color: gold;

    font-weight: bold;

}

.data-comentario {

    color: #888;

    font-size: 12px;

}


/* =========================================================
   TEXTO DOS COMENTÁRIOS JÁ PUBLICADOS
========================================================= */

textarea {

    width: 100%;

    height: 60px;

    min-height: 60px;

    max-height: 300px;

    background: #222;

    color: white;

    border: 1px solid #555;

    padding: 12px;

    border-radius: 8px;

    resize: none;

    overflow-y: hidden;

    font-family: Arial, sans-serif;

    font-size: 15px;

    line-height: 1.5;

}

textarea:focus {

    outline: none;

    border-color: gold;

}


/* =========================================================
   BARRA DE ROLAGEM DO COMENTÁRIO
========================================================= */

.texto-comentario::-webkit-scrollbar {

    width: 6px;

}

.texto-comentario::-webkit-scrollbar-track {

    background: #111;

    border-radius: 10px;

}

.texto-comentario::-webkit-scrollbar-thumb {

    background: gold;

    border-radius: 10px;

}


/* =========================================================
   SEM COMENTÁRIOS
========================================================= */

.sem-comentarios {

    text-align: center;

    color: #888;

    padding: 30px;

}


/* =========================================================
   RESPONSIVO
========================================================= */

@media(max-width: 800px) {

    .container {

        grid-template-columns: 1fr;

    }

    .galeria {

        height: 350px;

    }

    h1 {

        font-size: 30px;

    }

    .comentario-cabecalho {

        flex-direction: column;

        align-items: flex-start;

    }

}

</style>

</head>


<body>


<header>

    <div class="logo">
        Legends_Games
    </div>

    <a
        href="tela_inicial.php"
        class="voltar"
    >
        ⬅ Voltar
    </a>

</header>


<h1>
    <?= htmlspecialchars($nome) ?>
</h1>


<div class="container">


    <!-- =====================================================
         GALERIA
    ====================================================== -->

    <div>

        <div class="galeria">

            <?php foreach (
                $imagens as $i => $imagem
            ): ?>

                <div
                    class="slide
                    <?= $i === 0 ? 'ativo' : '' ?>"
                >

                    <img
                        src="<?= htmlspecialchars($imagem) ?>"
                        onerror="
                            this.src='https://via.placeholder.com/800x500/111111/FFD700?text=Imagem+nao+encontrada'
                        "
                    >

                </div>

            <?php endforeach; ?>


            <div class="slide">

                <iframe
                    src="<?= htmlspecialchars($video) ?>"
                    allowfullscreen
                ></iframe>

            </div>


            <button
                type="button"
                class="seta esquerda"
                onclick="anterior()"
            >
                ❮
            </button>


            <button
                type="button"
                class="seta direita"
                onclick="proximo()"
            >
                ❯
            </button>


            <div
                class="contador"
                id="contador"
            >
                1 / <?= count($imagens) + 1 ?>
            </div>

        </div>


        <div class="miniaturas">

            <?php foreach (
                $imagens as $i => $imagem
            ): ?>

                <img
                    class="miniatura
                    <?= $i === 0 ? 'selecionada' : '' ?>"
                    src="<?= htmlspecialchars($imagem) ?>"
                    onclick="irPara(<?= $i ?>)"
                >

            <?php endforeach; ?>


            <div
                class="miniatura"
                onclick="
                    irPara(
                        <?= count($imagens) ?>
                    )
                "
                style="
                    background:#111;
                    color:gold;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    font-weight:bold;
                "
            >
                ▶ VÍDEO
            </div>

        </div>

    </div>


    <!-- =====================================================
         INFORMAÇÕES
    ====================================================== -->

    <div class="box">

        <div class="preco">

            R$

            <?= number_format(
                $preco,
                2,
                ',',
                '.'
            ) ?>

        </div>


        <div class="estrelas">
            ★★★★★
        </div>


        <p>

            ⭐ Nota:

            <?= htmlspecialchars($nota) ?>

        </p>


        <a
            class="comprar"
            href="carrinho.php?adicionar=1&nome=<?= urlencode($nome) ?>&preco=<?= urlencode($preco) ?>&img=<?= urlencode($imagens[0]) ?>"
        >

            🛒 Comprar

        </a>


        <a
            href="carrinho.php"
            class="ver-carrinho"
        >

            🛒 Ver meu carrinho

        </a>


        <p>

            <b>Categoria:</b>

            <?= htmlspecialchars($categoria) ?>

        </p>


        <p>

            Aproveite

            <b>
                <?= htmlspecialchars($nome) ?>
            </b>

            na Legends_Games!

        </p>


        <hr>


        <h3 style="color:gold">
            🎮 Sobre o jogo
        </h3>


        <p>

            Explore o mundo de

            <?= htmlspecialchars($nome) ?>,

            enfrente desafios e aproveite
            uma experiência incrível.

        </p>

    </div>

</div>


<!-- =========================================================
     COMENTÁRIOS
========================================================= -->

<div class="comentarios">

    <h2>

        💬 Comentários sobre
        <?= htmlspecialchars($nome) ?>

    </h2>


    <div class="total-comentarios">

        <?= count($comentarios) ?>

        comentário(s) neste jogo.

    </div>


    <form
        method="POST"
        action="tela_de_jogo.php?nome=<?= urlencode($nome) ?>"
    >

        <textarea
            id="campoComentario"
            name="comentario"
            placeholder="Escreva um comentário sobre este jogo..."
            maxlength="1000"
            required
        ></textarea>


        <button
            type="submit"
            name="enviar_comentario"
            class="enviar"
        >

            💬 Enviar comentário

        </button>

    </form>


    <!-- =====================================================
         LISTA
    ====================================================== -->

    <div class="lista-comentarios">

        <?php if (
            count($comentarios) > 0
        ): ?>


            <?php foreach (
                $comentarios as $comentario
            ): ?>

                <div class="comentario">


                    <div class="comentario-cabecalho">


                        <div class="usuario-comentario">

                            👤

                            <?= htmlspecialchars(
                                $comentario['NomeUsuario'] ??
                                'Usuário',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </div>


                        <div class="data-comentario">

                            <?php

                            if (
                                !empty(
                                    $comentario[
                                        'Data_Comentario'
                                    ]
                                )
                            ) {

                                $data =
                                    strtotime(
                                        $comentario[
                                            'Data_Comentario'
                                        ]
                                    );

                                if (
                                    $data !== false
                                ) {

                                    echo date(
                                        'd/m/Y H:i',
                                        $data
                                    );

                                }

                            }

                            ?>

                        </div>

                    </div>


                    <div class="texto-comentario">

                        <?= htmlspecialchars(
                            $comentario['Comentario'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </div>


                </div>

            <?php endforeach; ?>


        <?php else: ?>


            <div class="sem-comentarios">

                💬 Ainda não existem comentários
                para este jogo.

                <br><br>

                Seja o primeiro a comentar!

            </div>


        <?php endif; ?>

    </div>

</div>


<script>

/* =========================================================
   GALERIA
========================================================= */

let atual = 0;

const slides =
    document.querySelectorAll(".slide");

const miniaturas =
    document.querySelectorAll(".miniatura");

const total =
    slides.length;


/* =========================================================
   MOSTRAR SLIDE
========================================================= */

function mostrar(numero) {

    if (numero < 0) {

        numero = total - 1;

    }

    if (numero >= total) {

        numero = 0;

    }

    atual = numero;


    slides.forEach(
        function(slide) {

            slide.classList.remove(
                "ativo"
            );

        }
    );


    miniaturas.forEach(
        function(mini) {

            mini.classList.remove(
                "selecionada"
            );

        }
    );


    slides[atual]
        .classList
        .add("ativo");


    if (miniaturas[atual]) {

        miniaturas[atual]
            .classList
            .add("selecionada");

    }


    document
        .getElementById("contador")
        .innerText =
            (atual + 1)
            + " / "
            + total;

}


/* =========================================================
   PRÓXIMO
========================================================= */

function proximo() {

    mostrar(atual + 1);

}


/* =========================================================
   ANTERIOR
========================================================= */

function anterior() {

    mostrar(atual - 1);

}


/* =========================================================
   IR PARA
========================================================= */

function irPara(numero) {

    mostrar(numero);

}


/* =========================================================
   TECLADO
========================================================= */

document.addEventListener(
    "keydown",
    function(e) {

        if (
            e.key === "ArrowRight"
        ) {

            proximo();

        }

        if (
            e.key === "ArrowLeft"
        ) {

            anterior();

        }

    }
);


/* =========================================================
   AUMENTAR AUTOMATICAMENTE A CAIXA DE COMENTÁRIO
========================================================= */

const campoComentario =
    document.getElementById(
        "campoComentario"
    );


function ajustarAlturaComentario() {

    /*
       Primeiro diminuímos a altura para
       conseguir calcular o tamanho real
       do texto.
    */

    campoComentario.style.height = "60px";


    /*
       scrollHeight informa quanto espaço
       o texto realmente está ocupando.
    */

    let novaAltura =
        campoComentario.scrollHeight;


    /*
       Limite máximo de 300px.
    */

    if (novaAltura > 300) {

        novaAltura = 300;

        campoComentario.style.overflowY =
            "auto";

    }
    else {

        campoComentario.style.overflowY =
            "hidden";

    }


    campoComentario.style.height =
        novaAltura + "px";

}


/* =========================================================
   AUMENTAR ENQUANTO DIGITA
========================================================= */

campoComentario.addEventListener(
    "input",
    ajustarAlturaComentario
);


/* =========================================================
   AJUSTAR AO CARREGAR A PÁGINA
========================================================= */

ajustarAlturaComentario();

</script>


</body>

</html>

<?php

session_start();

/* =========================
   JOGOS
========================= */

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


/* =========================
   PEGAR NOME DO JOGO
========================= */

$nome = $_GET['nome'] ?? "Minecraft";

if (!isset($jogos[$nome])) {
    $nome = "Minecraft";
}

$jogo = $jogos[$nome];

$preco = $jogo[0];
$nota = $jogo[1];
$categoria = $jogo[2];
$imagens = $jogo[3];
$video = $jogo[4];

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
    <?= htmlspecialchars($nome) ?> - Legends Games
</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;

    background: radial-gradient(
        circle at top,
        #1b2838,
        #05070a
    );

    color: white;
}


/* =========================
   TOPO
========================= */

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


/* =========================
   TITULO
========================= */

h1 {

    width: 90%;

    margin: 35px auto;

    color: gold;

    font-size: 40px;
}


/* =========================
   CONTAINER
========================= */

.container {

    width: 90%;

    margin: auto;

    display: grid;

    grid-template-columns: 2fr 1fr;

    gap: 30px;
}


/* =========================
   GALERIA
========================= */

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


/* =========================
   SETAS
========================= */

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


/* =========================
   CONTADOR
========================= */

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


/* =========================
   MINIATURAS
========================= */

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


/* =========================
   BOX DE INFORMAÇÕES
========================= */

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


/* =========================
   BOTÃO COMPRAR
========================= */

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

    cursor: pointer;
}

.comprar:hover {

    background: white;
}


/* =========================
   BOTÃO CARRINHO
========================= */

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


/* =========================
   COMENTÁRIOS
========================= */

.comentarios {

    width: 90%;

    margin: 30px auto;

    background: #14181f;

    padding: 20px;

    border-radius: 15px;
}

textarea {

    width: 100%;

    height: 80px;

    background: #222;

    color: white;

    border: 1px solid #444;

    padding: 10px;

    border-radius: 8px;

    resize: none;
}

button.enviar {

    margin-top: 10px;

    padding: 10px 20px;

    background: gold;

    border: 0;

    border-radius: 8px;

    font-weight: bold;

    cursor: pointer;
}

.comentario {

    background: #222;

    padding: 10px;

    margin-top: 10px;

    border-radius: 8px;
}

.comentario b {

    color: gold;
}


/* =========================
   RESPONSIVO
========================= */

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


    <!-- =========================
         GALERIA
    ========================= -->

    <div>

        <div class="galeria">


            <?php foreach($imagens as $i => $imagem): ?>

                <div
                    class="slide <?= $i == 0 ? 'ativo' : '' ?>"
                >

                    <img
                        src="<?= htmlspecialchars($imagem) ?>"

                        onerror="
                            this.src='https://via.placeholder.com/800x500/111111/FFD700?text=Imagem+nao+encontrada'
                        "
                    >

                </div>

            <?php endforeach; ?>


            <!-- VÍDEO -->

            <div class="slide">

                <iframe
                    src="<?= htmlspecialchars($video) ?>"
                    allowfullscreen>
                </iframe>

            </div>


            <!-- SETA ESQUERDA -->

            <button
                class="seta esquerda"
                onclick="anterior()"
            >
                ❮
            </button>


            <!-- SETA DIREITA -->

            <button
                class="seta direita"
                onclick="proximo()"
            >
                ❯
            </button>


            <!-- CONTADOR -->

            <div
                class="contador"
                id="contador"
            >
                1 / <?= count($imagens) + 1 ?>
            </div>

        </div>


        <!-- =========================
             MINIATURAS
        ========================= -->

        <div class="miniaturas">


            <?php foreach($imagens as $i => $imagem): ?>

                <img
                    class="miniatura <?= $i == 0 ? 'selecionada' : '' ?>"

                    src="<?= htmlspecialchars($imagem) ?>"

                    onclick="irPara(<?= $i ?>)"
                >

            <?php endforeach; ?>


            <!-- MINIATURA VÍDEO -->

            <div
                class="miniatura"

                onclick="irPara(<?= count($imagens) ?>)"

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


    <!-- =========================
         INFORMAÇÕES
    ========================= -->

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


        <!-- =========================
             COMPRAR
        ========================= -->

        <a
            class="comprar"

            href="carrinho.php?adicionar=1&nome=<?= urlencode($nome) ?>&preco=<?= urlencode($preco) ?>&img=<?= urlencode($imagens[0]) ?>"
        >

            🛒 Comprar

        </a>


        <!-- =========================
             VER CARRINHO
        ========================= -->

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
            <b><?= htmlspecialchars($nome) ?></b>
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


<!-- =========================
     COMENTÁRIOS
========================= -->

<div class="comentarios">


    <h2 style="color:gold">

        💬 Comentários

    </h2>


    <textarea
        id="texto"
        placeholder="Escreva um comentário..."
    ></textarea>


    <br>


    <button
        class="enviar"
        onclick="comentar()"
    >

        Enviar

    </button>


    <div id="lista"></div>


</div>


<script>


/* =========================
   GALERIA
========================= */

let atual = 0;

const slides =
    document.querySelectorAll(".slide");

const miniaturas =
    document.querySelectorAll(".miniatura");

const total =
    slides.length;


function mostrar(numero) {

    if(numero < 0) {

        numero = total - 1;

    }


    if(numero >= total) {

        numero = 0;

    }


    atual = numero;


    slides.forEach(function(slide) {

        slide.classList.remove("ativo");

    });


    miniaturas.forEach(function(mini) {

        mini.classList.remove("selecionada");

    });


    slides[atual].classList.add("ativo");


    if(miniaturas[atual]) {

        miniaturas[atual]
            .classList
            .add("selecionada");

    }


    document
        .getElementById("contador")
        .innerText =
        (atual + 1) + " / " + total;

}


function proximo() {

    mostrar(atual + 1);

}


function anterior() {

    mostrar(atual - 1);

}


function irPara(numero) {

    mostrar(numero);

}


/* =========================
   TECLADO
========================= */

document.addEventListener(
    "keydown",
    function(e) {

        if(e.key === "ArrowRight") {

            proximo();

        }


        if(e.key === "ArrowLeft") {

            anterior();

        }

    }
);


/* =========================
   COMENTÁRIOS
========================= */

function comentar() {

    let texto =
        document
        .getElementById("texto")
        .value
        .trim();


    if(!texto) {

        return;

    }


    let div =
        document.createElement("div");


    div.className =
        "comentario";


    div.innerHTML =
        "<b>Usuário</b><br>" +
        texto;


    document
        .getElementById("lista")
        .prepend(div);


    document
        .getElementById("texto")
        .value = "";

}

</script>


</body>

</html>
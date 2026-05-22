<?php
$jogo = [
    "nome" => $_GET['nome'] ?? "ARK Survival Ascended",
    "preco" => $_GET['preco'] ?? 219.90,
    "img" => $_GET['img'] ?? "ark.jpg"
];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Pagamento</title>

<!-- Remove icone quebrado -->
<link rel="icon" href="data:,">

<style>
body {
    font-family: Arial;
    margin: 0;
    background: radial-gradient(circle at top, #1b2838, #0f141a);
    color: #FFD700;
}

/* TOPO */
.topo {
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    padding: 20px;
    background: rgba(17,17,17,0.95);
}

.logo {
    font-size: 36px;
    font-weight: bold;
    background: linear-gradient(90deg,#FFD700,#FFE066,#FFD700);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    padding: 8px 25px;
    border: 2px solid #FFD700;
    border-radius: 12px;
}

.voltar {
    position: absolute;
    left: 20px;
}

.voltar button {
    padding: 10px;
    border-radius: 10px;
    border: none;
    background: #222;
    color: gold;
    cursor: pointer;
}

/* CONTAINER */
.container {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding-top: 40px;
}

/* BOX */
.box {
    background: #222;
    padding: 30px;
    border-radius: 20px;
    width: 420px;
    box-shadow: 0 0 20px rgba(255,215,0,0.2);
    text-align: center;
}

/* IMAGEM JOGO */
.img-jogo {
    width: 180px;
    border-radius: 10px;
    margin-bottom: 10px;
}

/* PREÇO */
.preco {
    font-size: 32px;
    font-weight: bold;
    background: gold;
    color: black;
    padding: 10px;
    border-radius: 10px;
    margin: 10px 0;
}

/* INPUTS */
input, select {
    width: 100%;
    padding: 10px;
    margin: 6px 0;
    border-radius: 8px;
    border: none;
}

.linha {
    display: flex;
    gap: 10px;
}

.linha input {
    flex: 1;
}

/* BOTÃO */
.pagar {
    width: 100%;
    padding: 14px;
    margin-top: 15px;
    border-radius: 10px;
    border: none;
    background: linear-gradient(90deg, gold, #ffcc00);
    font-weight: bold;
    cursor: pointer;
}

/* PIX */
.pix-box {
    display: none;
    margin-top: 15px;
}

.codigo {
    background: #000;
    padding: 10px;
    border-radius: 8px;
    margin: 10px 0;
}

/* LOADER */
.loader {
    border: 5px solid #333;
    border-top: 5px solid gold;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    margin: 10px auto;
    animation: girar 1s linear infinite;
}

@keyframes girar {
    100% { transform: rotate(360deg); }
}

/* MENSAGENS */
.sucesso {
    display: none;
    color: #00ff88;
    font-size: 20px;
    margin-top: 20px;
}

.erro {
    display: none;
    color: red;
    margin-top: 10px;
}
</style>
</head>

<body>

<header class="topo">
    <div class="voltar">
        <button onclick="history.back()">⬅ Voltar</button>
    </div>
    <div class="logo">Lendas_Games</div>
</header>

<div class="container">
<div class="box">

<img class="img-jogo" src="<?= $jogo['img']; ?>">
<h2><?= $jogo['nome']; ?></h2>

<div class="preco">
R$ <?= number_format($jogo['preco'],2,',','.'); ?>
</div>

<label>Forma de pagamento</label>
<select id="metodo" onchange="mudarMetodo()">
<option value="cartao">Cartão</option>
<option value="pix">PIX</option>
</select>

<!-- CARTÃO -->
<div id="cartao">
<input id="nome" type="text" placeholder="Nome no cartão">
<input id="numero" type="text" placeholder="Número do cartão">

<div class="linha">
<input id="validade" type="text" placeholder="Validade">
<input id="cvv" type="text" placeholder="CVV">
</div>
</div>

<!-- PIX -->
<div id="pix" class="pix-box">
<p><b>Escaneie o QR Code</b></p>

<img id="qrCode" width="220">

<div class="codigo" id="codigoPix"></div>

<div id="statusPix">
<div class="loader"></div>
<p>Aguardando pagamento...</p>
</div>
</div>

<button class="pagar" onclick="finalizar()">Finalizar pagamento</button>

<div id="erro" class="erro">
⚠️ Preencha todos os dados corretamente
</div>

<div id="sucesso" class="sucesso">
✅ Pagamento aprovado! 🎮
</div>

</div>
</div>

<script>

function resetarTela(){
document.getElementById("cartao").style.display = "block";
document.getElementById("pix").style.display = "none";
document.querySelector(".pagar").style.display = "block";
document.querySelector(".logo").style.display = "block";

document.getElementById("statusPix").style.display = "block";
document.getElementById("sucesso").style.display = "none";
document.getElementById("erro").style.display = "none";

document.getElementById("qrCode").src = "";
document.getElementById("codigoPix").innerText = "";
}

function mudarMetodo(){

let metodo = document.getElementById("metodo").value;

resetarTela();

if(metodo === "cartao"){
document.getElementById("cartao").style.display = "block";
}

if(metodo === "pix"){

document.getElementById("cartao").style.display = "none";
document.getElementById("pix").style.display = "block";

document.querySelector(".pagar").style.display = "none";
document.querySelector(".logo").style.display = "none";

let codigo = "PIX-" + Math.floor(Math.random()*999999999);

document.getElementById("codigoPix").innerText = codigo;

let url = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" + codigo;

document.getElementById("qrCode").src = url;

setTimeout(()=>{

document.getElementById("statusPix").style.display = "none";
document.getElementById("sucesso").style.display = "block";

},5000);

}

}

function finalizar(){

let nome = document.getElementById("nome").value;
let numero = document.getElementById("numero").value;
let validade = document.getElementById("validade").value;
let cvv = document.getElementById("cvv").value;

if(nome === "" || numero.length < 8 || validade === "" || cvv.length < 3){

document.getElementById("erro").style.display = "block";
return;

}

document.getElementById("erro").style.display = "none";
document.getElementById("sucesso").style.display = "block";

}

window.onload = resetarTela;

</script>

</body>
</html>
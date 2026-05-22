<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ARK</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;}

body{
    font-family:Arial;
    background:radial-gradient(circle at top,#1b2838,#05070a);
    color:#fff;
}

/* TOPO */
.topo{
    background:#0d0f13;
    padding:25px 50px;
    display:flex;
    justify-content:space-between;
    border-bottom:2px solid gold;
}

.logo{
    color:gold;
    font-size:30px;
}

.voltar{
    background:#1a1f26;
    color:gold;
    padding:10px 15px;
    border-radius:10px;
    cursor:pointer;
}

/* TITULO */
.titulo{
    font-size:50px;
    color:gold;
    margin:40px;
}

/* LAYOUT */
.container{
    width:90%;
    margin:auto;
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:50px;
}

/* VIDEO */
.video iframe{
    width:100%;
    height:420px;
    border-radius:15px;
}

/* IMAGEM PRINCIPAL */
.imagem-principal img{
    width:100%;
    height:400px;
    object-fit:cover;
    border-radius:15px;
    margin-top:20px;
}

/* GALERIA */
.galeria{
    display:flex;
    gap:10px;
    margin-top:15px;
}

.galeria img{
    width:33%;
    height:120px;
    object-fit:cover;
    border-radius:10px;
    cursor:pointer;
    opacity:0.7;
}

.galeria img:hover{
    opacity:1;
    transform:scale(1.05);
}

/* COMENTARIOS */
.comentarios{
    margin-top:40px;
    background:#14181f;
    padding:20px;
    border-radius:15px;
}

textarea{
    width:100%;
    height:80px;
    border-radius:10px;
    padding:10px;
}

.btn{
    margin-top:10px;
    background:gold;
    padding:10px;
    border:none;
    border-radius:10px;
}

.card{
    background:#1f2a36;
    padding:10px;
    margin-top:10px;
    border-radius:10px;
}

.nome{color:gold;}
.tempo{font-size:12px;color:#aaa;}

/* COMUNIDADE */
.comunidade{
    margin-top:40px;
}

.galeria-comunidade{
    display:flex;
    gap:10px;
}

.galeria-comunidade img{
    width:25%;
    height:120px;
    border-radius:10px;
}

/* SIDEBAR */
.sidebar{
    background:#14181f;
    padding:25px;
    border-radius:15px;
}

.preco{
    font-size:28px;
    color:gold;
}

/* AVALIAÇÃO */
.estrelas{
    color:gold;
    font-size:20px;
}

/* BOTÃO */
.botao{
    background:gold;
    padding:12px;
    text-align:center;
    border-radius:10px;
    margin:15px 0;
}

/* RODAPÉ */
.rodape{
    margin-top:60px;
    background:#0d0f13;
    border-top:2px solid gold;
    padding:40px;
}

.rodape-container{
    display:flex;
    justify-content:space-between;
    width:90%;
    margin:auto;
    color:#ccc;
}

.rodape h3{color:gold;}

@media(max-width:900px){
.container{
    grid-template-columns:1fr;
}
}
</style>
</head>

<body>

<div class="topo">
<div class="logo">Lendas_Games</div>
<div class="voltar" onclick="history.back()">⬅ Voltar</div>
</div>

<h1 class="titulo">ARK: Survival Evolved</h1>

<div class="container">

<!-- ESQUERDA -->
<div>

<div class="video">
<iframe src="https://www.youtube.com/embed/5fIAPcVdZO8"></iframe>
</div>

<div class="imagem-principal">
<img id="imgPrincipal" src="ark1.jpg">
</div>

<div class="galeria">
<img src="ark1.jpg" onclick="trocar(this)">
<img src="ark2.jpg" onclick="trocar(this)">
<img src="ark3.jpg" onclick="trocar(this)">
</div>

<div class="comentarios">
<h2 style="color:gold;">Comentários</h2>

<textarea id="txt"></textarea>
<button class="btn" onclick="add()">Enviar</button>

<div id="lista"></div>
</div>

<div class="comunidade">
<h2 style="color:gold;">Comunidade</h2>

<div class="galeria-comunidade">
<img src="com1.jpg">
<img src="com2.jpg">
<img src="com3.jpg">
<img src="com4.jpg">
</div>
</div>

</div>

<!-- DIREITA -->
<div class="sidebar">

<div class="preco">R$ 89,90</div>

<div class="estrelas">★★★★★</div>
<p>4.7 (12.458 avaliações)</p>

<div class="botao">🛒 Comprar</div>

<p><strong>Categoria:</strong> Sobrevivência</p>

<p>
ARK é um jogo de sobrevivência com dinossauros em mundo aberto.
</p>

<h3 style="color:gold;">Requisitos</h3>
<p>RAM: 8GB</p>
<p>CPU: i5</p>
<p>GPU: GTX 670</p>

</div>

</div>

<footer class="rodape">
<div class="rodape-container">

<div>
<h3>Lendas_Games</h3>
<p>Explore novos mundos.</p>
</div>

<div>
<p>Loja</p>
<p>Biblioteca</p>
</div>

<div>
<p>Suporte</p>
<p>© 2026</p>
</div>

</div>
</footer>

<script>
function trocar(img){
document.getElementById("imgPrincipal").src = img.src;
}

function add(){
let t = document.getElementById("txt").value;
if(t=="") return;

let d = document.createElement("div");
d.className="card";
d.innerHTML=`<div class="nome">Usuário</div><div>${t}</div><div class="tempo">agora</div>`;

document.getElementById("lista").prepend(d);
document.getElementById("txt").value="";
}
</script>

</body>
</html>
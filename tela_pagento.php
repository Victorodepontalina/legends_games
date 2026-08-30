<?php
session_start();
require_once 'conexao.php';
$conn->set_charset("utf8mb4");

if (!isset($_SESSION["ID_Usuario"])) {
    header("Location: login.php");
    exit;
}

$id_usuario = (int)$_SESSION["ID_Usuario"];
if (!isset($_SESSION['carrinho'])) $_SESSION['carrinho'] = [];

$itens = [];
$total = 0;
$total_original = 0;
$erro = "";
$sucesso = false;
$forma_pagamento = "";
$autenticacao = "";
$codigo_pix = "";
$id_pagamento = 0;
$quantidade_adicionados = 0;

$eh_admin = false;
$stmtAdmin = $conn->prepare("SELECT Nivel_Acesso FROM usuario WHERE ID_usuario = ? LIMIT 1");
if ($stmtAdmin) {
    $stmtAdmin->bind_param("i", $id_usuario);
    $stmtAdmin->execute();
    $resAdmin = $stmtAdmin->get_result()->fetch_assoc();
    if ($resAdmin && isset($resAdmin['Nivel_Acesso']) && $resAdmin['Nivel_Acesso'] == 1) $eh_admin = true;
    $stmtAdmin->close();
}

foreach ($_SESSION['carrinho'] as $indice => $item) {
    $nome = isset($item['nome']) ? trim($item['nome']) : 'Jogo';
    $preco = isset($item['preco']) ? (float)$item['preco'] : 0;
    $img = isset($item['img']) ? $item['img'] : '';
    $quantidade = isset($item['quantidade']) ? (int)$item['quantidade'] : 1;
    if ($quantidade < 1) $quantidade = 1;

    $id_jogo = 0;
    $sqlJogo = "SELECT * FROM jogo WHERE Nome = ? LIMIT 1";
    $stmtJogo = $conn->prepare($sqlJogo);
    if ($stmtJogo) {
        $stmtJogo->bind_param("s", $nome);
        $stmtJogo->execute();
        $resJogo = $stmtJogo->get_result();
        if ($resJogo->num_rows > 0) {
            $jogo = $resJogo->fetch_assoc();
            $id_jogo = (int)$jogo['ID_jogo'];
            if ($preco <= 0) $preco = (float)($jogo['Preco_Unitario'] ?? $jogo['Preco'] ?? 0);
        }
        $stmtJogo->close();
    }
    
    $subtotal = $preco * $quantidade;
    $total += $subtotal;
    $itens[] = ['indice' => $indice, 'id_jogo' => $id_jogo, 'nome' => $nome, 'preco' => $preco, 'img' => $img, 'quantidade' => $quantidade, 'subtotal' => $subtotal];
}

$total_original = $total;
$desconto_aplicado = 0;
$mensagem_cupom = "";

// APLICAR OU REMOVER CUPOM
if (isset($_POST['aplicar_cupom'])) {
    $codigo = strtoupper(trim($_POST['codigo_cupom']));
    $stmt = $conn->prepare("SELECT Desconto FROM cupom WHERE Codigo = ? AND Ativo = 1");
    if($stmt){
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if($res){
            $_SESSION['cupom'] = ['codigo' => $codigo, 'desconto' => $res['Desconto']];
            $mensagem_cupom = "<div style='color:#00ff88; margin-bottom:10px; font-weight:bold;'>✅ Cupom aplicado com sucesso!</div>";
        } else {
            $mensagem_cupom = "<div style='color:#ff4d4d; margin-bottom:10px; font-weight:bold;'>❌ Cupom inválido ou expirado.</div>";
        }
        $stmt->close();
    }
}
if (isset($_POST['remover_cupom'])) {
    unset($_SESSION['cupom']);
}

// CALCULAR ABATIMENTO
if (isset($_SESSION['cupom']) && !$eh_admin) {
    $desconto_aplicado = $total * ($_SESSION['cupom']['desconto'] / 100);
    $total -= $desconto_aplicado;
}

if ($eh_admin) $total = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_compra'])) {
    if ($eh_admin) $forma_pagamento = "Resgate Admin";
    else $forma_pagamento = trim($_POST['forma_pagamento'] ?? '');

    if (count($itens) === 0) $erro = "Seu carrinho está vazio.";
    
    if ($erro === "" && !$eh_admin) {
        if ($forma_pagamento !== "Cartão" && $forma_pagamento !== "PIX") $erro = "Escolha uma forma de pagamento.";
        elseif ($forma_pagamento === "Cartão") {
            if (empty(trim($_POST['nome_cartao'] ?? ''))) $erro = "Informe o nome do titular do cartão.";
            elseif (strlen(preg_replace('/\D/', '', $_POST['numero_cartao'] ?? '')) < 13) $erro = "Número do cartão inválido.";
        }
    }

    if ($erro === "") {
        try {
            $conn->begin_transaction();
            $autenticacao = "LG-" . date("YmdHis") . "-" . strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
            
            $stmtPagamento = $conn->prepare("INSERT INTO pagamento (Forma_Pagamento, Autenticacao) VALUES (?, ?)");
            $stmtPagamento->bind_param("ss", $forma_pagamento, $autenticacao);
            $stmtPagamento->execute();
            $id_pagamento = $conn->insert_id;
            $stmtPagamento->close();

            foreach ($itens as $item) {
                $id_jogo = (int)$item['id_jogo'];
                $check = $conn->prepare("SELECT ID_Biblioteca FROM biblioteca WHERE ID_usuario = ? AND ID_jogo = ? LIMIT 1");
                $check->bind_param("ii", $id_usuario, $id_jogo);
                $check->execute();
                $jaPossui = $check->get_result()->num_rows > 0;
                $check->close();

                if (!$jaPossui) {
                    $ins = $conn->prepare("INSERT INTO biblioteca (ID_usuario, ID_jogo, Data_Aquisicao, Horas_Jogadas) VALUES (?, ?, CURDATE(), 0)");
                    $ins->bind_param("ii", $id_usuario, $id_jogo);
                    $ins->execute();
                    $quantidade_adicionados++;
                    $ins->close();
                }
            }

            $conn->commit();
            if ($forma_pagamento === "PIX") {
                $codigo_pix = "LEGENDS-GAMES-" . $id_pagamento . "-" . number_format($total, 2, '', '') . "-" . strtoupper(substr(md5(uniqid("pix", true)), 0, 8));
            }

            unset($_SESSION['cupom']);
            $_SESSION['carrinho'] = [];
            $sucesso = true;

        } catch (Exception $e) {
            $conn->rollback();
            $erro = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Finalizar compra - Legends Games</title>
<style>
* { box-sizing: border-box; }
body { margin: 0; font-family: Arial, sans-serif; background: radial-gradient(circle at top, #1b2838, #05070a); color: white; min-height: 100vh; }
.topo { display: flex; justify-content: center; align-items: center; padding: 20px; background: #0d0f13; border-bottom: 2px solid gold; position: relative; }
.logo { color: gold; font-size: 30px; font-weight: bold; }
.voltar { position: absolute; left: 20px; color: gold; text-decoration: none; background: #222; border: 1px solid gold; padding: 10px 16px; border-radius: 8px; font-weight: bold; }
.voltar:hover { background: gold; color: black; }
.container { width: 90%; max-width: 1100px; margin: 40px auto; }
.titulo { text-align: center; color: gold; font-size: 32px; margin-bottom: 30px; }
.conteudo { display: grid; grid-template-columns: 1fr 420px; gap: 25px; align-items: start;}
.box { background: #14181f; padding: 25px; border-radius: 15px; border: 1px solid #333; box-shadow: 0 0 20px rgba(0,0,0,.3); margin-bottom: 20px;}
.box h2 { color: gold; margin-top: 0; border-bottom: 1px solid #333; padding-bottom: 10px;}
.produto { padding: 18px 0; border-bottom: 1px dashed #333; }
.produto h3 { margin: 0 0 8px; color: white; }
.preco { color: gold; font-weight: bold; font-size: 18px; }
.total { display: flex; justify-content: space-between; align-items: center; margin-top: 25px; padding-top: 20px; font-size: 25px; font-weight: bold; }
.total span:last-child { color: gold; }
label { display: block; margin-top: 15px; margin-bottom: 7px; color: #ddd; }
select, input { width: 100%; padding: 13px; border-radius: 8px; border: 1px solid #444; background: #252a32; color: white; font-size: 15px; outline: none; }
select:focus, input:focus { border-color: gold; }
.linha { display: flex; gap: 10px; }
.linha div { flex: 1; }
#pix { display: none; margin-top: 20px; padding: 20px; background: #0d0f13; border-radius: 12px; text-align: center; border: 1px solid #333; }
#qrCode { width: 220px; height: 220px; background: white; padding: 8px; border-radius: 10px; display: block; margin: 20px auto; }
.codigo-pix { background: #000; color: #00ff88; padding: 15px; border-radius: 8px; word-break: break-all; font-size: 13px; margin-top: 15px; }
.botao { width: 100%; margin-top: 25px; padding: 16px; border: none; border-radius: 10px; background: linear-gradient(90deg, gold, #ffcc00); color: black; font-size: 18px; font-weight: bold; cursor: pointer; }
.botao:hover { background: white; }
.erro { background: #8b1e24; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: bold; }
.confirmacao { max-width: 700px; margin: 60px auto; background: #14181f; border: 1px solid #00ff88; border-radius: 20px; padding: 40px; text-align: center; }
.confirmacao h1 { color: #00ff88; margin-top: 0; font-size: 32px; }
@media(max-width: 800px) { .conteudo { grid-template-columns: 1fr; } }
</style>
</head>
<body>

<header class="topo">
    <a href="carrinho.php" class="voltar">⬅ Voltar</a>
    <div class="logo">🎮 Legends Games</div>
</header>

<div class="container">
<?php if ($sucesso): ?>
    <div class="confirmacao">
        <h1>✅ Compra finalizada!</h1>
        <p>Seu pagamento foi registrado com sucesso.</p>
        <p style="color: gold; font-size: 23px; font-weight: bold;">Pagamento nº <?= (int)$id_pagamento ?></p>
        <p>Forma de pagamento: <strong><?= htmlspecialchars($forma_pagamento) ?></strong></p>
        <p style="color: gold; font-size: 32px; font-weight: bold;">R$ <?= number_format($total, 2, ',', '.') ?></p>
        
        <?php if ($forma_pagamento === "PIX"): ?>
            <h2 style="color:#00ff88; margin-top:30px;">📱 PIX</h2>
            <p>Escaneie o QR Code abaixo.</p>
            <img id="qrConfirmacao" alt="QR Code PIX" style="width:220px; height:220px; background:white; padding:8px; border-radius:10px; margin:20px auto; display:block;">
            <div class="codigo-pix"><?= htmlspecialchars($codigo_pix) ?></div>
            <button type="button" class="botao" style="width:auto; padding:10px 20px; margin-top:15px;" onclick="copiarConfirmacao()">📋 Copiar PIX</button>
        <?php endif; ?>

        <br><br>
        <a href="biblioteca.php" class="botao" style="display:inline-block; width:auto; text-decoration:none;">🎮 Ir para biblioteca</a>
    </div>

<?php else: ?>
    <h1 class="titulo">💳 Finalizar pagamento</h1>
    <?php if ($erro !== ""): ?> <div class="erro">⚠️ <?= htmlspecialchars($erro) ?></div> <?php endif; ?>

    <div class="conteudo">
        <div>
            <div class="box">
                <h2>🛒 Seu carrinho</h2>
                <?php if (count($itens) > 0): ?>
                    <?php foreach ($itens as $item): ?>
                        <div class="produto">
                            <h3><?= htmlspecialchars($item['nome']) ?></h3>
                            <p style="color:#aaa; margin:5px 0;">Preço unitário: R$ <?= number_format($item['preco'], 2, ',', '.') ?></p>
                            <div class="preco">Subtotal: R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Carrinho vazio.</p>
                <?php endif; ?>

                <div class="total">
                    <span>Total</span>
                    <span>
                        <?php if ($eh_admin): ?>
                            <span style="text-decoration: line-through; color: #888; font-size: 16px; margin-right: 10px;">R$ <?= number_format($total_original, 2, ',', '.') ?></span>
                            <span style="color: #00ff88;">Grátis</span>
                        <?php elseif (isset($_SESSION['cupom'])): ?>
                            <span style="text-decoration: line-through; color: #888; font-size: 16px; margin-right: 10px;">R$ <?= number_format($total_original, 2, ',', '.') ?></span>
                            R$ <?= number_format($total, 2, ',', '.') ?>
                        <?php else: ?>
                            R$ <?= number_format($total, 2, ',', '.') ?>
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <!-- CAMPO DE CUPOM DE DESCONTO -->
            <?php if (!$eh_admin && count($itens) > 0): ?>
            <div class="box">
                <h2>🏷️ Cupom de Desconto</h2>
                <?= $mensagem_cupom ?>
                <?php if(isset($_SESSION['cupom'])): ?>
                    <div style="background: #111; padding: 15px; border-radius: 8px; border: 1px solid #00ff88; display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #00ff88; font-weight: bold;">✅ <?= htmlspecialchars($_SESSION['cupom']['codigo']) ?> (<?= $_SESSION['cupom']['desconto'] ?>% OFF)</span>
                        <form method="POST" style="margin:0;">
                            <button type="submit" name="remover_cupom" style="background: #8b1e24; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-weight:bold;">Remover</button>
                        </form>
                    </div>
                <?php else: ?>
                    <form method="POST" style="display: flex; gap: 10px;">
                        <input type="text" name="codigo_cupom" placeholder="Ex: NINJA20" required style="margin:0; flex:1;">
                        <button type="submit" name="aplicar_cupom" style="background: gold; color: black; border: none; padding: 12px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; margin:0; width:auto;">Aplicar</button>
                    </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="box">
            <h2>💳 Pagamento</h2>
            <?php if (count($itens) > 0): ?>
                <form method="POST" action="tela_pagento.php">
                    <input type="hidden" name="finalizar_compra" value="1">
                    <?php if ($eh_admin): ?>
                        <div style="text-align: center; padding: 20px 0;">
                            <p style="color: #00ff88; font-size: 18px; font-weight: bold;">✨ Privilégio de Administrador Ativado</p>
                            <button type="submit" class="botao" style="background: #00ff88; color: black;">🎁 Resgatar Gratuitamente</button>
                        </div>
                    <?php else: ?>
                        <label>Forma de pagamento</label>
                        <select name="forma_pagamento" id="metodo" onchange="mudarMetodo()" required>
                            <option value="Cartão">💳 Cartão</option>
                            <option value="PIX">📱 PIX</option>
                        </select>

                        <div id="cartao">
                            <label>Nome no cartão</label>
                            <input type="text" name="nome_cartao" placeholder="Nome do titular">
                            <label>Número do cartão</label>
                            <input type="text" name="numero_cartao" id="numero_cartao" placeholder="0000 0000 0000 0000" maxlength="19">
                            <div class="linha">
                                <div><label>Validade</label><input type="text" name="validade" id="validade" placeholder="MM/AA" maxlength="5"></div>
                                <div><label>CVV</label><input type="password" name="cvv" placeholder="123" maxlength="4"></div>
                            </div>
                        </div>

                        <div id="pix">
                            <h3>📱 Pagamento via PIX</h3>
                            <img id="qrCode" alt="QR Code PIX">
                            <div class="codigo-pix" id="codigoPix"></div>
                        </div>
                        <button type="submit" class="botao">✅ Finalizar compra</button>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
</div>

<script>
function mudarMetodo() {
    const metodo = document.getElementById("metodo");
    if (!metodo) return;
    document.getElementById("cartao").style.display = (metodo.value === "PIX") ? "none" : "block";
    document.getElementById("pix").style.display = (metodo.value === "PIX") ? "block" : "none";
    if(metodo.value === "PIX") gerarPreviewPix();
}

function gerarPreviewPix() {
    const valor = <?= json_encode(number_format($total, 2, '.', '')) ?>;
    const codigo = "LEGENDS-GAMES-PIX-" + valor + "-" + Math.floor(Math.random() * 99999999);
    document.getElementById("codigoPix").innerText = codigo;
    document.getElementById("qrCode").src = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" + encodeURIComponent(codigo);
}

document.getElementById("numero_cartao")?.addEventListener("input", function() {
    this.value = this.value.replace(/\D/g, "").substring(0, 16).replace(/(\d{4})(?=\d)/g, "$1 ");
});
document.getElementById("validade")?.addEventListener("input", function() {
    let v = this.value.replace(/\D/g, "").substring(0, 4);
    this.value = v.length >= 3 ? v.substring(0, 2) + "/" + v.substring(2) : v;
});

<?php if ($sucesso && $forma_pagamento === "PIX"): ?>
const codigoConfirmacao = <?= json_encode($codigo_pix) ?>;
document.getElementById("qrConfirmacao").src = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" + encodeURIComponent(codigoConfirmacao);
function copiarConfirmacao() { navigator.clipboard.writeText(codigoConfirmacao).then(() => alert("Copiado!")); }
<?php endif; ?>
</script>
</body>
</html>
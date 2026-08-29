<?php
session_start();

// Puxa a conexão centralizada usando MySQLi
require_once 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['ID_Usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = (int)$_SESSION['ID_Usuario'];

// Busca os jogos que o usuário comprou
$sql = "SELECT b.Data_Aquisicao, b.Horas_Jogadas, j.ID_jogo, j.Nome, j.Capa 
        FROM biblioteca b
        INNER JOIN jogo j ON b.ID_jogo = j.ID_jogo
        WHERE b.ID_usuario = ?
        ORDER BY b.ID_Biblioteca DESC";

$stmt = $conexao->prepare($sql);

if (!$stmt) {
    die("Erro na consulta: " . $conexao->error);
}

$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

$meus_jogos = [];
if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $meus_jogos[] = $row;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Minha Biblioteca - Legends Games</title>
<style>
* { box-sizing: border-box; }
body { margin: 0; font-family: Arial, sans-serif; background: radial-gradient(circle at top, #1b2838, #05070a); color: white; min-height: 100vh; }
header { padding: 20px; background: #0d0f13; border-bottom: 2px solid gold; display: flex; justify-content: space-between; align-items: center; }
.logo { color: gold; font-size: 28px; font-weight: bold; }
.voltar { color: gold; text-decoration: none; background: #222; padding: 10px 15px; border-radius: 8px; font-weight: bold; }
.voltar:hover { background: gold; color: black; }
.container { width: 90%; max-width: 1200px; margin: 40px auto; }
h1 { color: gold; text-align: center; margin-bottom: 40px; font-size: 36px; }
.grid-biblioteca { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 30px; }
.card-jogo { background: #14181f; border-radius: 15px; overflow: hidden; border: 1px solid #333; transition: 0.3s; }
.card-jogo:hover { transform: translateY(-5px); border-color: gold; box-shadow: 0 5px 15px rgba(255, 215, 0, 0.2); }
.card-jogo img { width: 100%; height: 150px; object-fit: cover; }
.info-jogo { padding: 20px; }
.info-jogo h3 { margin: 0 0 10px 0; color: white; font-size: 18px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.estatisticas { font-size: 13px; color: #aaa; margin-bottom: 15px; line-height: 1.6; }
.estatisticas span { color: gold; font-weight: bold; }
.btn-jogar { display: block; width: 100%; padding: 12px; background: gold; color: black; text-align: center; text-decoration: none; border-radius: 8px; font-weight: bold; transition: 0.3s; }
.btn-jogar:hover { background: white; }
.vazio { text-align: center; padding: 50px; background: #14181f; border-radius: 15px; border: 1px dashed #333; grid-column: 1 / -1; }
.vazio a { display: inline-block; margin-top: 20px; padding: 12px 25px; background: gold; color: black; text-decoration: none; border-radius: 8px; font-weight: bold; }
</style>
</head>
<body>

<header>
    <div class="logo">Legends_Games</div>
    <a href="tela_inicial.php" class="voltar">⬅ Voltar à Loja</a>
</header>

<div class="container">
    <h1>📚 Minha Biblioteca</h1>

    <div class="grid-biblioteca">
        <?php if (count($meus_jogos) > 0): ?>
            <?php foreach ($meus_jogos as $jogo): ?>
                <div class="card-jogo">
                    <img src="<?= htmlspecialchars($jogo['Capa']) ?>" onerror="this.src='https://via.placeholder.com/250x150/111111/FFD700?text=Capa'">
                    <div class="info-jogo">
                        <h3><?= htmlspecialchars($jogo['Nome']) ?></h3>
                        <div class="estatisticas">
                            Adquirido em: <span><?= date('d/m/Y', strtotime($jogo['Data_Aquisicao'])) ?></span><br>
                            Tempo de jogo: <span><?= (int)$jogo['Horas_Jogadas'] ?>h</span>
                        </div>
                        <a href="tela_de_jogo.php?nome=<?= urlencode($jogo['Nome']) ?>" class="btn-jogar">🎮 Instalar / Jogar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="vazio">
                <h2 style="color: white; margin-top: 0;">Sua biblioteca está vazia!</h2>
                <p style="color: #aaa;">Você ainda não comprou nenhum jogo.</p>
                <a href="tela_inicial.php">Explorar a Loja</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
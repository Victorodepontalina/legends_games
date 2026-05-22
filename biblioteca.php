<?php
session_start();

$usuario_foto = "https://cdn-icons-png.flaticon.com/512/149/149071.png";

$host = "localhost";
$db   = "legends_games_1";
$user = "root";
$pass = "";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}

// Pega automaticamente um usuário existente
$stmtUser = $pdo->query("SELECT ID_usuario FROM usuario LIMIT 1");
$id_usuario = $stmtUser->fetchColumn();

// Se não existir nenhum usuário
if (!$id_usuario) {
    die("Nenhum usuário cadastrado.");
}

// Lista de jogos de exemplo
$exemplo_jogos = [
    ['Nome' => 'Cyberpunk 2077', 'Capa' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/1091500/header.jpg'],
    ['Nome' => 'GTA V', 'Capa' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/271590/header.jpg'],
    ['Nome' => 'Red Dead Redemption 2', 'Capa' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/1174180/header.jpg'],
    ['Nome' => 'The Witcher 3', 'Capa' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/292030/header.jpg'],
    ['Nome' => 'Elden Ring', 'Capa' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/1245620/header.jpg']
];

// Adiciona jogos à biblioteca do usuário
foreach ($exemplo_jogos as $jogo) {

    $stmtCheck = $pdo->prepare("
        SELECT COUNT(*) 
        FROM biblioteca b
        JOIN jogo j ON b.ID_jogo = j.ID_jogo
        WHERE b.ID_usuario = :usuario
        AND j.Nome = :nome
    ");

    $stmtCheck->execute([
        'usuario' => $id_usuario,
        'nome' => $jogo['Nome']
    ]);

    if ($stmtCheck->fetchColumn() == 0) {

        // Verifica se jogo já existe
        $stmtJogo = $pdo->prepare("SELECT ID_jogo FROM jogo WHERE Nome = :nome");
        $stmtJogo->execute(['nome' => $jogo['Nome']]);
        $id_jogo = $stmtJogo->fetchColumn();

        // Se não existe, insere corretamente
        if (!$id_jogo) {

            $stmtInsertJogo = $pdo->prepare("
                INSERT INTO jogo 
                (Nome, Descrição, Video_Demostrativo, Capa, Preço_Unitario, Classificação_Etaria, ID_Categoria)
                VALUES (:nome, '', '', :capa, 0, 0, 1)
            ");

            $stmtInsertJogo->execute([
                'nome' => $jogo['Nome'],
                'capa' => $jogo['Capa']
            ]);

            $id_jogo = $pdo->lastInsertId();
        }

        // Inserir na biblioteca
        $stmtInsert = $pdo->prepare("
            INSERT INTO biblioteca
            (ID_usuario, ID_jogo, Data_Aquisicao, Horas_Jogadas)
            VALUES (:usuario, :jogo, CURDATE(), 0)
        ");

        $stmtInsert->execute([
            'usuario' => $id_usuario,
            'jogo' => $id_jogo
        ]);
    }
}

// Buscar biblioteca
$sql = "
    SELECT b.ID_Biblioteca, j.Nome, j.Capa
    FROM biblioteca b
    JOIN jogo j ON b.ID_jogo = j.ID_jogo
    WHERE b.ID_usuario = :usuario
    AND j.Capa IS NOT NULL
    AND j.Capa <> ''
    ORDER BY j.Nome
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['usuario' => $id_usuario]);
$biblioteca = $stmt->fetchAll();

// Filtro de busca
$busca = $_GET['busca'] ?? '';

if ($busca != '') {
    $biblioteca = array_filter($biblioteca, function($jogo) use ($busca) {
        return stripos($jogo['Nome'], $busca) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Minha Biblioteca</title>

<style>
body{
    background: linear-gradient(135deg, #000000 0%, #111111 40%, #ffd700 80%);
    color:#ffd700;
    font-family:Segoe UI;
    margin:0;
    min-height:100vh;
}

header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    background:rgba(0,0,0,0.8);
    padding:15px;
    border-bottom:2px solid #ffd700;
}

header a{
    color:#ffd700;
    text-decoration:none;
    font-size:28px;
    font-weight:bold;
}

header h1{
    position:absolute;
    left:50%;
    transform:translateX(-50%);
    font-size:32px;
}

.foto{
    width:40px;
    border-radius:50%;
    border:2px solid #ffd700;
}

main{
    padding:20px;
}

input{
    padding:10px;
    border-radius:20px;
    border:none;
    width:250px;
    background:rgba(0,0,0,0.7);
    color:#ffd700;
}

.grid{
    margin-top:20px;
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
    gap:20px;
}

.card{
    background:rgba(0,0,0,0.85);
    padding:10px;
    border-radius:10px;
    border:2px solid #ffd700;
}

.card img{
    width:100%;
    height:120px;
    object-fit:cover;
}

.title{
    text-align:center;
    margin-top:8px;
}

.empty{
    text-align:center;
    margin-top:40px;
}
</style>
</head>

<body>

<header>
    <a href="tela_inicial.php">&#8592;</a>
    <h1>Legends Games</h1>
    <img src="<?= $usuario_foto ?>" class="foto">
</header>

<main>

<form>
<input type="search" name="busca" placeholder="Buscar jogo..." value="<?= htmlspecialchars($busca) ?>">
</form>

<?php if(count($biblioteca) == 0): ?>
    <p class="empty">Nenhum jogo encontrado</p>
<?php else: ?>
    <div class="grid">
        <?php foreach($biblioteca as $jogo): ?>
            <div class="card">
                <img src="<?= $jogo['Capa'] ?>">
                <div class="title"><?= htmlspecialchars($jogo['Nome']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</main>
</body>
</html>
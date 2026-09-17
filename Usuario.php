<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db = "legends_games_1";

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit();
}

$ID_Usuario = $_SESSION['ID_Usuario'];

// Garante que as colunas necessárias existam no banco para não dar erro
$conn->query("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS Foto_Perfil VARCHAR(255)");
$conn->query("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS Horas_Jogadas INT DEFAULT 0");

// ========================================================
// LÓGICA DE UPLOAD DE FOTO
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['nova_foto'])) {
    $diretorio = "uploads/";
    
    if (!is_dir($diretorio)) {
        mkdir($diretorio, 0777, true);
    }

    $nomeArquivo = basename($_FILES["nova_foto"]["name"]);
    $caminhoCompleto = $diretorio . uniqid() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "", $nomeArquivo);

    $check = @getimagesize($_FILES["nova_foto"]["tmp_name"]);
    if($check !== false) {
        if (move_uploaded_file($_FILES["nova_foto"]["tmp_name"], $caminhoCompleto)) {
            $up = $conn->prepare("UPDATE usuario SET Foto_Perfil = ? WHERE ID_usuario = ?");
            $up->bind_param("si", $caminhoCompleto, $ID_Usuario);
            $up->execute();
        } else {
            $erro_upload = "Erro ao salvar a imagem na pasta. Verifique as permissões do XAMPP.";
        }
    } else {
        $erro_upload = "O arquivo selecionado não é uma imagem válida.";
    }
    header("Location: Usuario.php");
    exit;
}

// ========================================================
// BUSCA DE DADOS DO USUÁRIO
// ========================================================
$sql = "SELECT * FROM usuario WHERE ID_Usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ID_Usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $usuario = $resultado->fetch_assoc();
} else {
    die("Usuário não encontrado.");
}

$nomeUsuario = $usuario['Nome'] ?? $usuario['nome'] ?? 'Usuário';
$emailUsuario = $usuario['Email'] ?? $usuario['email'] ?? 'Não informado';
$foto_banco = $usuario['Foto_Perfil'] ?? $usuario['Foto'] ?? $usuario['foto'] ?? '';
$fotoUsuario = !empty($foto_banco) ? $foto_banco : 'https://via.placeholder.com/130/222222/FFD700?text=Avatar';
$horas_jogadas = $usuario['Horas_Jogadas'] ?? 0;
$nivel_acesso = $usuario['Nivel_Acesso'] ?? 0;

// ========================================================
// ESTATÍSTICAS DINÂMICAS DO BANCO
// ========================================================

// 1. Total de Jogos na Biblioteca
$stmtBib = $conn->prepare("SELECT COUNT(*) AS total FROM biblioteca WHERE ID_usuario = ?");
$stmtBib->bind_param("i", $ID_Usuario);
$stmtBib->execute();
$jogos = $stmtBib->get_result()->fetch_assoc()['total'] ?? 0;
$stmtBib->close();

// 2. Total de Favoritos
$stmtFav = $conn->prepare("SELECT COUNT(*) AS total FROM favoritos WHERE ID_usuario = ?");
$stmtFav->bind_param("i", $ID_Usuario);
$stmtFav->execute();
$favoritos = $stmtFav->get_result()->fetch_assoc()['total'] ?? 0;
$stmtFav->close();

// ========================================================
// SISTEMA DE PATENTES (GAMIFICAÇÃO)
// ========================================================
if ($nivel_acesso == 1) {
    // É o Dono/Administrador
    $nome_patente = "⚡ ADMIN SUPREMO";
    $cor_fundo = "linear-gradient(135deg, #ff0000, #800000)";
    $cor_texto = "#fff";
} else {
    // Sistema Automático Baseado em Jogos Comprados
    if ($jogos < 2) {
        $nome_patente = "🥉 NOVATO";
        $cor_fundo = "linear-gradient(135deg, #a6a6a6, #595959)";
        $cor_texto = "#fff";
    } elseif ($jogos < 5) {
        $nome_patente = "🥈 EXPLORADOR";
        $cor_fundo = "linear-gradient(135deg, #00cc44, #006622)";
        $cor_texto = "#fff";
    } elseif ($jogos < 10) {
        $nome_patente = "🔮 COLECIONADOR ELITE";
        $cor_fundo = "linear-gradient(135deg, #9933ff, #4d0099)";
        $cor_texto = "#fff";
    } else {
        $nome_patente = "🏆 LENDA DOURADA";
        $cor_fundo = "linear-gradient(135deg, #FFD700, #ffa500)";
        $cor_texto = "#000";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Minha Conta - Legends Games</title>

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: radial-gradient(circle at top, #1b2838, #0a0d12); color: #fff; min-height: 100vh; }
.topo { display: flex; justify-content: space-between; align-items: center; padding: 15px 40px; background: rgba(13, 15, 19, 0.95); border-bottom: 2px solid #FFD700; backdrop-filter: blur(10px); }
.logo { font-size: 26px; font-weight: 800; color: #FFD700; letter-spacing: 1px; }
.usuario-topo { display: flex; align-items: center; gap: 15px; }
.usuario-topo img { width: 42px; height: 42px; border-radius: 50%; border: 2px solid #FFD700; object-fit: cover; }
.usuario-topo span { font-weight: 600; color: #eee; }
.logout { background: #FFD700; color: #0d0f13; border: none; padding: 8px 18px; border-radius: 8px; cursor: pointer; font-weight: bold; text-decoration: none; transition: 0.3s; }
.logout:hover { background: #e6c200; transform: translateY(-2px); }
.container { display: flex; max-width: 1300px; margin: 30px auto; gap: 30px; padding: 0 20px; }
.menu { width: 240px; background: rgba(20, 24, 31, 0.8); padding: 20px 10px; border-radius: 16px; border: 1px solid rgba(255, 215, 0, 0.1); height: fit-content; }
.menu ul { list-style: none; }
.menu li a { display: flex; align-items: center; gap: 12px; padding: 12px 18px; color: #aaa; text-decoration: none; font-weight: 600; border-radius: 10px; transition: all 0.3s ease; margin-bottom: 8px; }
.menu li.ativo a, .menu li a:hover { background: rgba(255, 215, 0, 0.15); color: #FFD700; border-left: 4px solid #FFD700; }
.conteudo { flex: 1; }
.perfil { background: rgba(20, 24, 31, 0.9); border-radius: 20px; padding: 35px; display: flex; align-items: center; gap: 30px; border: 1px solid rgba(255, 215, 0, 0.2); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5); margin-bottom: 30px; }
.perfil-img-container { position: relative; }
.perfil img { width: 130px; height: 130px; border-radius: 50%; border: 3px solid #FFD700; object-fit: cover; box-shadow: 0 0 20px rgba(255, 215, 0, 0.2); }
.info h1 { font-size: 32px; color: #fff; margin-bottom: 5px; }
.info p { color: #88a0b5; font-size: 15px; margin-bottom: 12px; }
.badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
.stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
.card { background: rgba(20, 24, 31, 0.7); padding: 25px; border-radius: 16px; border: 1px solid rgba(255, 255, 255, 0.05); text-align: center; transition: transform 0.3s ease, border-color 0.3s ease; }
.card:hover { transform: translateY(-5px); border-color: rgba(255, 215, 0, 0.4); }
.card h2 { font-size: 38px; color: #FFD700; font-weight: 800; }
.card p { color: #9bb0c1; margin-top: 5px; font-size: 15px; text-transform: uppercase; font-weight: bold; }
.acoes { display: flex; gap: 15px; flex-wrap: wrap; }
.btn { padding: 12px 24px; border: none; border-radius: 10px; cursor: pointer; font-weight: bold; font-size: 14px; transition: all 0.3s ease; text-decoration: none; display: inline-block; }
.btn-editar { background: #FFD700; color: #000; display: inline-flex; align-items: center; justify-content: center;}
.btn-editar:hover { background: #e6c200; box-shadow: 0 0 15px rgba(255, 215, 0, 0.4); }
.btn-senha { background: transparent; color: #FFD700; border: 2px solid #FFD700; }
.btn-senha:hover { background: rgba(255, 215, 0, 0.1); }
.btn-sair-conta { background: #ff4d4d; color: #fff; border: 2px solid #ff4d4d; }
.btn-sair-conta:hover { background: #cc0000; border-color: #cc0000; box-shadow: 0 0 15px rgba(255, 77, 77, 0.4); }
@media (max-width: 850px) { .container { flex-direction: column; } .menu { width: 100%; } .perfil { flex-direction: column; text-align: center; } }
</style>
</head>

<body>

<header class="topo">
    <div class="logo">Legends_Games</div>
    <div class="usuario-topo">
        <img src="<?= htmlspecialchars($fotoUsuario); ?>" alt="Avatar" onerror="this.src='https://via.placeholder.com/42/222222/FFD700?text=Avatar'">
        <span><?= htmlspecialchars($nomeUsuario); ?></span>
        <a href="tela_inicial.php" class="logout">Voltar à Loja</a>
    </div>
</header>

<div class="container">

<aside class="menu">
    <ul>
        <li class="ativo"><a href="#">👤 Minha Conta</a></li>
        <li><a href="biblioteca.php">🎮 Biblioteca</a></li>
        <li><a href="configuração.php">⚙️ Configurações</a></li>
        <li><a href="carrinho.php">🛒 Carrinho</a></li>
    </ul>
</aside>

<main class="conteudo">

<?php if (isset($erro_upload)): ?>
    <div style="background: #ff4d4d; padding: 15px; border-radius: 10px; margin-bottom: 20px; color: white; font-weight: bold;">
        <?= $erro_upload ?>
    </div>
<?php endif; ?>

<section class="perfil">
    <div class="perfil-img-container">
        <img src="<?= htmlspecialchars($fotoUsuario); ?>" alt="Foto Perfil" onerror="this.src='https://via.placeholder.com/130/222222/FFD700?text=Avatar'">
    </div>
    <div class="info">
        <h1><?= htmlspecialchars($nomeUsuario); ?></h1>
        <p>Email: <?= htmlspecialchars($emailUsuario); ?></p>
        
        <!-- PATENTE DINÂMICA -->
        <span class="badge" style="background: <?= $cor_fundo ?>; color: <?= $cor_texto ?>;">
            <?= $nome_patente ?>
        </span>
    </div>
</section>

<!-- ESTATÍSTICAS DINÂMICAS DO BANCO DE DADOS -->
<section class="stats">
    <div class="card">
        <h2><?= $jogos; ?></h2>
        <p>Jogos na Biblioteca</p>
    </div>
    <div class="card">
        <h2><?= $horas_jogadas; ?>h</h2>
        <p>Horas Jogadas</p>
    </div>
    <div class="card">
        <h2><?= $favoritos; ?></h2>
        <p>Favoritos</p>
    </div>
</section>

<div class="acoes">

    <form action="" method="POST" enctype="multipart/form-data" style="display: inline-block; margin: 0;">
        <input type="file" name="nova_foto" id="inputFoto" accept="image/*" style="display: none;" onchange="this.form.submit()">
        <label for="inputFoto" class="btn btn-editar" style="margin: 0; cursor: pointer;">
            📷 Trocar Foto
        </label>
    </form>

    <button class="btn btn-senha">Alterar Senha</button>
    <a href="login.php" class="btn btn-sair-conta">Sair da Conta</a>

</div>

</main>
</div>

</body>
</html>
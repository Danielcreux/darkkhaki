<?php
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if (!file_exists('databases')) {
    mkdir('databases', 0777, true);
}


// Configuración SQLite
$db = new PDO('sqlite:databases/darkkhaki.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Crear tablas si no existen
$db->exec("CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT,
    email TEXT,
    usuario TEXT UNIQUE,
    contrasena TEXT
);

CREATE TABLE IF NOT EXISTS horas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER,
    fecha TEXT,
    horas REAL,
    FOREIGN KEY(usuario_id) REFERENCES usuarios(id)
);");

// Insertar usuario inicial si no existe
$stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = 'danielcreux'");
$stmt->execute();
if ($stmt->fetchColumn() == 0) {
    $insert = $db->prepare("INSERT INTO usuarios (nombre, email, usuario, contrasena) VALUES (?, ?, ?, ?)");
    $insert->execute([
        'Joshué Daniel Freire Sánchez',
        'jd2000_fs@hotmail.com',
        'danielcreux',
        password_hash('danielcreux', PASSWORD_DEFAULT)
    ]);
}

// Autenticación y Registro
function login($db, $usuario, $contrasena) {
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($contrasena, $user['contrasena'])) {
        $_SESSION['user'] = $user;
        header("Location: index.php");
        exit;
    }
    return false;
}

function register($db, $nombre, $email, $usuario, $contrasena) {
    $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, usuario, contrasena) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$nombre, $email, $usuario, password_hash($contrasena, PASSWORD_DEFAULT)])) {
        login($db, $usuario, $contrasena);
    }
}

// Procesamiento de formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        login($db, $_POST['usuario'], $_POST['contrasena']);
    } elseif (isset($_POST['register'])) {
        register($db, $_POST['nombre'], $_POST['email'], $_POST['usuario'], $_POST['contrasena']);
    } elseif (isset($_POST['guardar_horas']) && isset($_SESSION['user'])) {
        foreach ($_POST['horas'] as $fecha => $horas) {
            $stmt = $db->prepare("REPLACE INTO horas (usuario_id, fecha, horas) VALUES ((SELECT id FROM usuarios WHERE usuario = ?), ?, ?)");
            $stmt->execute([$_SESSION['user']['usuario'], $fecha, $horas]);
        }
    }
}

// HTML de inicio de sesión o registro si no está logueado
if (!isset($_SESSION['user'])) {
    echo '<link rel="stylesheet" href="estilos.css">';
    echo '<link rel="icon" type="image/svg+xml" href="darkkhaki.png">';
    echo '<div class="login-form">';
    echo '<title>Darkkhaki</title>';
    echo '<h1><img src="darkkhaki.png"> darkhkaki</h1>';
    echo '<h2>Login</h2>
        <form method="POST">
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="contrasena" placeholder="Contraseña" required>
            <button name="login">Entrar</button>
        </form>
        <h2>Registro</h2>
        <form method="POST">
            <input type="text" name="nombre" placeholder="Nombre completo" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="contrasena" placeholder="Contraseña" required>
            <button name="register">Registrar</button>
        </form>';
    echo '</div>';
    exit;
}

// Función para mostrar calendario
function mostrarCalendario($user, $db, $verOtro = false) {
    $usuario = $user['usuario'];
    $stmt = $db->prepare("SELECT fecha, horas FROM horas WHERE usuario_id = (SELECT id FROM usuarios WHERE usuario = ?)");
    $stmt->execute([$usuario]);
    $horasDB = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $total = array_sum($horasDB);

    echo "<form method='POST'>";
    echo "<h2>Calendario de $usuario</h2>";
    echo "<div class='total'>Total de horas: $total</div>";
    echo "<div class='calendarios'>";
    foreach ([3, 4, 5, 6] as $mes) {
        $dias = cal_days_in_month(CAL_GREGORIAN, $mes, 2025);
        $primerDia = date('w', strtotime("2025-$mes-01"));

        echo "<div class='mes'>";
        echo "<h3>" . date('F', mktime(0,0,0,$mes,1)) . "</h3>";
        echo "<div class='grid'>";
        $diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        foreach ($diasSemana as $d) echo "<div class='dia-titulo'>$d</div>";

        for ($i = 0; $i < $primerDia; $i++) echo "<div class='dia-vacio'></div>";
        for ($dia = 1; $dia <= $dias; $dia++) {
            $fecha = sprintf("2025-%02d-%02d", $mes, $dia);
            $valor = $horasDB[$fecha] ?? '';
            echo "<div class='dia'><span>$dia</span><input type='number' step='0.1' name='horas[$fecha]' value='$valor'" . ($verOtro ? " readonly" : "") . "></div>";
        }
        echo "</div></div>";
    }
    echo "</div>";
    if (!$verOtro) echo "<button name='guardar_horas'>Guardar</button>";
    echo "</form>";
}

// Interfaz principal
echo '<link rel="stylesheet" href="estilos.css">';
echo "<div class='bienvenida'>Bienvenido, {$_SESSION['user']['nombre']} | <a href='?logout=true'>Cerrar sesión</a></div>";
if ($_SESSION['user']['usuario'] === 'danielcreux') {
    echo "<h2>Usuarios con calendario</h2><ul>";
    $stmt = $db->query("SELECT DISTINCT u.usuario FROM usuarios u JOIN horas h ON u.id = h.usuario_id");
    while ($row = $stmt->fetch()) {
        echo "<li><a href='?ver={$row['usuario']}'>{$row['usuario']}</a></li>";
    }
    echo "</ul>";
}

if (isset($_GET['ver']) && $_SESSION['user']['usuario'] === 'danielcreux') {
    $otro = ['usuario' => $_GET['ver']];
    mostrarCalendario($otro, $db, true);
} else {
    mostrarCalendario($_SESSION['user'], $db);
}
?>


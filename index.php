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
$db->exec('PRAGMA journal_mode=WAL');  // Enable Write-Ahead Logging
$db->exec('PRAGMA busy_timeout=5000'); // Set timeout to 5 seconds
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
);

CREATE TABLE IF NOT EXISTS plantillas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER,
    nombre TEXT,
    horas TEXT,
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

function login($db, $usuario, $contrasena) {
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $_SESSION['error'] = "Usuario no encontrado";
        return false;
    }
    
    if (!password_verify($contrasena, $user['contrasena'])) {
        $_SESSION['error'] = "Contraseña incorrecta";
        return false;
    }
    
    $_SESSION['user'] = $user;
    header("Location: index.php");
    exit;
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
            if ($horas !== '') {
                $stmt = $db->prepare("REPLACE INTO horas (usuario_id, fecha, horas) VALUES ((SELECT id FROM usuarios WHERE usuario = ?), ?, ?)");
                $stmt->execute([$_SESSION['user']['usuario'], $fecha, $horas]);
            }
        }
        header("Location: index.php");
        exit;
    } elseif (isset($_POST['aplicar_diseno']) && isset($_SESSION['user'])) {
        $_SESSION['diseno'] = [
            'vista' => $_POST['vista_calendario'],
            'color_fondo' => $_POST['color_fondo'],
            'color_texto' => $_POST['color_texto'],
            'mostrar_fines_semana' => isset($_POST['mostrar_fines_semana']),
            'mostrar_total_diario' => isset($_POST['mostrar_total_diario'])
        ];
        header("Location: index.php");
        exit;
    } elseif (isset($_POST['guardar_plantilla']) && isset($_SESSION['user'])) {
        $nombre = $_POST['nombre_plantilla'];
        $horas = json_encode($_POST['horas']);
        $stmt = $db->prepare("INSERT INTO plantillas (usuario_id, nombre, horas) VALUES ((SELECT id FROM usuarios WHERE usuario = ?), ?, ?)");
        $stmt->execute([$_SESSION['user']['usuario'], $nombre, $horas]);
    } elseif (isset($_POST['cargar_plantilla']) && isset($_SESSION['user'])) {
        $stmt = $db->prepare("SELECT horas FROM plantillas WHERE id = ? AND usuario_id = (SELECT id FROM usuarios WHERE usuario = ?)");
        $stmt->execute([$_POST['cargar_plantilla'], $_SESSION['user']['usuario']]);
        $plantilla = $stmt->fetch();
        if ($plantilla) {
            $horas = json_decode($plantilla['horas'], true);
            foreach ($horas as $fecha => $valor) {
                $stmt = $db->prepare("REPLACE INTO horas (usuario_id, fecha, horas) VALUES ((SELECT id FROM usuarios WHERE usuario = ?), ?, ?)");
                $stmt->execute([$_SESSION['user']['usuario'], $fecha, $valor]);
            }
        }
    } elseif (isset($_POST['eliminar_plantilla']) && isset($_SESSION['user'])) {
        $stmt = $db->prepare("DELETE FROM plantillas WHERE id = ? AND usuario_id = (SELECT id FROM usuarios WHERE usuario = ?)");
        $stmt->execute([$_POST['eliminar_plantilla'], $_SESSION['user']['usuario']]);
    }
}
// HTML de inicio de sesión o registro si no está logueado
if (!isset($_SESSION['user'])) {  // Fixed the typo 'f' to 'if'
    echo '<link rel="stylesheet" href="estilos.css">';
    echo '<link rel="icon" type="image/svg+xml" href="darkkhaki.png">';
    echo '<div class="login-form">';
    echo '<title>Darkkhaki</title>';
    echo '<h1><img src="darkkhaki.png"> darkhkaki</h1>';
    
    if (isset($_SESSION['error'])) {
        echo '<div class="error">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    
    echo '<div class="auth-container">';
    echo '<div class="auth-section">';
    echo '<h2>Login</h2>
        <form method="POST">
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="contrasena" placeholder="Contraseña" required>
            <button name="login">Entrar</button>
        </form>';
    echo '</div>';
    
    echo '<div class="auth-section">';
    echo '<h2>Registro</h2>
        <form method="POST">
            <input type="text" name="nombre" placeholder="Nombre completo" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="contrasena" placeholder="Contraseña" required>
            <button name="register">Registrar</button>
        </form>';
    echo '</div>';
    echo '</div></div>';
    exit;
}

function mostrarCalendario($user, $db, $verOtro = false) {
    $usuario = $user['usuario'];
    $stmt = $db->prepare("SELECT fecha, horas FROM horas WHERE usuario_id = (SELECT id FROM usuarios WHERE usuario = ?)");
    $stmt->execute([$usuario]);
    $horasDB = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $total = array_sum($horasDB);

    // Obtener configuración de diseño
    $diseno = $_SESSION['diseno'] ?? [
        'vista' => 'mes',
        'color_fondo' => '#ffffff',
        'color_texto' => '#000000',
        'mostrar_fines_semana' => true,
        'mostrar_total_diario' => true
    ];

    echo "<style>
        .calendarios { background-color: {$diseno['color_fondo']}; }
        .dia, .dia-titulo { color: {$diseno['color_texto']}; }
    </style>";

    echo "<form method='POST'>";
    echo "<h2>Calendario de $usuario</h2>";
    echo "<div class='total'>Total de horas: $total</div>";
    echo "<div class='calendarios'>";

    if ($diseno['vista'] === 'mes') {
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
                $diaSemana = date('w', strtotime($fecha));
                
                if (!$diseno['mostrar_fines_semana'] && ($diaSemana == 0 || $diaSemana == 6)) {
                    continue;
                }
                
                echo "<div class='dia'>";
                echo "<span>$dia</span>";
                echo "<input type='number' step='0.1' name='horas[$fecha]' value='$valor'" . ($verOtro ? " readonly" : "") . ">";
                if ($diseno['mostrar_total_diario'] && $valor !== '') {
                    echo "<div class='total-diario'>Total: $valor</div>";
                }
                echo "</div>";
            }
            echo "</div></div>";
        }
    } elseif ($diseno['vista'] === 'lista') {
        echo "<div class='vista-lista'>";
        foreach ($horasDB as $fecha => $horas) {
            echo "<div class='dia-lista'>";
            echo "<span>" . date('d/m/Y', strtotime($fecha)) . "</span>";
            echo "<input type='number' step='0.1' name='horas[$fecha]' value='$horas'" . ($verOtro ? " readonly" : "") . ">";
            echo "</div>";
        }
        echo "</div>";
    }
    
    echo "</div>";
    if (!$verOtro) {
        echo "<button name='guardar_horas' class='btn-primary'>Guardar</button>";
        
        // Opciones de diseño del calendario
        echo "<div class='opciones-diseno'>";
        echo "<h3>Personalización del Calendario</h3>";
        echo "<div class='controles-diseno'>";
        
        // Vista del calendario
        echo "<div class='opcion-grupo'>";
        echo "<label class='opcion-titulo'>Vista del Calendario</label>";
        echo "<div class='selector-vista'>";
        echo "<select name='vista_calendario'>";
        echo "<option value='mes'" . ($diseno['vista'] === 'mes' ? ' selected' : '') . ">Vista Mensual</option>";
        echo "<option value='lista'" . ($diseno['vista'] === 'lista' ? ' selected' : '') . ">Vista Lista</option>";
        echo "</select>";
        echo "</div></div>";
        
        // Colores
        echo "<div class='opcion-grupo'>";
        echo "<label class='opcion-titulo'>Colores</label>";
        echo "<div class='opciones-color'>";
        echo "<div class='color-picker'>";
        echo "<label>Fondo <input type='color' name='color_fondo' value='{$diseno['color_fondo']}'></label>";
        echo "</div>";
        echo "<div class='color-picker'>";
        echo "<label>Texto <input type='color' name='color_texto' value='{$diseno['color_texto']}'></label>";
        echo "</div>";
        echo "</div></div>";
        
        // Visualización
        echo "<div class='opcion-grupo'>";
        echo "<label class='opcion-titulo'>Visualización</label>";
        echo "<div class='opciones-visualizacion'>";
        echo "<label class='checkbox-custom'>";
        echo "<input type='checkbox' name='mostrar_fines_semana'" . ($diseno['mostrar_fines_semana'] ? ' checked' : '') . ">";
        echo "<span class='checkmark'></span>";
        echo "Mostrar fines de semana";
        echo "</label>";
        echo "<label class='checkbox-custom'>";
        echo "<input type='checkbox' name='mostrar_total_diario'" . ($diseno['mostrar_total_diario'] ? ' checked' : '') . ">";
        echo "<span class='checkmark'></span>";
        echo "Mostrar total diario";
        echo "</label>";
        echo "</div></div>";
        
        echo "<button name='aplicar_diseno' class='btn-aplicar'>Aplicar Cambios</button>";
        echo "</div></div>";
    }
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
$db = null; // Close the database connection
?>


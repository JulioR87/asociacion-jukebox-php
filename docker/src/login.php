<?php
// docker/src/login.php
require_once __DIR__ . '/lib/sesion.php';
require_once __DIR__ . '/lib/almacen-json.php';

// Si ya está autenticado, enviarlo directo a home
if (!empty($_SESSION['usuario_id'])) {
    header('Location: home.php', true, 303);
    exit;
}

$email = '';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validar Token CSRF
    validarTokenCsrf();

    $emailInput = $_POST['email'] ?? '';
    $passwordInput = $_POST['password'] ?? '';

    if (is_string($emailInput) && is_string($passwordInput)) {
        $email = strtolower(trim($emailInput));
        $password = $passwordInput; // No aplicamos trim a la clave

        // 2. Leer usuarios registrados
        $rutaUsuarios = '/var/www/data/usuarios.json';
        $usuarios = [];
        try {
            $usuarios = leerJsonConBloqueo($rutaUsuarios);
        } catch (Throwable $e) {
            $usuarios = [];
        }

        // 3. Buscar usuario por correo
        $usuarioEncontrado = null;
        foreach ($usuarios as $user) {
            if (isset($user['email']) && strtolower($user['email']) === $email) {
                $usuarioEncontrado = $user;
                break;
            }
        }

        // 4. Verificar clave con password_verify
        if ($usuarioEncontrado !== null && password_verify($password, $usuarioEncontrado['password_hash'])) {
            // Renovar ID de sesión contra fijación de sesión
            session_regenerate_id(true);

            $_SESSION['usuario_id'] = $usuarioEncontrado['id'];
            $_SESSION['alias'] = $usuarioEncontrado['alias'];

            // Redirigir a la zona privada con código 303
            header('Location: home.php', true, 303);
            exit;
        } else {
            // Mismo mensaje genérico para usuario desconocido o clave errónea
            http_response_code(401);
            $error = 'Credenciales no válidas.';
        }
    } else {
        http_response_code(400);
        $error = 'Formato de datos no válido.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Jukebox</title>
</head>
<body>
    <h1>Acceso de usuarios</h1>

    <?php if (isset($_GET['registrado'])): ?>
        <p style="color: green; font-weight: bold;">Registro completado con éxito. Ya puedes identificarte.</p>
    <?php endif; ?>

    <?php if ($error !== null): ?>
        <p style="color: red; font-weight: bold;"><?php echo htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="post" action="login.php">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">

        <p>
            <label for="email">Correo electrónico:</label><br>
            <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
        </p>

        <p>
            <label for="password">Contraseña:</label><br>
            <!-- Siempre vacía ante errores -->
            <input type="password" id="password" name="password" value="">
        </p>

        <button type="submit">Entrar</button>
    </form>

    <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
</body>
</html>
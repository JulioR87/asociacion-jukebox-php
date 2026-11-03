<?php
// docker/src/register.php
require_once __DIR__ . '/lib/sesion.php';
require_once __DIR__ . '/lib/almacen-json.php';

$alias = '';
$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validar Token CSRF
    validarTokenCsrf();

    // 2. Validación de Alias
    if (isset($_POST['alias']) && is_string($_POST['alias'])) {
        $alias = trim($_POST['alias']);
        $longitudAlias = mb_strlen($alias, 'UTF-8');
        if ($longitudAlias < 2 || $longitudAlias > 30) {
            $errors['alias'] = 'El alias debe tener entre 2 y 30 caracteres.';
        }
    } else {
        $errors['alias'] = 'Alias no válido.';
    }

    // 3. Validación y Normalización de Correo
    if (isset($_POST['email']) && is_string($_POST['email'])) {
        $email = strtolower(trim($_POST['email']));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'El correo electrónico no tiene un formato válido.';
        }
    } else {
        $errors['email'] = 'Correo no válido.';
    }

    // 4. Validación de Contraseña
    $password = $_POST['password'] ?? null;
    $password_confirm = $_POST['password_confirm'] ?? null;

    if (!is_string($password) || !is_string($password_confirm)) {
        $errors['password'] = 'Formato de contraseña no válido.';
    } else {
        $longitudPassword = mb_strlen($password, 'UTF-8');
        $bytesPassword = strlen($password);

        if ($longitudPassword < 12 || $longitudPassword > 64) {
            $errors['password'] = 'La contraseña debe tener entre 12 y 64 caracteres.';
        } elseif ($bytesPassword > 72) {
            $errors['password'] = 'La contraseña supera el límite seguro de 72 bytes.';
        } elseif ($password !== $password_confirm) {
            $errors['password_confirm'] = 'Las contraseñas no coinciden.';
        }
    }

    // 5. Procedimiento común de actualización atómica bajo LOCK_EX
    if (empty($errors)) {
        $rutaUsuarios = '/var/www/data/usuarios.json';

        try {
            actualizarJsonConBloqueo($rutaUsuarios, function (array $usuarios) use ($alias, $email, $password) {
                // Comprobar duplicados sobre los datos recién leídos dentro del cerrojo
                foreach ($usuarios as $u) {
                    if (isset($u['email']) && strtolower($u['email']) === $email) {
                        throw new DomainException('Este correo electrónico ya está registrado.');
                    }
                }

                // Calcular ID autoincremental
                $maxId = 0;
                foreach ($usuarios as $u) {
                    if (isset($u['id']) && $u['id'] > $maxId) {
                        $maxId = (int)$u['id'];
                    }
                }
                $newId = $maxId + 1;

                // Generar hash seguro
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $usuarios[] = [
                    'id' => $newId,
                    'alias' => $alias,
                    'email' => $email,
                    'password_hash' => $hash
                ];

                return $usuarios;
            });

            // Redirigir a login con código 303 si se guardó con éxito
            header('Location: login.php?registrado=1', true, 303);
            exit;

        } catch (DomainException $e) {
            $errors['email'] = $e->getMessage();
        } catch (Throwable $e) {
            $errors['general'] = 'Fallo durante el guardado protegido: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Jukebox</title>
</head>
<body>
    <h1>Crear cuenta de prueba</h1>

    <?php if (isset($errors['general'])): ?>
        <p style="color: red; font-weight: bold;"><?php echo $errors['general']; ?></p>
    <?php endif; ?>

    <form method="post" action="register.php">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">

        <p>
            <label for="alias">Alias (2 a 30 caracteres):</label><br>
            <input type="text" id="alias" name="alias" value="<?php echo htmlspecialchars($alias, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
            <?php if (isset($errors['alias'])): ?>
                <span style="color: red;"><?php echo $errors['alias']; ?></span>
            <?php endif; ?>
        </p>

        <p>
            <label for="email">Correo electrónico:</label><br>
            <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
            <?php if (isset($errors['email'])): ?>
                <span style="color: red;"><?php echo $errors['email']; ?></span>
            <?php endif; ?>
        </p>

        <p>
            <label for="password">Contraseña (12 a 64 caracteres):</label><br>
            <input type="password" id="password" name="password" value="">
            <?php if (isset($errors['password'])): ?>
                <span style="color: red;"><?php echo $errors['password']; ?></span>
            <?php endif; ?>
        </p>

        <p>
            <label for="password_confirm">Repetir contraseña:</label><br>
            <input type="password" id="password_confirm" name="password_confirm" value="">
            <?php if (isset($errors['password_confirm'])): ?>
                <span style="color: red;"><?php echo $errors['password_confirm']; ?></span>
            <?php endif; ?>
        </p>

        <button type="submit">Registrar cuenta</button>
    </form>
</body>
</html>
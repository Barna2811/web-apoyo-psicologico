<?php
include 'includes/header.php';
include 'database.php'; // Incluye la conexión a la BD

session_start(); // Iniciar sesión para manejar el estado del usuario

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = $_POST['nombre_usuario'];
    $contrasena = $_POST['contrasena'];

    // Prevenir inyección SQL usando prepared statements
    // Modificado para seleccionar también 'tipo_usuario'
    $stmt = $conn->prepare("SELECT id, contrasena, tipo_usuario FROM usuarios WHERE nombre_usuario = ?");
    $stmt->bind_param("s", $nombre_usuario);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id_usuario, $contrasena_hashed, $tipo_usuario_db); // Añadido $tipo_usuario_db

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        if (password_verify($contrasena, $contrasena_hashed)) {
            $_SESSION['usuario_id'] = $id_usuario;
            $_SESSION['nombre_usuario'] = $nombre_usuario;
            $_SESSION['tipo_usuario'] = $tipo_usuario_db; // Guardar el tipo de usuario en la sesión

            $mensaje = "<p class='success'>¡Bienvenido, " . htmlspecialchars($nombre_usuario) . "!</p>";

            // Redirigir al usuario según su tipo
            if ($tipo_usuario_db === 'paciente') {
                header("Location: dashboard_paciente.php");
            } elseif ($tipo_usuario_db === 'especialista') {
                header("Location: dashboard_especialista.php");
            } else {
                // Si el tipo de usuario es desconocido o no está definido, redirigir a una página predeterminada
                header("Location: index.php");
            }
            exit();
        } else {
            $mensaje = "<p class='error'>Contraseña incorrecta.</p>";
        }
    } else {
        $mensaje = "<p class='error'>Usuario no encontrado.</p>";
    }
    $stmt->close();
}
$conn->close();
?>

<section class="auth-form">
    <h2>Ingresar</h2>
    <?php echo $mensaje; ?>
    <form action="ingresar.php" method="POST">
        <div class="form-group">
            <label for="nombre_usuario">Nombre de Usuario:</label>
            <input type="text" id="nombre_usuario" name="nombre_usuario" required>
        </div>
        <div class="form-group">
            <label for="contrasena">Contraseña:</label>
            <input type="password" id="contrasena" name="contrasena" required>
        </div>
        <button type="submit" class="btn">Ingresar</button>
    </form>
    <p>¿No tienes una cuenta? <a href="registrarse.php">Regístrate aquí</a>.</p>
</section>

<?php include 'includes/footer.php'; ?>
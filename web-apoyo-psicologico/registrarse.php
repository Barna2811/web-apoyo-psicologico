<?php
include 'includes/header.php';
include 'database.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = $_POST['nombre_usuario'];
    $email = $_POST['email'];
    $contrasena = $_POST['contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];
    $tipo_usuario = $_POST['tipo_usuario'];
    $especialidad = isset($_POST['especialidad']) ? $_POST['especialidad'] : NULL; // Nuevo campo

    if ($contrasena !== $confirmar_contrasena) {
        $mensaje = "<p class='error'>Las contraseñas no coinciden.</p>";
    } else {
        $contrasena_hashed = password_hash($contrasena, PASSWORD_DEFAULT);

        // Modificado para incluir 'especialidad'
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario, email, contrasena, tipo_usuario, especialidad) VALUES (?, ?, ?, ?, ?)");
        // 'sssss' para 5 strings, o 'sssss' si especialidad es NULLable, o 'ssss' si no la incluyes para pacientes.
        // Asumiendo que especialidad puede ser NULL para pacientes, usamos "sssss" y pasamos NULL si no es especialista.
        $stmt->bind_param("sssss", $nombre_usuario, $email, $contrasena_hashed, $tipo_usuario, $especialidad);


        if ($stmt->execute()) {
            $mensaje = "<p class='success'>¡Registro exitoso! Ya puedes <a href='ingresar.php'>iniciar sesión</a>.</p>";
        } else {
            if ($conn->errno == 1062) {
                 $mensaje = "<p class='error'>El nombre de usuario o el correo electrónico ya están registrados.</p>";
            } else {
                $mensaje = "<p class='error'>Error al registrar: " . $stmt->error . "</p>";
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>

<section class="auth-form">
    <h2>Registrarse</h2>
    <?php echo $mensaje; ?>
    <form action="registrarse.php" method="POST">
        <div class="form-group">
            <label for="nombre_usuario">Nombre de Usuario:</label>
            <input type="text" id="nombre_usuario" name="nombre_usuario" required>
        </div>
        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="contrasena">Contraseña:</label>
            <input type="password" id="contrasena" name="contrasena" required>
        </div>
        <div class="form-group">
            <label for="confirmar_contrasena">Confirmar Contraseña:</label>
            <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>
        </div>
        <div class="form-group">
            <label for="tipo_usuario">Tipo de Usuario:</label>
            <select id="tipo_usuario" name="tipo_usuario" required onchange="toggleEspecialidadField()">
                <option value="paciente">Paciente</option>
                <option value="especialista">Especialista</option>
            </select>
        </div>
        <div class="form-group" id="especialidad_group" style="display:none;">
            <label for="especialidad">Especialidad:</label>
            <select id="especialidad" name="especialidad">
                <option value="">Selecciona una especialidad</option>
                <?php
                // Conexión temporal para cargar especialidades, luego se cierra
                include 'database.php';
                $sql_especialidades = "SELECT nombre_especialidad FROM especialidades_disponibles ORDER BY nombre_especialidad ASC";
                $result_especialidades = $conn->query($sql_especialidades);
                if ($result_especialidades->num_rows > 0) {
                    while($row_esp = $result_especialidades->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($row_esp['nombre_especialidad']) . "'>" . htmlspecialchars($row_esp['nombre_especialidad']) . "</option>";
                    }
                }
                $conn->close(); // Importante cerrar la conexión aquí si se abrió solo para esto
                ?>
            </select>
        </div>
        <button type="submit" class="btn">Registrarse</button>
    </form>
    <p>¿Ya tienes una cuenta? <a href="ingresar.php">Ingresar aquí</a>.</p>
</section>

<?php include 'includes/footer.php'; ?>

<script>
    // JavaScript para mostrar/ocultar el campo de especialidad
    function toggleEspecialidadField() {
        var tipoUsuario = document.getElementById('tipo_usuario').value;
        var especialidadGroup = document.getElementById('especialidad_group');
        var especialidadSelect = document.getElementById('especialidad');

        if (tipoUsuario === 'especialista') {
            especialidadGroup.style.display = 'block';
            especialidadSelect.setAttribute('required', 'required'); // Hacerlo requerido para especialistas
        } else {
            especialidadGroup.style.display = 'none';
            especialidadSelect.removeAttribute('required'); // Quitar requerido para pacientes
            especialidadSelect.value = ''; // Limpiar selección al cambiar a paciente
        }
    }

    // Ejecutar al cargar la página para el estado inicial
    document.addEventListener('DOMContentLoaded', toggleEspecialidadField);
</script>
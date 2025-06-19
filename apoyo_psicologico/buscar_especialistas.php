<?php
session_start();
include 'includes/header.php';

// Verificar si el usuario está logueado y es especialista
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'especialista') {
    header("Location: ingresar.php"); // Redirigir si no está logueado o no es especialista
    exit();
}
?>

<style>
    /* Estilos específicos para el dashboard del especialista */
    .content-section {
        background-color: #e8f5e9; /* Un verde muy claro */
        border: 1px solid #c8e6c9; /* Borde verde suave */
        padding: 40px;
        margin-top: 30px;
        text-align: center;
        border-radius: 10px; /* Bordes redondeados */
        box-shadow: 0 5px 15px rgba(0,0,0,0.1); /* Sombra suave */
        margin-left: auto; /* Centrar la sección */
        margin-right: auto; /* Centrar la sección */
        max-width: 900px; /* Ancho máximo para la sección */
    }

    .content-section h2 {
        color: #2e7d32; /* Verde oscuro */
        margin-bottom: 25px;
        font-size: 2.2em;
    }

    .content-section p {
        color: #555;
        font-size: 1.1em;
        line-height: 1.6;
        margin-bottom: 30px;
    }

    .content-section ul {
        list-style: none;
        padding: 0;
        margin-bottom: 40px;
        display: flex; /* Para poner los elementos en fila */
        flex-wrap: wrap; /* Para que se ajusten en pantallas pequeñas */
        justify-content: center; /* Centrar los elementos de la lista */
        gap: 20px; /* Espacio entre los elementos de la lista */
    }

    .content-section ul li {
        margin-bottom: 0; /* Ya no necesitamos el margin-bottom aquí */
    }

    .content-section ul li a {
        display: inline-block;
        background-color: #4CAF50; /* Verde principal */
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        transition: background-color 0.3s ease, transform 0.2s ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .content-section ul li a:hover {
        background-color: #388E3C; /* Verde más oscuro al pasar el ratón */
        transform: translateY(-2px); /* Pequeño efecto de elevación */
    }

    /* Estilo para el botón de cerrar sesión */
    .content-section .btn {
        display: inline-block; /* Para asegurar que el botón tiene los estilos de padding */
        background-color: #f44336; /* Rojo para cerrar sesión */
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: bold;
        text-decoration: none; /* Asegurar que no tenga subrayado */
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: background-color 0.3s ease; /* Transición para el hover */
    }

    .content-section .btn:hover {
        background-color: #d32f2f; /* Rojo más oscuro al pasar el ratón */
    }
</style>

<section class="content-section">
    <h2>Bienvenido, Especialista <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></h2>
    <p>Este es tu panel de control como especialista. Aquí podrás gestionar tu perfil, ver casos y acceder a herramientas.</p>
    <ul>
        <li><a href="#">Gestionar mi perfil profesional</a></li>
        <li><a href="#">Ver solicitudes de pacientes (funcionalidad futura)</a></li>
        <li><a href="http://localhost/foro/" target="_blank">Participar en el Foro de la Comunidad</a></li>
        <li><a href="#">Acceder a herramientas para especialistas</a></li>
    </ul>
    <p><a href="logout.php" class="btn">Cerrar Sesión</a></p>
</section>

<?php include 'includes/footer.php'; ?>
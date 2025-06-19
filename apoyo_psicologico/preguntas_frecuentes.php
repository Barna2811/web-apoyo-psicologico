<?php include 'includes/header.php'; ?>
<?php include 'database.php'; // Incluye la conexión a la BD ?>

<section class="content-section">
    <h2>Preguntas Frecuentes (FAQ)</h2>

    <div class="faq-list">
        <?php
        $sql = "SELECT pregunta, respuesta FROM preguntas_frecuentes";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='faq-item'>";
                echo "<h3>" . htmlspecialchars($row['pregunta']) . "</h3>";
                echo "<p>" . htmlspecialchars($row['respuesta']) . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p>No hay preguntas frecuentes en este momento.</p>";
        }
        $conn->close();
        ?>
    </div>

</section>

<?php include 'includes/footer.php'; ?>
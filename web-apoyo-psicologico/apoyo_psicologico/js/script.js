document.addEventListener('DOMContentLoaded', function() {
    // --- Lógica para el Cuestionario ---
    const cuestionarioForm = document.querySelector('.cuestionario-section form');
    if (cuestionarioForm) {
        cuestionarioForm.addEventListener('submit', function(event) {
            let allAnswered = true;
            const radioGroups = cuestionarioForm.querySelectorAll('input[type="radio"]');
            const questions = {};

            // Agrupar los radios por su nombre (ej: q1, q2)
            radioGroups.forEach(radio => {
                if (!questions[radio.name]) {
                    questions[radio.name] = [];
                }
                questions[radio.name].push(radio);
            });

            // Verificar que al menos una opción esté seleccionada por cada grupo de preguntas
            for (const questionName in questions) {
                const radiosInGroup = questions[questionName];
                const isChecked = Array.from(radiosInGroup).some(radio => radio.checked);
                if (!isChecked) {
                    allAnswered = false;
                    break; // Salir del bucle si falta una respuesta
                }
            }

            if (!allAnswered) {
                alert('Por favor, responde todas las preguntas del cuestionario antes de enviar.');
                event.preventDefault(); // Evita que el formulario se envíe
            }
            // Si todo está respondido, el formulario se enviará normalmente.
        });
    }

    // --- Lógica para la Barra de Navegación (Ej. resaltar el elemento activo) ---
    const navLinks = document.querySelectorAll('nav ul li a');
    const path = window.location.pathname;
    const page = path.split("/").pop(); // Obtiene el nombre del archivo (ej: index.php)

    navLinks.forEach(link => {
        if (link.getAttribute('href') === page || (page === '' && link.getAttribute('href') === 'index.php')) {
            link.classList.add('active'); // Añade una clase 'active' al enlace actual
        }
    });

    // Puedes añadir una clase 'active' a tu CSS para darle estilo:
    // nav ul li a.active {
    //     background-color: rgba(255, 255, 255, 0.3);
    //     border-radius: 5px;
    // }


    // --- Lógica para formularios de Ingresar/Registrarse (Validación básica) ---
    const registerForm = document.querySelector('.auth-form form[action="registrarse.php"]');
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            const password = registerForm.querySelector('#contrasena').value;
            const confirmPassword = registerForm.querySelector('#confirmar_contrasena').value;

            if (password !== confirmPassword) {
                alert('Las contraseñas no coinciden. Por favor, verifica.');
                event.preventDefault(); // Detiene el envío del formulario
            }
            // Puedes añadir más validaciones aquí (ej. longitud de contraseña, formato de email)
        });
    }

    const loginForm = document.querySelector('.auth-form form[action="ingresar.php"]');
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            const username = loginForm.querySelector('#nombre_usuario').value;
            const password = loginForm.querySelector('#contrasena').value;

            if (username.trim() === '' || password.trim() === '') {
                alert('Por favor, ingresa tu nombre de usuario y contraseña.');
                event.preventDefault();
            }
        });
    }


    // --- Scroll suave para el botón "Realizar Cuestionario" ---
    const scrollToCuestionarioBtn = document.querySelector('.hero .btn');
    if (scrollToCuestionarioBtn) {
        scrollToCuestionarioBtn.addEventListener('click', function(event) {
            event.preventDefault(); // Evita el comportamiento predeterminado del enlace
            const targetId = this.getAttribute('href').substring(1); // Obtiene 'cuestionario'
            const targetElement = document.getElementById(targetId);

            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 70, // Ajusta el offset si tienes un header fijo
                    behavior: 'smooth' // Hace el scroll suave
                });
            }
        });
    }

});
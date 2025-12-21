<footer class="bg-dark text-white pt-5 pb-2 mt-auto">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-warning fw-bold mb-3">
                        <i class="bi bi-mortarboard-fill"></i> EduPlatform
                    </h5>
                    <p class="text-secondary small">
                        Transformamos el aprendizaje con tecnología. Accede a cursos de alta calidad impartidos por expertos de la industria, disponibles 24/7.
                    </p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="text-secondary fs-5 hover-text-white"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-secondary fs-5 hover-text-white"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="text-secondary fs-5 hover-text-white"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-secondary fs-5 hover-text-white"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white fw-bold mb-3">Navegación</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="index.php" class="text-decoration-none text-secondary hover-text-white">Inicio</a></li>
                        <li class="mb-2"><a href="modules/estudiante/catalogo.php" class="text-decoration-none text-secondary hover-text-white">Cursos</a></li>
                        <li class="mb-2"><a href="modules/auth/registro.php" class="text-decoration-none text-secondary hover-text-white">Registrarse</a></li>
                        <li class="mb-2"><a href="modules/auth/login.php" class="text-decoration-none text-secondary hover-text-white">Ingresar</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white fw-bold mb-3">Soporte</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="#" class="text-decoration-none text-secondary hover-text-white">Preguntas Frecuentes</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-secondary hover-text-white">Ayuda y Contacto</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-secondary hover-text-white">Términos de Uso</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-secondary hover-text-white">Privacidad</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-6">
                    <h6 class="text-white fw-bold mb-3">Suscríbete</h6>
                    <p class="small text-secondary">Recibe ofertas exclusivas y nuevos cursos.</p>
                    <form action="#" method="POST" class="d-flex gap-2">
                        <input type="email" class="form-control form-control-sm" placeholder="Tu correo electrónico" required>
                        <button type="button" class="btn btn-warning btn-sm fw-bold">Enviar</button>
                    </form>
                </div>
            </div>

            <hr class="border-secondary opacity-25">
            
            <div class="row align-items-center py-2">
                <div class="col-md-6 text-center text-md-start small text-secondary">
                    &copy; <?php echo date('Y'); ?> <strong>EduPlatform</strong>. Todos los derechos reservados.
                </div>
                <div class="col-md-6 text-center text-md-end small">
                    <span class="text-secondary">Hecho con <i class="bi bi-heart-fill text-danger"></i> para estudiantes.</span>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Pequeño script para mejorar la interactividad de los enlaces del footer
        document.querySelectorAll('.hover-text-white').forEach(link => {
            link.addEventListener('mouseenter', () => link.classList.replace('text-secondary', 'text-white'));
            link.addEventListener('mouseleave', () => link.classList.replace('text-white', 'text-secondary'));
        });
    </script>
</body>
</html>
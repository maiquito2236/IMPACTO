<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Salud y Bienestar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/landing/pagina_principal.css">

</head>
<body>

    <nav class="navbar">
        <div class="container-fluid">
            <div class="nav-box">
                <a href="#inicio" class="logo">
                    <img src="/LOGIN_ORIGINAL/public/img/logo_odontologia.png" alt="Odonto Estética" style="width: 90%; max-width: 200px; height: auto; object-fit: contain; display: inline-block;">
                </a>
                <div class="nav-menu">
                    <a href="#inicio" class="active">Inicio</a>
                    <a href="#nosotros">Nosotros</a>
                    <a href="#servicios">Servicios</a>
                    <a href="#contacto">Contacto</a>
                </div>
                <div class="nav-auth">
                    <a href="/LOGIN_ORIGINAL/login" class="btn-link">Iniciar Sesión</a>
                    <a href="/LOGIN_ORIGINAL/registro" class="btn-primary btn-sm">Registrarse</a>
                </div>
            </div>
        </div>
    </nav>

    <header id="inicio" class="hero-section">
        <div class="container-fluid">
            <div class="hero-grid">
                <div class="hero-content">
                    <span class="sub-tag">Salud y Bienestar</span>
                    <h1 class="hero-title">Bienvenido a <span class="highlight">Odontoestetica</span></h1>
                    <p class="hero-text">En Odonto Estética, entendemos que una sonrisa saludable es el reflejo de tu bienestar general. Nacimos con la misión de transformar la experiencia de ir al dentista, combining la calidez humana con los últimos avances en tecnología digital y estética dental.</p>
                    <div class="hero-actions">
                        <a href="/LOGIN_ORIGINAL/login" class="btn-primary">
                            <i class="fa-solid fa-calendar-check"></i> Pedir Cita
                        </a>
                        <div class="hero-phone">
                            <div class="phone-icon">
                                <i class="fa-solid fa-phone color-accent"></i>
                            </div>
                            <div class="phone-info">
                                <span>SOPORTE 24/7</span>
                                <strong>3115204752</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hero-image-container">
                    <div class="hero-img-box">
                        <img src="https://images.unsplash.com/photo-1629909615184-74f495363b67?q=80&w=1169&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Doctora odontóloga sonriendo en el consultorio de Odonto Estética">
                    </div>
                    <div class="circle-decor-1"></div>
                    <div class="circle-decor-2"></div>
                </div>
            </div>
        </div>
    </header>

    <section id="servicios" class="services-section">
        <div class="container-fluid text-center">
            <span class="sub-tag">Especialidades</span>
            <h2 class="section-title">Nuestros Servicios Médicos</h2>
            <div class="title-divider"></div>
            
            <div class="carousel-container-custom">
                <div id="servicesCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
                    
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#servicesCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Grupo de servicios 1"></button>
                        <button type="button" data-bs-target="#servicesCarousel" data-bs-slide-to="1" aria-label="Grupo de servicios 2"></button>
                        <button type="button" data-bs-target="#servicesCarousel" data-bs-slide-to="2" aria-label="Grupo de servicios 3"></button>
                        <button type="button" data-bs-target="#servicesCarousel" data-bs-slide-to="3" aria-label="Grupo de servicios 4"></button>
                    </div>

                    <div class="carousel-inner">
                        
                        <div class="carousel-item active">
                            <div class="services-grid">
                                <div class="service-card">
                                    <div>
                                        <div class="card-icon-box bg-blue-light">
                                            <i class="fa-solid fa-kit-medical"></i>
                                        </div>
                                        <h3>Valoración y diagnóstico inicial</h3>
                                        <p>Examen clínico completo y plan de tratamiento</p>
                                    </div>
                                </div>
                                <div class="service-card">
                                    <div>
                                        <div class="card-icon-box bg-blue-cyan">
                                            <i class="fa-solid fa-teeth"></i>
                                        </div>
                                        <h3>Limpieza Profunda (detartraje)</h3>
                                        <p>Remoción de cálculo y profilaxis simple</p>
                                    </div>
                                </div>
                                <div class="service-card">
                                    <div>
                                        <div class="card-icon-box bg-blue-dark-icon">
                                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                                        </div>
                                        <h3>Resina de fotocurado (calza)</h3>
                                        <p>Restauración estética para cavidades por caries</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="carousel-item">
                            <div class="services-grid">
                                <div class="service-card">
                                    <div>
                                        <div class="card-icon-box bg-blue-light">
                                            <i class="fa-solid fa-tooth"></i>
                                        </div>
                                        <h3>Exodoncia Simple (extracción)</h3>
                                        <p>Retiro de pieza dental no compleja</p>
                                    </div>
                                </div>
                                <div class="service-card">
                                    <div>
                                        <div class="card-icon-box bg-blue-cyan">
                                            <i class="fa-solid fa-tablets"></i>
                                        </div>
                                        <h3>Prótesis Total Superior</h3>
                                        <p>Reemplazo total de dentadura superior en acrílico</p>
                                    </div>
                                </div>
                                <div class="service-card">
                                    <div>
                                        <div class="card-icon-box bg-blue-dark-icon">
                                            <i class="fa-solid fa-briefcase-medical"></i>
                                        </div>
                                        <h3>Prótesis Total Inferior</h3>
                                        <p>Reemplazo total de dentadura inferior en acrílico</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="carousel-item">
                            <div class="services-grid">
                                <div class="service-card">
                                    <div>
                                        <div class="card-icon-box bg-blue-light">
                                            <i class="fa-solid fa-stethoscope"></i>
                                        </div>
                                        <h3>Prótesis Parcial Removible</h3>
                                        <p>Estructura para reemplazar solo algunas piezas faltantes</p>
                                    </div>
                                </div>
                                <div class="service-card">
                                    <div>
                                        <div class="card-icon-box bg-blue-cyan">
                                            <i class="fa-solid fa-shield-heart"></i>
                                        </div>
                                        <h3>Montaje de Brackets Metálicos (cuota inicial)</h3>
                                        <p>Instalación de aparatología para alineamiento dental</p>
                                    </div>
                                </div>
                                <div class="service-card">
                                    <div>
                                        <div class="card-icon-box bg-blue-dark-icon">
                                            <i class="fa-solid fa-hospital-user"></i>
                                        </div>
                                        <h3>Control Mensual de Ortodoncia</h3>
                                        <p>Ajuste de arcos y ligaduras mensuales</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="carousel-item">
                            <div class="services-grid">
                                <div class="service-card">
                                    <div>
                                        <div class="card-icon-box bg-blue-light">
                                            <i class="fa-solid fa-heart-pulse"></i>
                                        </div>
                                        <h3>Endodoncia Monorradicular</h3>
                                        <p>Tratamiento de nervio en dientes frontales</p>
                                    </div>
                                </div>
                                <div class="service-card">
                                    <div>
                                        <div class="card-icon-box bg-blue-cyan">
                                            <i class="fa-solid fa-user-nurse"></i>
                                        </div>
                                        <h3>Endodoncia Multirradicular</h3>
                                        <p>Tratamiento de nervio en muelas (más complejo)</p>
                                    </div>
                                </div>
                                <div style="visibility: hidden; pointer-events: none;"></div>
                            </div>
                        </div>

                    </div>

                    <button class="carousel-control-prev" type="button" data-bs-target="#servicesCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#servicesCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Siguiente</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section id="nosotros" class="about-section">
        <div class="container-fluid">
            <div class="about-grid">
                <div class="about-image-box">
                    <img src="https://images.unsplash.com/photo-1643660526741-094639fbe53a?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Instalaciones del consultorio clínico Odonto Estética">
                </div>
                <div class="about-content">
                    <span class="sub-tag">Sobre Nosotros</span>
                    <h2 class="section-title-left">Comprometidos con la Excelencia de tu Salud Oral</h2>
                    <p class="about-text">Nos especializamos en diseñar sonrisas integrales y personalizadas en un entorno diseñado exclusivamente para tu tranquilidad. Aquí, los tratamientos complejos se vuelven simples y los procesos quirúrgicos o estéticos se realizan bajo los estándares clínicos, asegurando resultados duraderos y procesos completamente libres de dolor.</p>
                    
                    <div class="about-features">
                        <div class="about-feat-item">
                            <div class="feat-icon text-primary">
                                <i class="fa-solid fa-user-doctor"></i>
                            </div>
                            <div class="feat-info">
                                <strong>Especialistas Certificados</strong>
                                <p>Especialistas con excelente proceso de formación</p>
                            </div>
                        </div>
                        <div class="about-features-separator"></div>
                        <div class="about-feat-item">
                            <div class="feat-icon text-primary">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <div class="feat-info">
                                <strong>Garantía de cuidado</strong>
                                <p>Estrictos protocolos de control sanitario.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="contacto" class="contact-section">
        <div class="container-fluid">
            <div class="text-center">
                <span class="sub-tag">Contacto</span>
                <h2 class="section-title">Contactanos para atender tus dudas e inquietudes</h2>
                <div class="title-divider"></div>
            </div>

            <div class="contact-grid">
                <div class="contact-info-block">
                    <div class="info-card">
                        <h3>Datos de contacto</h3>
                        
                        <div class="info-item">
                            <i class="fa-solid fa-location-dot"></i>
                            <div class="info-text">
                                <strong>Dirección</strong>
                                <p>Calle 10#10-35</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class="fa-solid fa-phone"></i>
                            <div class="info-text">
                                <strong>Teléfono</strong>
                                <p>3115204752</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class="fa-solid fa-envelope"></i>
                            <div class="info-text">
                                <strong>Email</strong>
                                <p>odontoestetica@gmail.com</p>
                            </div>
                        </div>
                    </div>
                        <div class="mapa" id="ubicacion">
                            <h3>Nuestra Ubicación</h3>
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d995.6355891637384!2d-73.62037533052036!3d3.4607669384416804!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e3dfaee0b38966b%3A0xb8c82f25692f8950!2sCra.%2012%20%23%2010-39%2C%20Fuentedeoro%2C%20Fuente%20de%20Oro%2C%20Meta!5e0!3m2!1ses-419!2sco!4v1784203514220!5m2!1ses-419!2sco" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin">
                            </iframe>
                        </div>
                    </div>

                <div class="contact-form-card">
                    <form action="procesar_contacto.php" method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="contactName">Nombre Completo</label>
                                <input type="text" id="contactName" name="nombre" placeholder="Ej. Juan Guarnizo" required>
                            </div>
                            <div class="form-group">
                                <label for="contactEmail">Correo Electrónico</label>
                                <input type="email" id="contactEmail" name="email" placeholder="juan@ejemplo.com" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="contactPhone">Teléfono de Contacto</label>
                                <input type="tel" id="contactPhone" name="telefono" placeholder="5512345678">
                            </div>
                            <div class="form-group">
                                <label for="contactService">Tratamiento de Interés</label>
                                <input type="text" id="contactService" name="servicio_interes" placeholder="Ej. Ortodoncia u Odontología General">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="contactMessage">Mensaje o Comentarios adicionales</label>
                            <textarea id="contactMessage" name="mensaje" rows="4" placeholder="Escribe aquí tus dudas o el horario en el que prefieres recibir nuestra llamada..." required></textarea>
                        </div>
                        <button type="submit" class="btn-primary btn-flat">
                            <i class="fa-solid fa-paper-plane"></i> Enviar Mensaje
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer class="main-footer">
        <div class="container-fluid">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h2>Odonto Estética</h2>
                    <p>Cuidamos de tu salud bucal con el mejor procedimiento y responsabilidad del sector médico.</p>
                </div>
                <div class="footer-links">
                    <h4>Navegación</h4>
                    <a href="#inicio">Inicio</a>
                    <a href="#nosotros">Nosotros</a>
                    <a href="#servicios">Servicios</a>
                </div>
                <div class="footer-links">
                    <h4>Legales</h4>
                    <a href="#">Aviso de Privacidad</a>
                    <a href="#">Términos y Condiciones</a>
                    <a href="#">Políticas de Cancelación</a>
                </div>
                <div class="footer-links">
                    <h4>Horarios</h4>
                    <p>Lunes a Viernes: 8:00 AM - 8:00 PM</p>
                    <p>Sábados: 9:00 AM - 2:00 PM</p>
                    <p>Domingos: Cerrado</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Odonto Estética. Todos los derechos reservados. Desarrollado para la gestión integral clínica.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="/LOGIN_ORIGINAL/public/js/landing/pagina_principal.js"></script>
    
</body>
</html>
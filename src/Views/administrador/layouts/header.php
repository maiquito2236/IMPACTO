<header class="menu-main-header">
    <div class="menu-welcome-text">
        <h1>¡Hola, <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Administrador'); ?>!</h1>
        <p class="menu-current-date">Hoy es <?= htmlspecialchars($_SESSION['fecha_completa_es'] ?? date('d / m / Y')); ?></p>
    </div>

    <div class="menu-header-actions">
        <div style="width: 42px; height: 42px; border-radius: 50%; background: #2563eb; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px; border: 2px solid #dbeafe;">
            <?= strtoupper(substr($_SESSION['usuario_nome'] ?? 'A', 0, 1)); ?>
        </div>
    </div>
</header>
# SIGCO - Sistema Integrado de Gestión Clínica Odontológica (Odonto Estética)

Sistema integral de gestión de historias clínicas, citas, tratamientos, facturación y reportes odontológicos desarrollado en Laravel 11, Vite, JavaScript y MySQL / MariaDB.

---

## 📋 Requisitos Previos del Sistema

Para el correcto funcionamiento del software y todas sus dependencias (incluyendo exportación gráfica de reportes en PDF y compilación de assets), asegúrese de contar con:

- **Servidor Web / PHP**: PHP 8.2 o superior (vía XAMPP u otro entorno).
- **Base de Datos**: MySQL 8.0+ o MariaDB 10.4+ configurado en cotejamiento `utf8mb4_unicode_ci`.
- **Manejador de Paquetes PHP**: Composer 2.x instalado globalmente.
- **Entorno Frontend**: Node.js v18+ y npm v9+.
- **Extensiones PHP Requeridas en `php.ini`**:
  - `extension=gd` *(Esencial para la renderización de imágenes y logos corporativos en la exportación de PDFs con DomPDF)*.
  - `extension=pdo_mysql`
  - `extension=mbstring`
  - `extension=fileinfo`
  - `extension=openssl`
  - `extension=curl`

> **Nota para usuarios de XAMPP**: Para activar la extensión GD, abra el panel de control de XAMPP, haga clic en **Config** > **PHP (php.ini)** junto a Apache, busque `;extension=gd`, elimine el punto y coma inicial para que quede `extension=gd`, guarde el archivo y reinicie Apache.

---

## 🚀 Proceso de Instalación Paso a Paso

### 1. Clonación del Repositorio
Ubique la terminal en el directorio raíz de aplicaciones web (en XAMPP: `C:\xampp\htdocs\`):
```bash
cd C:\xampp\htdocs
git clone -b Esteban https://github.com/maiquito2236/IMPACTO.git proyectlaravel
cd proyectlaravel
```

### 2. Instalación de Dependencias Backend (PHP / Laravel)
Descargue e instale las librerías del framework ejecutando Composer:
```bash
composer install
```

### 3. Instalación y Compilación de Dependencias Frontend
Instale los paquetes de Node.js y compile los recursos gráficos corporativos con Vite:
```bash
npm install
npm run build
```

### 4. Configuración del Entorno de Aplicación (.env)
Copie la plantilla de entorno y genere la clave criptográfica de seguridad:
```bash
copy .env.example .env
php artisan key:generate
```

Abra el archivo `.env` en la raíz del proyecto y configure los parámetros de base de datos, zona horaria y sesión:
```ini
APP_NAME=SIGCO
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost/proyectlaravel/public

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sigco_esteban_alone
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### 5. Creación y Carga de la Base de Datos
Asegúrese de que el servicio MySQL de XAMPP esté iniciado.

#### Opción A (Consola Shell de XAMPP - Recomendada):
```bash
mysql -u root -e "CREATE DATABASE sigco_esteban_alone CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root --default-character-set=utf8mb4 sigco_esteban_alone < SIGCO.sql
```

#### Opción B (Vía phpMyAdmin):
1. Ingrese a `http://localhost/phpmyadmin/`.
2. Cree una nueva base de datos llamada `sigco_esteban_alone` con cotejamiento `utf8mb4_unicode_ci`.
3. Seleccione la base de datos, diríjase a la pestaña **Importar**, seleccione el archivo `SIGCO.sql` ubicado en la raíz del proyecto y haga clic en **Importar**.

### 6. Enlace de Almacenamiento y Limpieza de Caché
Cree el enlace simbólico para archivos públicos y limpie cualquier caché residual:
```bash
php artisan storage:link
php artisan optimize:clear
```

### 7. Puesta en Marcha del Sistema
Verifique que los módulos Apache y MySQL estén iniciados en el panel de control de XAMPP y acceda desde su navegador web:
- **Vía Apache XAMPP**: [http://localhost/proyectlaravel/public](http://localhost/proyectlaravel/public)
- **Vía servidor integrado de Laravel**:
  ```bash
  php artisan serve
  ```
  Acceda a: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## ⚙️ Parámetros de Configuración del Sistema

| Parámetro / Variable | Valor Recomendado | Descripción |
| :--- | :--- | :--- |
| **`APP_NAME`** | `SIGCO` | Nombre oficial de la aplicación. |
| **`APP_ENV`** | `local` / `production` | Entorno de ejecución de Laravel. |
| **`APP_DEBUG`** | `true` (dev) / `false` (prod) | Muestra trazas de depuración detalladas. |
| **`DB_CONNECTION`** | `mysql` | Controlador de base de datos MySQL / MariaDB. |
| **`DB_DATABASE`** | `sigco_esteban_alone` | Base de datos principal de la clínica odontológica. |
| **`DB_COLLATION`** | `utf8mb4_unicode_ci` | Codificación internacional UTF-8 (tildes, diéresis, ñ). |
| **`SESSION_DRIVER`** | `file` | Mecanismo de persistencia de sesiones. |
| **`TIMEZONE`** | `America/Bogota` | Zona horaria oficial de Colombia (`config/app.php`). |
| **`LOCALE`** | `es` | Idioma español para validaciones y textos del sistema. |

### Ajustes recomendados en `php.ini`:
- `upload_max_filesize = 10M` *(Permite subir adjuntos clínicos y radiografías)*
- `post_max_size = 12M`
- `memory_limit = 256M`
- `max_execution_time = 120`
- `extension=gd` *(Habilitada sin punto y coma)*

---

## 👥 Cuentas de Acceso y Roles de Prueba

| Rol | Correo Electrónico | Contraseña | Descripción |
| :--- | :--- | :--- | :--- |
| **Administrador** | `admin@gmail.com` | `admin123` | Control general, gestión de usuarios, horarios, consultorios, reportes y finanzas. |
| **Odontólogo** | `martharomeroodon@gmail.com` | `martha123` | Agenda médica, citas, evolución clínica, odontograma y planes de tratamiento. |
| **Paciente** | `esteban.alone@gmail.com` | `paciente123` | Agendamiento de citas, núcleo familiar (menores a cargo), historial clínico y pagos. |

---

## 🛡️ Estándares Técnicos del Proyecto
- **Arquitectura de Base de Datos**: Nombres nativos de tablas y columnas estándar (`NOMBRE_ODONTOLOGO`, `ESTADO_CITA`, `NOMBRE_ESPECIALIDAD`, `NOMBRE_PROCEDIMIENTO`, `NOMBRE_CONSULTORIO`, `ESTADO_HORARIO`, `FECHA_CREACION`) sin alias artificiales (`AS ...`) para garantizar integridad y consistencia relacional.
- **Codificación Universal**: 100% UTF-8 (`utf8mb4_unicode_ci`) tanto a nivel de aplicación como en el dump de datos de `SIGCO.sql`.

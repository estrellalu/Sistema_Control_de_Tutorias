# Sistema de Control de Tutorías

Herramienta web para el seguimiento académico y psicopedagógico de alumnos
por parte de sus tutores: agenda de citas, bitácora de sesiones, reportes
de canalización y alertas de alumnos en riesgo de deserción.

**Stack:** HTML5 + CSS3 + JavaScript (fetch/AJAX) + PHP 8 (backend) +
Supabase/PostgreSQL (consumido vía PostgREST con cURL, sin Composer ni
librerías externas).

---

## 1. Crear el proyecto en Supabase

1. Ve a https://supabase.com y crea una cuenta / proyecto nuevo (es gratis).
2. Espera a que termine de aprovisionarse .
3. En el menú lateral entra a **SQL Editor** → **New query**.
4. Abre el archivo `sql/schema.sql` de este proyecto, copia **todo** su
   contenido, pégalo en el editor y presiona **Run**.
   - Esto crea las tablas: `profesores`, `alumnos`, `citas`, `bitacora`,
     `canalizaciones`, `alertas`, sus índices, triggers y activa
     Row Level Security (RLS).
5. Ve a **Project Settings** (ícono de engranaje) → **API**.
   - Copia el valor de **Project URL**.
   - Copia el valor de **service_role** (en la sección "Project API keys").
     Esta clave tiene acceso total a la base de datos: nunca la pongas
     en código JavaScript del navegador. Aquí solo se usa en PHP
     (servidor), por eso es segura.

## 2. Configurar el proyecto

Abre el archivo `config/config.php` y reemplaza estas dos líneas con tus
datos reales de Supabase: 

```php
define('SUPABASE_URL', 'https://TU-PROYECTO.supabase.co');
define('SUPABASE_SERVICE_KEY', 'TU_SERVICE_ROLE_KEY_AQUI');
```

No necesitas tocar nada más para empezar a funcionar.

## 3. Acceso del Administrador (login fijo)

El admin **no vive en la base de datos**: sus credenciales están fijas en
`config/config.php`, ya que solo existe un administrador del sistema.

- **Usuario:** `admin@tutorias.com`
- **Contraseña:** `Admin123!`

Para cambiar la contraseña del admin, genera un nuevo hash con PHP:

```bash
php -r "echo password_hash('TU_NUEVA_CONTRASEÑA', PASSWORD_BCRYPT);"
```

y reemplaza el valor de `ADMIN_PASSWORD_HASH` en `config/config.php` con
el resultado.

Los **profesores/tutores sí se guardan en Supabase**: el admin los crea
desde el panel "Profesores / Tutores", indicando su correo y una
contraseña. Esa contraseña se guarda como hash (bcrypt) en la tabla
`profesores`, nunca en texto plano.

## 4. Ejecutar el proyecto localmente (sin servidor externo)

No necesitas Apache, Nginx, ni Composer. Solo PHP 8 instalado en tu
máquina. Desde la carpeta raíz del proyecto (`tutorias-system/`), corre:

```bash
php -S localhost:8000
```

Y abre tu navegador en:

```
http://localhost:8000
```

Eso es todo con eso verás la pantalla de login.

## 5. Flujo de uso

1. Entra como **admin** (`admin@tutorias.com` / `Admin123!`).
2. Ve a **Profesores / Tutores** → "+ Nuevo profesor" y crea las cuentas
   de tus tutores (correo + contraseña que tú definas).
3. Ve a **Alumnos** → "+ Nuevo alumno" y sube el archivo ya sea excel,calc, etc, y asignándoles
   un tutor.
4. Cierra sesión e ingresa con el correo/contraseña de un profesor que
   acabas de crear.
5. Como tutor podrás:
   - **Agenda de citas:** programar y dar seguimiento a citas con tus
     alumnos asignados, tomar la asistencia correspondiente.
   - **Bitácora de sesiones:** registrar lo tratado en cada sesión
     (tema, observaciones, acuerdos).
   - **Canalizaciones:** generar reportes cuando derivas a un alumno a
     apoyo psicológico, médico, académico, etc.
   - **Alertas de riesgo:** marcar a un alumno con nivel de riesgo
     bajo/medio/alto; esto actualiza automáticamente su nivel de riesgo
     general, visible también en el panel del admin.

## 6. Estructura del proyecto

```
tutorias-system/
├── sql/schema.sql            # Ejecutar UNA vez en el SQL Editor de Supabase
├── config/config.php         # Credenciales de Supabase + admin fijo
├── includes/
│   ├── SupabaseClient.php    # Cliente cURL para hablar con PostgREST
│   ├── auth.php              # Sesiones y control de acceso (admin/profesor)
│   └── sidebar.php           # Menú lateral reutilizable
├── api/                      # Endpoints PHP que el JS consume vía fetch()
│   ├── login.php / logout.php
│   ├── profesores.php        # CRUD tutores (solo admin)
│   ├── alumnos.php           # CRUD alumnos (admin escribe, tutor solo lee los suyos)
│   ├── citas.php             # Agenda de citas
│   ├── bitacora.php          # Bitácora de sesiones
│   ├── canalizacion.php      # Reportes de canalización
│   └── alertas.php           # Alertas de riesgo de deserción
├── admin/                    # Páginas del panel de administrador
├── tutor/                    # Páginas del panel del tutor
├── assets/css/style.css
├── assets/js/*.js            # Lógica AJAX de cada página
└── index.php                 # Login (punto de entrada)
```

## 7. Seguridad implementada

- Las contraseñas de los tutores nunca se guardan en texto plano:
  `password_hash()` / `password_verify()` de PHP (bcrypt).
- La `service_role key` de Supabase solo se usa del lado del servidor
  (PHP); nunca se envía al navegador.
- RLS (Row Level Security) está activado en todas las tablas sin
  políticas explícitas, así que **solo** las peticiones hechas con la
  service_role key (es decir, tu backend PHP) pueden leer/escribir datos.
- Cada tutor solo puede ver y modificar la información de **sus propios**
  alumnos, citas, bitácora, canalizaciones y alertas — esto se valida en
  cada endpoint de `api/`, no solo en el frontend.
- Las sesiones de PHP (`$_SESSION`) controlan quién está autenticado y
  con qué rol (`admin` o `profesor`).

## 8. Posibles mejoras a futuro

- Recuperación de contraseña por correo para los tutores.
- Exportar reportes de canalización/alertas a PDF.
- Notificaciones por correo cuando se registra una alerta de riesgo alto.
- Paginación en las tablas cuando el número de alumnos/citas crezca mucho.

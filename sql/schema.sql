-- DÓNDE EJECUTAR ESTE ARCHIVO:
--   1. Entra a tu proyecto en https://supabase.com
--   2. Menú lateral -> "SQL Editor" -> "New query"
--   3. Pega TODO este archivo y presiona "Run"
-- =====================================================================

-- Extensión para generar UUIDs
create extension if not exists "pgcrypto";

-- ---------------------------------------------------------------------
-- TABLA: profesores (tutores)
-- El admin (login fijo, NO está en esta base de datos) crea estos
-- registros desde el panel de administración.
-- ---------------------------------------------------------------------
create table if not exists profesores (
    id              uuid primary key default gen_random_uuid(),
    nombre          varchar(150) not null,
    email           varchar(150) not null unique,
    password_hash   varchar(255) not null,   -- generado con password_hash() de PHP
    especialidad    varchar(150),             -- ej. Psicopedagogía, Académico, etc.
    telefono        varchar(30),
    activo          boolean not null default true,
    created_at      timestamptz not null default now(),
    updated_at      timestamptz not null default now()
);

-- ---------------------------------------------------------------------
-- TABLA: alumnos
-- ---------------------------------------------------------------------
create table if not exists alumnos (
    id                  uuid primary key default gen_random_uuid(),
    matricula           varchar(50) not null unique,
    nombre              varchar(150) not null,
    carrera             varchar(150),
    grupo               varchar(50),
    semestre            int,
    email               varchar(150),
    telefono            varchar(30),
    profesor_id         uuid references profesores(id) on delete set null,
    nivel_riesgo        varchar(20) not null default 'bajo'
                         check (nivel_riesgo in ('bajo','medio','alto')),
    activo              boolean not null default true,
    created_at          timestamptz not null default now(),
    updated_at          timestamptz not null default now()
);

-- ---------------------------------------------------------------------
-- TABLA: citas (agenda de citas)
-- ---------------------------------------------------------------------
create table if not exists citas (
    id              uuid primary key default gen_random_uuid(),
    alumno_id       uuid not null references alumnos(id) on delete cascade,
    profesor_id     uuid not null references profesores(id) on delete cascade,
    fecha           date not null,
    hora            time not null,
    motivo          varchar(255),
    lugar           varchar(150),
    estado          varchar(20) not null default 'pendiente'
                     check (estado in ('pendiente','confirmada','realizada','cancelada')),
    created_at      timestamptz not null default now(),
    updated_at      timestamptz not null default now()
);

-- ---------------------------------------------------------------------
-- TABLA: bitacora (bitácora de sesiones realizadas)
-- ---------------------------------------------------------------------
create table if not exists bitacora (
    id              uuid primary key default gen_random_uuid(),
    cita_id         uuid references citas(id) on delete set null,
    alumno_id       uuid not null references alumnos(id) on delete cascade,
    profesor_id     uuid not null references profesores(id) on delete cascade,
    fecha_sesion    date not null,
    tema            varchar(255) not null,
    observaciones   text,
    acuerdos        text,
    created_at      timestamptz not null default now()
);

-- ---------------------------------------------------------------------
-- TABLA: canalizaciones (reportes de canalización)
-- ---------------------------------------------------------------------
create table if not exists canalizaciones (
    id                  uuid primary key default gen_random_uuid(),
    alumno_id           uuid not null references alumnos(id) on delete cascade,
    profesor_id         uuid not null references profesores(id) on delete cascade,
    area_canalizacion   varchar(100) not null, -- Psicológico, Médico, Académico, Económico, etc.
    motivo              text not null,
    fecha               date not null default current_date,
    estado              varchar(20) not null default 'pendiente'
                         check (estado in ('pendiente','en_proceso','atendida')),
    seguimiento         text,
    created_at          timestamptz not null default now()
);

-- ---------------------------------------------------------------------
-- TABLA: asistencias (lista de asistencia con 8 casillas por alumno)
-- Cada alumno tiene hasta 8 registros (uno por número de sesión, del 1
-- al 8). "presente" indica si asistió a esa sesión. Así el tutor puede
-- ver de un vistazo cuántas asistencias acumuló cada alumno.
-- ---------------------------------------------------------------------
create table if not exists asistencias (
    id              uuid primary key default gen_random_uuid(),
    alumno_id       uuid not null references alumnos(id) on delete cascade,
    profesor_id     uuid not null references profesores(id) on delete cascade,
    numero_sesion   int not null check (numero_sesion between 1 and 8),
    presente        boolean not null default false,
    fecha           date,
    created_at      timestamptz not null default now(),
    updated_at      timestamptz not null default now(),
    unique (alumno_id, numero_sesion)
);

-- ---------------------------------------------------------------------
-- TABLA: alertas (alumnos en riesgo de deserción)
-- ---------------------------------------------------------------------
create table if not exists alertas (
    id              uuid primary key default gen_random_uuid(),
    alumno_id       uuid not null references alumnos(id) on delete cascade,
    profesor_id     uuid not null references profesores(id) on delete cascade,
    tipo_alerta     varchar(100) not null, -- Inasistencias, Bajo rendimiento, Económico, Emocional, etc.
    nivel_riesgo    varchar(20) not null default 'medio'
                     check (nivel_riesgo in ('bajo','medio','alto')),
    descripcion     text,
    atendida        boolean not null default false,
    fecha           date not null default current_date,
    created_at      timestamptz not null default now()
);

-- ---------------------------------------------------------------------
-- Índices útiles
-- ---------------------------------------------------------------------
create index if not exists idx_alumnos_profesor on alumnos(profesor_id);
create index if not exists idx_citas_profesor on citas(profesor_id);
create index if not exists idx_citas_alumno on citas(alumno_id);
create index if not exists idx_bitacora_alumno on bitacora(alumno_id);
create index if not exists idx_asistencias_alumno on asistencias(alumno_id);
create index if not exists idx_asistencias_profesor on asistencias(profesor_id);
create index if not exists idx_canalizaciones_alumno on canalizaciones(alumno_id);
create index if not exists idx_alertas_alumno on alertas(alumno_id);
create index if not exists idx_alertas_atendida on alertas(atendida);

-- ---------------------------------------------------------------------
-- SEGURIDAD (RLS)
-- El backend en PHP llamará a la API REST de Supabase (PostgREST) usando
-- la "service_role key", la cual SIEMPRE ignora RLS. Por eso activamos
-- RLS sin políticas: así, si alguien intentara usar la "anon key" desde
-- el navegador, NO podría leer ni escribir nada. Solo tu backend PHP
-- (que nunca expone la service_role key) puede operar.
-- ---------------------------------------------------------------------
alter table profesores       enable row level security;
alter table alumnos          enable row level security;
alter table citas            enable row level security;
alter table bitacora         enable row level security;
alter table asistencias      enable row level security;
alter table canalizaciones   enable row level security;
alter table alertas          enable row level security;

-- (No se crean políticas a propósito: nadie excepto service_role puede
--  acceder. Si en el futuro quieres exponer algo directo al navegador
--  con la anon key, aquí es donde agregarías "create policy ...")

-- ---------------------------------------------------------------------
-- Trigger para mantener updated_at actualizado automáticamente
-- ---------------------------------------------------------------------
create or replace function set_updated_at()
returns trigger as $$
begin
  new.updated_at = now();
  return new;
end;
$$ language plpgsql;

create trigger trg_profesores_updated before update on profesores
    for each row execute function set_updated_at();
create trigger trg_alumnos_updated before update on alumnos
    for each row execute function set_updated_at();
create trigger trg_citas_updated before update on citas
    for each row execute function set_updated_at();
create trigger trg_asistencias_updated before update on asistencias
    for each row execute function set_updated_at();

-- MIGRACIÓN: agrega la tabla "asistencias"


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

create index if not exists idx_asistencias_alumno on asistencias(alumno_id);
create index if not exists idx_asistencias_profesor on asistencias(profesor_id);

alter table asistencias enable row level security;

create trigger trg_asistencias_updated before update on asistencias
    for each row execute function set_updated_at();

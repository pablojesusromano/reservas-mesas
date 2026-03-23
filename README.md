# Sistema de Reserva de Mesas

Sistema web para gestión de reservas de restaurant desarrollado en Laravel 12 con Livewire, Flux UI y SQLite.

## Funcionalidades

- **ABM de Mesas** con ubicación (A, B, C, D), número y capacidad de personas.
- **Solicitud de Reservas** con asignación automática de mesas y ubicación por orden de disponibilidad.
- **Listado de Reservas** por fecha y ubicación, mostrando las mesas asignadas en una sola consulta SQL.
- **Cancelación de Reservas** con liberación automática de mesas.

## Reglas de negocio

- Horarios permitidos: Lunes a Viernes 10:00 - 24:00 | Sábado 22:00 - 02:00 | Domingo 12:00 - 16:00.
- Duración de reserva: 2 horas por defecto.
- Máximo 3 mesas por reserva. Se pueden unir mesas de la misma ubicación.
- La ubicación la asigna el sistema en orden (A > B > C > D).
- Se puede reservar hasta 15 minutos antes del horario solicitado.
- Caché en memoria de disponibilidad por ubicación. Se decidio invalidarlo ante cada cambio para evitar complejidad.

## Requisitos

- PHP 8.2+ con las extensiones pdo_sqlite y sqlite3 descomentadas
- Composer 2.2+
- Node.js y NPM

## Instalación

```bash
# Clonar el repositorio
git clone https://github.com/pablojesusromano/reservas-mesas.git
cd reservas-mesas

# Instalar dependencias PHP
composer install

# Instalar dependencias JS
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Crear base de datos y correr migraciones con datos de prueba 
# IMPORTANTE: para correr este comando, asegurarse de descomentar las extensiones en el php.ini de su versión de PHP:
# extension=pdo_sqlite
# extension=sqlite3
php artisan migrate:fresh --seed

# Levantar servidores
php artisan serve
npm run dev
```

Acceder a `http://localhost:8000`, registrarse y comenzar a usar el sistema.

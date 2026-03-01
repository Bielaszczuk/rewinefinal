# Arquitectura del Backend

Este documento proporciona una vision completa de la arquitectura del backend de Rewine, cubriendo la estructura de capas, patrones de diseno, interacciones entre componentes y guias de desarrollo.

---

## Tabla de Contenidos

1. [Vision General](#1-vision-general)
2. [Estructura de Capas](#2-estructura-de-capas)
3. [Interaccion entre Capas](#3-interaccion-entre-capas)
4. [Patrones de Diseno](#4-patrones-de-diseno)
5. [Estructura de Directorios](#5-estructura-de-directorios)
6. [Modulos de Dominio](#6-modulos-de-dominio)
7. [Esquema de Base de Datos](#7-esquema-de-base-de-datos)
8. [Autenticacion y Autorizacion](#8-autenticacion-y-autorizacion)
9. [Manejo de Errores](#9-manejo-de-errores)
10. [Instalacion](#10-instalacion)
11. [Comandos Utiles](#11-comandos-utiles)
12. [Testing](#12-testing)
13. [Configuracion](#13-configuracion)
14. [Servicios Externos](#14-servicios-externos)

---

## 1. Vision General

El backend sigue una **Arquitectura en Capas** inspirada en **Clean Architecture** y principios de **Domain-Driven Design (DDD)**. Este enfoque proporciona una clara separacion de responsabilidades, facilidad de testing y mantenibilidad.

### Stack Tecnologico

| Componente | Tecnologia | Version |
|------------|------------|---------|
| Framework | Laravel | 11.x |
| Lenguaje | PHP | 8.3+ |
| Base de datos | PostgreSQL | 15+ |
| Autenticacion | JWT (tymon/jwt-auth) | 2.x |
| Formato API | REST + JSON | - |

### Diagrama de Arquitectura

```
+-------------------------------------------------------------------+
|                         CAPA HTTP                                  |
|  +-------------+  +-------------+  +-------------+  +----------+  |
|  | Controllers |  | Middleware  |  |  Requests   |  | Resources|  |
+-------------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------------+
|                    CAPA DE APLICACION                              |
|  +------------------+  +------------------+  +------------------+  |
|  |  QueryServices   |  | CommandServices  |  |  Otros Services  |  |
+-------------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------------+
|                       CAPA DE DOMINIO                              |
|  +-------------+  +-------------+  +-------------+                |
|  |  Contracts  |  |    DTOs     |  | Exceptions  |                |
+-------------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------------+
|                  CAPA DE INFRAESTRUCTURA                           |
|  +------------------+  +------------------+                        |
|  |   Repositories   |  |     Models       |                        |
+-------------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------------+
|                      CAPA DE DATOS                                 |
|                      +-------------+                               |
|                      | PostgreSQL  |                               |
+-------------------------------------------------------------------+
```

### Justificacion de la Arquitectura

Se eligio esta arquitectura por las siguientes razones:

1. **Separacion de responsabilidades**: Cada capa tiene una unica responsabilidad
2. **Testabilidad**: Las capas pueden probarse de forma aislada
3. **Mantenibilidad**: Los cambios en una capa minimizan el impacto en otras
4. **Escalabilidad**: Las capas pueden escalarse de forma independiente

---

## 2. Estructura de Capas

### 2.1 Capa HTTP (`app/Http/`)

La capa HTTP maneja todas las solicitudes HTTP entrantes y respuestas salientes. Es responsable de:

- Recibir y validar solicitudes HTTP
- Enrutar solicitudes a los controladores apropiados
- Aplicar middleware (autenticacion, autorizacion, logging)
- Transformar respuestas a formato JSON

**Componentes:**

| Componente | Ubicacion | Responsabilidad |
|------------|-----------|-----------------|
| Controllers | `Http/Controllers/` | Manejan solicitudes HTTP, delegan a servicios |
| Middleware | `Http/Middleware/` | Aspectos transversales (auth, logging) |
| Requests | `Http/Requests/` | Validacion de entrada y autorizacion |
| Resources | `Http/Resources/` | Transformacion de respuestas (JSON API Resources) |
| Responses | `Http/Responses/` | Formatos de respuesta estandarizados |

### 2.2 Capa de Aplicacion (`app/Application/`)

La capa de aplicacion contiene los casos de uso del sistema. Orquesta el flujo de datos entre la capa HTTP y la capa de dominio.

**Componentes:**

| Componente | Patron | Responsabilidad |
|------------|--------|-----------------|
| QueryServices | CQRS (Query) | Operaciones de lectura, recuperacion de datos |
| CommandServices | CQRS (Command) | Operaciones de escritura, mutaciones de estado |
| Otros Services | Service | Logica de aplicacion transversal |

**Estructura por modulo:**
```
Application/
├── {Modulo}/
│   └── Services/
│       ├── {Modulo}QueryService.php
│       └── {Modulo}CommandService.php
```

### 2.3 Capa de Dominio (`app/Domain/`)

La capa de dominio contiene las reglas de negocio y entidades. Es el nucleo de la aplicacion y no tiene dependencias de frameworks externos.

**Componentes:**

| Componente | Ubicacion | Responsabilidad |
|------------|-----------|-----------------|
| Contracts | `Domain/{Modulo}/Contracts/` | Interfaces de repositorios |
| DTOs | `Domain/{Modulo}/DTOs/` | Objetos de transferencia de datos |
| Exceptions | `Domain/{Modulo}/Exceptions/` | Excepciones especificas del dominio |

**Estructura por modulo:**
```
Domain/
├── {Modulo}/
│   ├── Contracts/
│   │   └── {Modulo}RepositoryInterface.php
│   ├── DTOs/
│   │   ├── Create{Modulo}Dto.php
│   │   └── Update{Modulo}Dto.php
│   └── Exceptions/
│       └── {Modulo}NotFoundException.php
```

### 2.4 Capa de Infraestructura (`app/Infrastructure/`)

La capa de infraestructura contiene las implementaciones de las interfaces del dominio e integraciones con servicios externos.

**Componentes:**

| Componente | Ubicacion | Responsabilidad |
|------------|-----------|-----------------|
| Repositories | `Infrastructure/Persistence/Repositories/` | Implementaciones de acceso a datos |

### 2.5 Capa de Modelos (`app/Models/`)

Modelos ORM de Eloquent que representan las tablas de la base de datos. Son utilizados por los repositorios para interactuar con la base de datos.

---

## 3. Interaccion entre Capas

### Flujo de una Solicitud

```
Solicitud HTTP
     |
     v
+----+----+
|Middleware| --> Autenticacion, Autorizacion, Request ID
+----+----+
     |
     v
+----+----+
|Controller| --> Recibe solicitud, valida entrada
+----+----+
     |
     v
+----+----+
| Service  | --> Orquestacion de logica de negocio
+----+----+
     |
     v
+----+------+
|Repository | --> Acceso a datos (via interface)
+----+------+
     |
     v
+----+----+
|  Model   | --> Eloquent ORM
+----+----+
     |
     v
+----+------+
| Database  |
+-----------+
```

### Direccion de Dependencias

Las dependencias fluyen hacia adentro, siguiendo el Principio de Inversion de Dependencias:

```
Capa HTTP --> Capa Aplicacion --> Capa Dominio <-- Capa Infraestructura
```

- **Capa HTTP** depende de **Capa de Aplicacion**
- **Capa de Aplicacion** depende de **Capa de Dominio** (interfaces)
- **Capa de Infraestructura** implementa interfaces de **Capa de Dominio**
- **Capa de Dominio** no tiene dependencias externas

### Ejemplo: Crear un Vino

```php
// 1. El Controller recibe la solicitud
class WineAdminController
{
    public function store(StoreWineRequest $request)
    {
        $dto = CreateWineDto::fromRequest($request);
        $wine = $this->commandService->create($dto);
        return WineResource::make($wine);
    }
}

// 2. El Service orquesta la logica de negocio
class WineCommandService
{
    public function create(CreateWineDto $dto): Wine
    {
        return DB::transaction(function () use ($dto) {
            return $this->repository->create($dto->toArray());
        });
    }
}

// 3. El Repository maneja la persistencia
class WineRepository implements WineRepositoryInterface
{
    public function create(array $data): Wine
    {
        return Wine::create($data);
    }
}
```

---

## 4. Patrones de Diseno

### 4.1 Patron Repository

Abstrae la logica de acceso a datos detras de interfaces, permitiendo:
- Testabilidad mediante implementaciones mock
- Flexibilidad para cambiar fuentes de datos
- Separacion de responsabilidades

```php
// Interface (Capa de Dominio)
interface WineRepositoryInterface
{
    public function findById(string $id): ?Wine;
    public function create(array $data): Wine;
}

// Implementacion (Capa de Infraestructura)
class WineRepository implements WineRepositoryInterface
{
    public function findById(string $id): ?Wine
    {
        return Wine::find($id);
    }
}
```

### 4.2 CQRS (Command Query Responsibility Segregation)

Separa las operaciones de lectura y escritura en servicios distintos:

- **QueryService**: Operaciones de solo lectura (findById, search, list)
- **CommandService**: Operaciones de escritura (create, update, delete)

Beneficios:
- Clara separacion de responsabilidades
- Rutas de lectura/escritura optimizadas
- Facilidad para escalar independientemente

### 4.3 DTO (Data Transfer Object)

Objetos inmutables para transferir datos entre capas:

```php
readonly class CreateWineDto
{
    public function __construct(
        public string $name,
        public string $wineryId,
        public string $wineType,
        // ...
    ) {}

    public function toArray(): array { /* ... */ }
}
```

### 4.4 Inyeccion de Dependencias

Todas las dependencias se inyectan a traves de constructores y se resuelven mediante el contenedor de servicios de Laravel:

```php
// El Service Provider vincula interfaces a implementaciones
$this->app->bind(WineRepositoryInterface::class, WineRepository::class);

// Los servicios reciben dependencias via constructor
class WineQueryService
{
    public function __construct(
        private readonly WineRepositoryInterface $repository
    ) {}
}
```

---

## 5. Estructura de Directorios

```
backend/app/
├── Application/                    # Capa de Aplicacion (Casos de Uso)
│   ├── Ai/Services/               # Generacion de perfiles con IA
│   ├── Auth/Services/             # Logica de autenticacion
│   ├── Cellar/Services/           # Gestion de bodega personal
│   ├── Event/Services/            # Gestion de eventos
│   ├── Review/Services/           # Gestion de resenas
│   ├── User/Services/             # Gestion de usuarios
│   ├── Wine/Services/             # Gestion de vinos
│   ├── WineRoute/Services/        # Gestion de rutas del vino
│   └── Winery/Services/           # Gestion de bodegas
│
├── Domain/                         # Capa de Dominio (Reglas de Negocio)
│   ├── {Modulo}/
│   │   ├── Contracts/             # Interfaces de repositorios
│   │   ├── DTOs/                  # Objetos de transferencia
│   │   └── Exceptions/            # Excepciones de dominio
│
├── Http/                           # Capa HTTP (Presentacion)
│   ├── Controllers/
│   │   └── Admin/                 # Controladores de administracion
│   ├── Middleware/
│   ├── Requests/                  # Validacion de formularios
│   ├── Resources/                 # Transformadores de respuesta
│   └── Responses/
│
├── Infrastructure/                 # Capa de Infraestructura
│   └── Persistence/
│       └── Repositories/          # Implementaciones de repositorios
│
├── Models/                         # Modelos Eloquent ORM
├── Providers/                      # Proveedores de servicios
├── Exceptions/                     # Manejo global de excepciones
└── Utils/                          # Utilidades
```

---

## 6. Modulos de Dominio

### 6.1 Modulo Wine (Vinos)

Gestiona el catalogo de vinos y operaciones relacionadas.

| Componente | Archivos |
|------------|----------|
| Services | `WineQueryService`, `WineCommandService`, `WineComparisonService` |
| Repository | `WineRepository` |
| DTOs | `CreateWineDto`, `UpdateWineDto` |
| Models | `Wine`, `WineAiProfile`, `WineComparison` |

### 6.2 Modulo Winery (Bodegas)

Gestiona informacion de bodegas y ubicaciones.

| Componente | Archivos |
|------------|----------|
| Services | `WineryQueryService`, `WineryCommandService` |
| Repository | `WineryRepository` |
| DTOs | `CreateWineryDto`, `UpdateWineryDto` |
| Model | `Winery` |

### 6.3 Modulo Event (Eventos)

Gestiona eventos relacionados con el vino.

| Componente | Archivos |
|------------|----------|
| Services | `EventQueryService`, `EventCommandService` |
| Repository | `EventRepository` |
| DTOs | `CreateEventDto`, `UpdateEventDto` |
| Model | `Event` |

### 6.4 Modulo Review (Resenas)

Gestiona resenas de vinos, comentarios y likes.

| Componente | Archivos |
|------------|----------|
| Services | `ReviewQueryService`, `ReviewCommandService` |
| Repository | `ReviewRepository` |
| DTOs | `CreateReviewDto`, `UpdateReviewDto` |
| Models | `Review`, `ReviewComment`, `ReviewLike` |

### 6.5 Modulo User (Usuarios)

Gestiona cuentas de usuario y perfiles.

| Componente | Archivos |
|------------|----------|
| Services | `UserQueryService`, `UserCommandService` |
| Repository | `UserRepository` |
| DTOs | `CreateUserDto`, `UpdateUserDto` |
| Models | `User`, `Role` |

### 6.6 Modulo Cellar (Bodega Personal)

Gestiona las bodegas personales de los usuarios.

| Componente | Archivos |
|------------|----------|
| Services | `CellarQueryService`, `CellarCommandService` |
| Repository | `CellarRepository` |
| Model | `CellarWine` |

### 6.7 Modulo WineRoute (Rutas del Vino)

Gestiona rutas de turismo enologico.

| Componente | Archivos |
|------------|----------|
| Services | `WineRouteQueryService`, `WineRouteCommandService` |
| Repository | `WineRouteRepository` |
| DTOs | `CreateWineRouteDto`, `UpdateWineRouteDto` |
| Models | `WineRoute`, `WineRouteStop` |

### 6.8 Modulo Auth (Autenticacion)

Maneja autenticacion y gestion de tokens.

| Componente | Archivos |
|------------|----------|
| Services | `AuthService` |
| Model | `RefreshToken` |

### 6.9 Modulo AI (Inteligencia Artificial)

Maneja funcionalidades con IA.

| Componente | Archivos |
|------------|----------|
| Services | `AiProfileService` |

---

## 7. Esquema de Base de Datos

### Diagrama de Entidad-Relacion

```
+-------------+       +-------------+       +-------------+
|   users     |       |    roles    |       | user_roles  |
+-------------+       +-------------+       +-------------+
| id (PK)     |<----->| id (PK)     |<----->| user_id(FK) |
| username    |       | name        |       | role_id(FK) |
| email       |       | description |       +-------------+
| password    |       +-------------+
| name        |
+------+------+
       |
       | 1:N
       v
+-------------+       +-------------+       +-------------+
|   reviews   |       |   wines     |       |  wineries   |
+-------------+       +-------------+       +-------------+
| id (PK)     |       | id (PK)     |<------| id (PK)     |
| user_id(FK) |------>| winery_id   |       | name        |
| wine_id(FK) |       | name        |       | region      |
| rating      |       | wine_type   |       | country     |
| comment     |       | vintage     |       | latitude    |
+-------------+       +-------------+       | longitude   |
                                            +-------------+
```

### Tablas Principales

| Tabla | Descripcion |
|-------|-------------|
| `users` | Cuentas de usuario |
| `roles` | Roles de usuario (ROLE_USER, ROLE_ADMIN, etc.) |
| `wines` | Catalogo de vinos |
| `wineries` | Informacion de bodegas |
| `reviews` | Resenas de vinos |
| `review_comments` | Comentarios en resenas |
| `review_likes` | Likes de resenas |
| `events` | Eventos de vino |
| `wine_routes` | Rutas de turismo enologico |
| `wine_route_stops` | Paradas dentro de rutas |
| `cellar_wines` | Entradas de bodega personal |
| `refresh_tokens` | Tokens de refresco JWT |
| `wine_comparisons` | Cache de comparaciones IA |
| `wine_ai_profiles` | Cache de perfiles IA |

---

## 8. Autenticacion y Autorizacion

### Autenticacion JWT

La API utiliza JWT (JSON Web Tokens) para autenticacion sin estado.

**Flujo:**
1. El usuario inicia sesion con credenciales
2. El servidor emite un token de acceso (corta duracion) y un token de refresco (larga duracion)
3. El cliente incluye el token de acceso en el header `Authorization: Bearer <token>`
4. El servidor valida el token en cada solicitud

### Middleware

| Middleware | Proposito |
|------------|-----------|
| `jwt.auth` | Requiere token JWT valido |
| `jwt.optional` | Parsea token si esta presente, continua si no |
| `role:ROLE_NAME` | Requiere rol especifico |

### Roles

| Rol | Permisos |
|-----|----------|
| `ROLE_USER` | Acceso basico de usuario |
| `ROLE_MODERATOR` | Moderacion de contenido |
| `ROLE_PARTNER` | Gestion de eventos propios |
| `ROLE_ADMIN` | Acceso administrativo completo |

---

## 9. Manejo de Errores

### Formato de Respuesta de Error

Todos los errores siguen una estructura JSON consistente:

```json
{
    "timestamp": "2026-03-01T12:00:00.000Z",
    "path": "/api/v1/wines/123",
    "requestId": "abc-123-def",
    "status": 404,
    "code": "E1002",
    "message": "Recurso no encontrado",
    "details": []
}
```

### Codigos de Error

| Codigo | HTTP Status | Descripcion |
|--------|-------------|-------------|
| E1000 | 500 | Error interno del servidor |
| E1001 | 422 | Validacion fallida |
| E1002 | 404 | Recurso no encontrado |
| E2001 | 401 | Credenciales invalidas |
| E2002 | 401 | Token invalido/expirado |
| E2003 | 403 | Cuenta deshabilitada |
| E2004 | 401 | Autenticacion requerida |
| E2005 | 403 | Prohibido |
| E3001 | 409 | Nombre de usuario ya existe |
| E3002 | 409 | Email ya registrado |

---

## 10. Instalacion

### Requisitos Previos

- PHP 8.3+
- Composer 2.x
- PostgreSQL 15+

### Pasos de Instalacion

1. **Clonar el repositorio:**
```bash
git clone <repository-url>
cd rewinelaravel/backend
```

2. **Instalar dependencias PHP:**
```bash
composer install
```

3. **Copiar archivo de entorno:**
```bash
cp .env.example .env
```

4. **Configurar variables de entorno:**
```bash
# Editar .env con tus credenciales de base de datos
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=rewine
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

5. **Generar clave de aplicacion:**
```bash
php artisan key:generate
```

6. **Generar secreto JWT:**
```bash
php artisan jwt:secret
```

7. **Ejecutar migraciones:**
```bash
php artisan migrate
```

8. **Ejecutar seeders (opcional):**
```bash
php artisan db:seed
```

9. **Iniciar servidor de desarrollo:**
```bash
php artisan serve
```

La API estara disponible en `http://localhost:8000`.

---

## 11. Comandos Utiles

### Comandos Artisan

```bash
# Iniciar servidor de desarrollo
php artisan serve

# Listar todas las rutas
php artisan route:list

# Listar rutas filtradas por path
php artisan route:list --path=api/v1/wines

# Limpiar todos los caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar para produccion
php artisan optimize

# Ejecutar migraciones
php artisan migrate

# Revertir ultima migracion
php artisan migrate:rollback

# Resetear y re-ejecutar todas las migraciones
php artisan migrate:fresh

# Ejecutar seeders
php artisan db:seed

# Ejecutar seeder especifico
php artisan db:seed --class=WineSeeder

# Crear nueva migracion
php artisan make:migration create_table_name

# Crear nuevo modelo con migracion
php artisan make:model ModelName -m

# Crear nuevo controlador
php artisan make:controller ControllerName

# Crear nuevo request
php artisan make:request RequestName

# Crear nuevo resource
php artisan make:resource ResourceName

# Shell interactivo (Tinker)
php artisan tinker
```

### Comandos Composer

```bash
# Instalar dependencias
composer install

# Actualizar dependencias
composer update

# Regenerar autoload
composer dump-autoload

# Verificar paquetes desactualizados
composer outdated
```

### Comandos de Base de Datos

```bash
# Conectar a PostgreSQL
psql -U username -d rewine

# Backup de base de datos
pg_dump -U username rewine > backup.sql

# Restaurar base de datos
psql -U username rewine < backup.sql
```

---

## 12. Testing

### Ejecutar Tests

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar con coverage
php artisan test --coverage

# Ejecutar archivo de test especifico
php artisan test tests/Feature/WineTest.php

# Ejecutar metodo de test especifico
php artisan test --filter=test_can_create_wine

# Ejecutar tests en paralelo
php artisan test --parallel
```

### Estructura de Tests

```
tests/
├── Feature/                # Tests de integracion
│   ├── AuthTest.php
│   ├── WineTest.php
│   └── ...
├── Unit/                   # Tests unitarios
│   ├── Services/
│   └── ...
└── TestCase.php           # Clase base de tests
```

### Escribir Tests

```php
class WineTest extends TestCase
{
    public function test_can_list_wines(): void
    {
        $response = $this->getJson('/api/v1/wines');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'items',
                'totalItems',
                'pageNumber',
            ]);
    }
}
```

---

## 13. Configuracion

### Archivos de Configuracion Principales

| Archivo | Proposito |
|---------|-----------|
| `config/app.php` | Configuracion de la aplicacion |
| `config/database.php` | Conexiones de base de datos |
| `config/cors.php` | Configuracion de CORS |
| `config/jwt.php` | Configuracion de autenticacion JWT |
| `config/logging.php` | Configuracion de logging |

### Variables de Entorno

| Variable | Descripcion | Por defecto |
|----------|-------------|-------------|
| `APP_ENV` | Entorno (local, production) | local |
| `APP_DEBUG` | Modo debug | true |
| `APP_URL` | URL de la aplicacion | http://localhost |
| `DB_CONNECTION` | Driver de base de datos | pgsql |
| `DB_HOST` | Host de base de datos | 127.0.0.1 |
| `DB_PORT` | Puerto de base de datos | 5432 |
| `DB_DATABASE` | Nombre de base de datos | rewine |
| `JWT_SECRET` | Clave de firma JWT | - |
| `JWT_TTL` | TTL del token de acceso (minutos) | 15 |
| `JWT_REFRESH_TTL` | TTL del token de refresco (minutos) | 10080 |
| `GOOGLE_MAPS_API_KEY` | Clave API de Google Maps | - |
| `OPENAI_API_KEY` | Clave API de OpenAI | - |

---

## 14. Servicios Externos

### 14.1 OpenAI API

Se utiliza para generar perfiles de vinos y comparaciones.

**Configuracion:**
```bash
OPENAI_API_KEY=sk-tu-api-key
```

**Funcionalidades:**
- Generacion de perfiles detallados de vinos
- Comparacion inteligente entre dos vinos
- Recomendaciones de maridaje

**Comportamiento:**
- Si no hay API key configurada, el sistema usa respuestas mock
- Los perfiles generados se cachean por vino e idioma

### 14.2 Google Maps API

Se utiliza para visualizacion de ubicaciones y rutas.

**Configuracion:**
```bash
GOOGLE_MAPS_API_KEY=AIza...
```

**APIs utilizadas:**
- Maps Embed API (visualizacion de mapas)
- Directions API (calculo de rutas)
- Geocoding API (conversion de direcciones)

**Comportamiento:**
- Si no hay API key, se muestran placeholders
- Las ubicaciones de bodegas se seleccionan en mapa al crear/editar

---

## Referencias

- [Documentacion de Laravel](https://laravel.com/docs)
- [Clean Architecture - Robert C. Martin](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
- [Domain-Driven Design](https://martinfowler.com/bliki/DomainDrivenDesign.html)
- [Patron CQRS](https://martinfowler.com/bliki/CQRS.html)

---

Ultima actualizacion: Marzo 2026


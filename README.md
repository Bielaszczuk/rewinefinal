# Rewine

Rewine es una plataforma web para amantes del vino que permite descubrir, explorar y gestionar colecciones de vinos, eventos y rutas enologicas.

---

## Tabla de Contenidos

1. [Descripcion General](#descripcion-general)
2. [Stack Tecnologico](#stack-tecnologico)
3. [Requisitos Previos](#requisitos-previos)
4. [Instalacion y Configuracion](#instalacion-y-configuracion)
5. [Comandos Utiles](#comandos-utiles)
6. [Estructura del Proyecto](#estructura-del-proyecto)
7. [Documentacion](#documentacion)
8. [Flujo de Comunicacion Frontend-Backend](#flujo-de-comunicacion-frontend-backend)
9. [Usuarios de Prueba](#usuarios-de-prueba)
10. [Variables de Entorno](#variables-de-entorno)

---

## Descripcion General

Rewine ofrece las siguientes funcionalidades:

- Catalogo de vinos con busqueda y filtros avanzados
- Sistema de resenas y calificaciones
- Bodega virtual personal para cada usuario
- Eventos relacionados con el vino
- Rutas del vino con integracion de Google Maps
- Comparacion de vinos con asistencia de IA
- Perfiles de vino generados por IA
- Panel de administracion completo
- Sistema de roles (Usuario, Partner, Moderador, Admin)

---

## Stack Tecnologico

### Frontend

| Tecnologia | Version | Descripcion |
|------------|---------|-------------|
| Vue.js | 3.x | Framework de UI |
| TypeScript | 5.x | Tipado estatico |
| Vite | 5.x | Build tool |
| Pinia | 2.x | State management |
| Tailwind CSS | 3.x | Framework CSS |
| Vue Router | 4.x | Enrutamiento |
| Axios | 1.x | Cliente HTTP |

### Backend

| Tecnologia | Version | Descripcion |
|------------|---------|-------------|
| Laravel | 11.x | Framework PHP |
| PHP | 8.3+ | Lenguaje |
| PostgreSQL | 15+ | Base de datos |
| JWT Auth | 2.x | Autenticacion |

### Servicios Externos

| Servicio | Uso |
|----------|-----|
| OpenAI API | Generacion de perfiles y comparaciones de vinos |
| Google Maps API | Visualizacion de ubicaciones y rutas |

---

## Requisitos Previos

- PHP 8.3 o superior
- Composer 2.x
- Node.js 18 o superior
- npm 9 o superior
- PostgreSQL 15 o superior

---

## Instalacion y Configuracion

### 1. Clonar el repositorio

```bash
git clone <repository-url>
cd rewinelaravel
```

### 2. Configurar el Backend

```bash
cd backend

# Instalar dependencias
composer install

# Copiar archivo de entorno
cp .env.example .env

# Configurar variables de entorno en .env
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=rewine
# DB_USERNAME=tu_usuario
# DB_PASSWORD=tu_password

# Generar clave de aplicacion
php artisan key:generate

# Generar secreto JWT
php artisan jwt:secret

# Ejecutar migraciones
php artisan migrate

# (Opcional) Ejecutar seeders
php artisan db:seed

# Iniciar servidor
php artisan serve
```

El backend estara disponible en `http://localhost:8000`

### 3. Configurar el Frontend

```bash
cd frontend

# Instalar dependencias
npm install

# Copiar archivo de entorno
cp .env.example .env.local

# Configurar variables de entorno en .env.local
# VITE_API_BASE_URL=http://localhost:8000/api/v1
# VITE_MOCK_API=false

# Iniciar servidor de desarrollo
npm run dev
```

El frontend estara disponible en `http://localhost:5173`

---

## Comandos Utiles

### Backend (Laravel)

```bash
# Iniciar servidor de desarrollo
php artisan serve

# Listar todas las rutas
php artisan route:list

# Listar rutas filtradas
php artisan route:list --path=api/v1/wines

# Ejecutar migraciones
php artisan migrate

# Revertir ultima migracion
php artisan migrate:rollback

# Resetear y re-ejecutar migraciones
php artisan migrate:fresh

# Ejecutar seeders
php artisan db:seed

# Limpiar caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Ejecutar tests
php artisan test

# Consola interactiva
php artisan tinker
```

### Frontend (Vue/Vite)

```bash
# Iniciar servidor de desarrollo
npm run dev

# Build para produccion
npm run build

# Preview del build
npm run preview

# Ejecutar tests unitarios
npm run test

# Ejecutar tests con coverage
npm run test:coverage

# Ejecutar tests e2e (Playwright)
npm run test:e2e

# Linting
npm run lint

# Type checking
npm run type-check
```

---

## Estructura del Proyecto

```
rewinelaravel/
|-- backend/                    # API Laravel
|   |-- app/
|   |   |-- Application/       # Capa de aplicacion (servicios)
|   |   |-- Domain/            # Capa de dominio (contratos, DTOs)
|   |   |-- Http/              # Capa HTTP (controllers, middleware)
|   |   |-- Infrastructure/    # Capa de infraestructura (repositorios)
|   |   +-- Models/            # Modelos Eloquent
|   |-- config/                # Configuracion
|   |-- database/              # Migraciones y seeders
|   |-- routes/                # Definicion de rutas
|   +-- ARCHITECTURE.md        # Documentacion de arquitectura
|
|-- frontend/                   # SPA Vue.js
|   |-- src/
|   |   |-- api/               # Clientes API y DTOs
|   |   |-- app/               # Bootstrap de la aplicacion
|   |   |-- components/        # Componentes Vue
|   |   |-- composables/       # Composition functions
|   |   |-- config/            # Configuracion
|   |   |-- domain/            # Tipos y mappers de dominio
|   |   |-- i18n/              # Internacionalizacion
|   |   |-- pages/             # Paginas/vistas
|   |   |-- services/          # Servicios de aplicacion
|   |   |-- stores/            # Stores Pinia
|   |   +-- utils/             # Utilidades
|   +-- ARCHITECTURE.md        # Documentacion de arquitectura
|
|-- docs/                       # Documentacion
|   +-- MANUAL_USUARIO.md      # Manual de usuario
|
+-- README.md                   # Este archivo
```

---

## Documentacion

| Documento | Descripcion |
|-----------|-------------|
| [Arquitectura Backend](backend/ARCHITECTURE.md) | Arquitectura completa del backend, capas, patrones, comandos |
| [Arquitectura Frontend](frontend/ARCHITECTURE.md) | Arquitectura completa del frontend, capas, patrones, estructura |
| [Manual de Usuario](docs/MANUAL_USUARIO.md) | Guia completa para usuarios de la plataforma |

---

## Flujo de Comunicacion Frontend-Backend

### Diagrama General

```
+------------------+          HTTP/REST           +------------------+
|                  |         JSON + JWT           |                  |
|     FRONTEND     | <------------------------->  |     BACKEND      |
|     (Vue.js)     |                              |    (Laravel)     |
|                  |                              |                  |
|  localhost:5173  |                              |  localhost:8000  |
+------------------+                              +------------------+
        |                                                  |
        v                                                  v
+------------------+                              +------------------+
|   Pinia Stores   |                              |   PostgreSQL     |
|   (Estado)       |                              |   (Base Datos)   |
+------------------+                              +------------------+
```

### Flujo de una Solicitud Completa

El siguiente diagrama muestra el flujo completo cuando un usuario realiza una accion (ejemplo: obtener lista de vinos):

```
FRONTEND                                              BACKEND
--------                                              -------

1. Usuario navega a /wines
         |
         v
2. WinesPage.vue
   - onMounted() llama a store
         |
         v
3. wines.store.ts
   - fetchWines() action
   - Muestra loading state
         |
         v
4. wines.service.ts
   - getWines(filters)
   - Prepara parametros
         |
         v
5. wines.client.ts                    
   - GET /api/v1/wines ------------------>  6. routes/api.php
   - Headers: Authorization: Bearer JWT        - Enruta a WineController
         |                                            |
         |                                            v
         |                                     7. JwtOptionalMiddleware
         |                                        - Valida token (si existe)
         |                                            |
         |                                            v
         |                                     8. WineController@index
         |                                        - Recibe request
         |                                            |
         |                                            v
         |                                     9. WineQueryService
         |                                        - Aplica filtros
         |                                            |
         |                                            v
         |                                     10. WineRepository
         |                                         - Query a PostgreSQL
         |                                            |
         |                                            v
         |                                     11. Eloquent Models
         |                                         - Retorna coleccion
         |                                            |
         |                                            v
         |                                     12. WineResource
         |                                         - Transforma a JSON
         |                                            |
         |  <----------------------------------  13. Response JSON
         |      { items: [...], totalItems: N }
         v
14. wine.mapper.ts
    - Transforma DTO a tipos de dominio
         |
         v
15. wines.store.ts
    - Actualiza state con vinos
    - loading = false
         |
         v
16. WinesPage.vue
    - Renderiza lista de vinos
         |
         v
17. Usuario ve los vinos
```

### Flujo de Autenticacion

```
FRONTEND                                              BACKEND
--------                                              -------

1. Usuario ingresa credenciales
   en LoginPage.vue
         |
         v
2. auth.store.ts
   - login(email, password)
         |
         v
3. auth.client.ts
   - POST /api/v1/auth/login ------------>  4. AuthController@login
     { email, password }                       - Valida credenciales
                                               - Genera JWT tokens
         |                                            |
         |  <----------------------------------  5. Response
         |      { accessToken, refreshToken,
         |        user: {...} }
         v
6. auth.store.ts
   - Guarda tokens en localStorage
   - Guarda usuario en state
   - Configura header Authorization
         |
         v
7. Router redirige a pagina protegida
         |
         v
8. Solicitudes posteriores incluyen
   Authorization: Bearer <accessToken>
```

### Flujo de Refresco de Token

```
FRONTEND                                              BACKEND
--------                                              -------

1. Solicitud con token expirado
         |
         v
2. Backend retorna 401
         |  <----------------------------------  { status: 401, code: "E2002" }
         v
3. Axios interceptor detecta 401
         |
         v
4. auth.client.ts
   - POST /api/v1/auth/refresh ----------->  5. AuthController@refresh
     { refreshToken }                           - Valida refresh token
                                                - Genera nuevo access token
         |                                            |
         |  <----------------------------------  6. Response
         |      { accessToken }
         v
7. auth.store.ts
   - Actualiza accessToken
         |
         v
8. Reintenta solicitud original
   con nuevo token
```

### Estructura de Comunicacion por Capas

```
FRONTEND                          |                    BACKEND
                                  |
+------------------+              |              +------------------+
|      Pages       |              |              |   Controllers    |
|   (Componentes)  |              |              |    (HTTP)        |
+--------+---------+              |              +--------+---------+
         |                        |                       |
         v                        |                       v
+------------------+              |              +------------------+
|      Stores      |              |              |    Services      |
|     (Pinia)      |              |              |  (Application)   |
+--------+---------+              |              +--------+---------+
         |                        |                       |
         v                        |                       v
+------------------+              |              +------------------+
|     Services     |              |              |   Repositories   |
|   (Orquestacion) |              |              | (Infrastructure) |
+--------+---------+              |              +--------+---------+
         |                        |                       |
         v                        |                       v
+------------------+   HTTP/JSON  |              +------------------+
|   API Clients    | <----------->|              |     Models       |
|     (Axios)      |   REST API   |              |   (Eloquent)     |
+------------------+              |              +------------------+
                                  |                       |
                                  |                       v
                                  |              +------------------+
                                  |              |   PostgreSQL     |
                                  |              +------------------+
```

### Endpoints Principales

| Metodo | Endpoint | Descripcion |
|--------|----------|-------------|
| POST | `/api/v1/auth/login` | Iniciar sesion |
| POST | `/api/v1/auth/register` | Registrar usuario |
| POST | `/api/v1/auth/refresh` | Refrescar token |
| GET | `/api/v1/wines` | Listar vinos |
| GET | `/api/v1/wines/{id}` | Detalle de vino |
| GET | `/api/v1/wineries` | Listar bodegas |
| GET | `/api/v1/events` | Listar eventos |
| GET | `/api/v1/routes` | Listar rutas del vino |
| GET | `/api/v1/wines/{id}/reviews` | Resenas de un vino |
| POST | `/api/v1/wines/{id}/reviews` | Crear resena |
| GET | `/api/v1/cellar` | Mi bodega personal |
| POST | `/api/v1/wines/compare` | Comparar vinos (IA) |
| POST | `/api/v1/wines/{id}/ai-profile` | Generar perfil IA |

### Formato de Respuestas

**Respuesta exitosa (lista paginada):**
```json
{
  "items": [...],
  "pageNumber": 0,
  "pageSize": 20,
  "totalItems": 150,
  "totalPages": 8,
  "hasNext": true,
  "hasPrevious": false
}
```

**Respuesta exitosa (entidad):**
```json
{
  "id": "uuid",
  "name": "Malbec Reserve",
  "winery": { "id": "uuid", "name": "Bodega X" },
  "createdAt": "2026-03-01T12:00:00Z"
}
```

**Respuesta de error:**
```json
{
  "timestamp": "2026-03-01T12:00:00Z",
  "path": "/api/v1/wines/123",
  "status": 404,
  "code": "E1002",
  "message": "Recurso no encontrado",
  "details": []
}
```

---

## Usuarios de Prueba

Los siguientes usuarios estan disponibles en el entorno de desarrollo local:

| Email | Password | Rol |
|-------|----------|-----|
| admin@rewine.local | Rewine123! | Administrador |
| moderator@rewine.local | Rewine123! | Moderador |
| partner@rewine.local | Rewine123! | Partner |
| user@rewine.local | Rewine123! | Usuario |

IMPORTANTE: Estas credenciales son solo para desarrollo local y no estan presentes en produccion.

---

## Variables de Entorno

### Backend (.env)

```bash
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=rewine
DB_USERNAME=rewine
DB_PASSWORD=secret

JWT_SECRET=your-jwt-secret-minimum-32-characters
JWT_TTL=15
JWT_REFRESH_TTL=10080

# Servicios externos (opcionales)
GOOGLE_MAPS_API_KEY=your-google-maps-api-key
OPENAI_API_KEY=your-openai-api-key
```

### Frontend (.env.local)

```bash
VITE_API_BASE_URL=http://localhost:8000/api/v1
VITE_MOCK_API=false
VITE_APP_ENV=development
VITE_GOOGLE_MAPS_API_KEY=your-google-maps-api-key
```

---

## Licencia

Proyecto privado. Todos los derechos reservados.


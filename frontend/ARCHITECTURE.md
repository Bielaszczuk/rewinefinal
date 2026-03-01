# Arquitectura del Frontend

Este documento proporciona una vision completa de la arquitectura del frontend de Rewine, cubriendo la estructura de capas, patrones de diseno, flujo de datos y guias de desarrollo.

---

## Tabla de Contenidos

1. [Vision General](#1-vision-general)
2. [Principios de Arquitectura](#2-principios-de-arquitectura)
3. [Estructura del Proyecto](#3-estructura-del-proyecto)
4. [Responsabilidades de Capas](#4-responsabilidades-de-capas)
5. [Flujo de Datos](#5-flujo-de-datos)
6. [Gestion de Estado](#6-gestion-de-estado)
7. [Sistema de Componentes](#7-sistema-de-componentes)
8. [Enrutamiento](#8-enrutamiento)
9. [Internacionalizacion](#9-internacionalizacion)
10. [Instalacion](#10-instalacion)
11. [Comandos Utiles](#11-comandos-utiles)
12. [Testing](#12-testing)
13. [Configuracion](#13-configuracion)

---

## 1. Vision General

El frontend de Rewine es una Single Page Application (SPA) construida con Vue.js 3 y TypeScript. Sigue una arquitectura en capas inspirada en Clean Architecture, con clara separacion de responsabilidades.

### Stack Tecnologico

| Tecnologia | Version | Proposito |
|------------|---------|-----------|
| Vue.js | 3.x | Framework de UI reactivo |
| TypeScript | 5.x | Tipado estatico |
| Vite | 5.x | Build tool y dev server |
| Pinia | 2.x | Gestion de estado |
| Vue Router | 4.x | Enrutamiento SPA |
| Tailwind CSS | 3.x | Framework de estilos |
| Axios | 1.x | Cliente HTTP |
| Vue I18n | 9.x | Internacionalizacion |

### Diagrama de Arquitectura

```
+------------------------------------------------------------------+
|                         CAPA DE PRESENTACION                      |
|  +----------+  +------------+  +------------+  +--------------+  |
|  |  Pages   |  | Components |  | Composables|  |  Directives  |  |
+------------------------------------------------------------------+
                              |
                              v
+------------------------------------------------------------------+
|                       CAPA DE APLICACION                          |
|  +----------+  +------------+  +------------+                    |
|  |  Stores  |  |  Services  |  |   Router   |                    |
+------------------------------------------------------------------+
                              |
                              v
+------------------------------------------------------------------+
|                        CAPA DE DOMINIO                            |
|  +----------+  +------------+  +------------+                    |
|  |  Types   |  |  Mappers   |  |   Models   |                    |
+------------------------------------------------------------------+
                              |
                              v
+------------------------------------------------------------------+
|                     CAPA DE INFRAESTRUCTURA                       |
|  +----------+  +------------+  +------------+                    |
|  |   API    |  |   Config   |  |   Utils    |                    |
+------------------------------------------------------------------+
```

---

## 2. Principios de Arquitectura

### 2.1 Flujo de Datos Unidireccional

```
Accion del Usuario --> Store --> API --> Actualizacion de Estado --> UI
```

Los datos fluyen en una sola direccion, haciendo el estado predecible y facil de depurar.

### 2.2 Aislamiento de Dominio

La logica de negocio reside en `/domain`, independiente de la UI. Esto permite:
- Reutilizar logica en diferentes componentes
- Testear logica de negocio sin dependencias de UI
- Cambiar la implementacion de UI sin afectar el dominio

### 2.3 Fuente Unica de Verdad

Todo el estado compartido se centraliza en stores de Pinia. Los componentes:
- Leen estado de los stores
- Disparan acciones para modificar estado
- Nunca modifican estado directamente

### 2.4 Inversion de Dependencias

Las paginas dependen de servicios/stores, no de clientes API directamente:

```
Page --> Service --> Store --> API Client
```

---

## 3. Estructura del Proyecto

```
frontend/src/
├── api/                        # Capa de Infraestructura - API
│   ├── clients/               # Clientes HTTP por dominio
│   │   ├── auth.client.ts
│   │   ├── wines.client.ts
│   │   ├── events.client.ts
│   │   ├── wineries.client.ts
│   │   ├── routes.client.ts
│   │   └── reviews.client.ts
│   ├── dtos/                  # Data Transfer Objects
│   │   ├── wine.dto.ts
│   │   ├── event.dto.ts
│   │   └── ...
│   └── index.ts               # Exportaciones publicas
│
├── app/                        # Bootstrap de la aplicacion
│   ├── http.ts                # Configuracion de Axios
│   ├── router.ts              # Configuracion del router
│   └── index.ts
│
├── components/                 # Capa de Presentacion - Componentes
│   ├── common/                # Componentes reutilizables
│   │   ├── BaseButton.vue
│   │   ├── BaseCard.vue
│   │   ├── BaseInput.vue
│   │   ├── BaseModal.vue
│   │   ├── LoadingSpinner.vue
│   │   └── ...
│   ├── layout/                # Componentes de layout
│   │   ├── AppHeader.vue
│   │   ├── AppFooter.vue
│   │   ├── AppShell.vue
│   │   └── AppSidebar.vue
│   ├── wines/                 # Componentes de vinos
│   │   ├── WineCard.vue
│   │   ├── WineFilters.vue
│   │   ├── WineGrid.vue
│   │   ├── ReviewsList.vue
│   │   └── ReviewForm.vue
│   ├── events/                # Componentes de eventos
│   ├── routes/                # Componentes de rutas
│   └── maps/                  # Componentes de mapas
│       └── MapView.vue
│
├── composables/                # Composition Functions reutilizables
│   ├── useAuth.ts
│   ├── usePagination.ts
│   ├── useForm.ts
│   └── useToast.ts
│
├── config/                     # Configuracion
│   ├── env.ts                 # Variables de entorno
│   └── constants.ts           # Constantes de la aplicacion
│
├── domain/                     # Capa de Dominio
│   ├── types/                 # Tipos de dominio
│   │   ├── wine.types.ts
│   │   ├── event.types.ts
│   │   ├── user.types.ts
│   │   └── ...
│   ├── mappers/               # Transformadores DTO -> Domain
│   │   ├── wine.mapper.ts
│   │   ├── event.mapper.ts
│   │   └── ...
│   └── models/                # Modelos de dominio
│
├── i18n/                       # Internacionalizacion
│   ├── index.ts               # Configuracion de i18n
│   └── locales/
│       ├── es.json            # Traducciones en espanol
│       └── en.json            # Traducciones en ingles
│
├── pages/                      # Capa de Presentacion - Paginas
│   ├── public/                # Paginas publicas
│   │   ├── HomePage.vue
│   │   ├── LoginPage.vue
│   │   ├── RegisterPage.vue
│   │   ├── WinesPage.vue
│   │   ├── WineDetailsPage.vue
│   │   ├── EventsPage.vue
│   │   ├── EventDetailsPage.vue
│   │   ├── WineriesPage.vue
│   │   ├── WineryDetailsPage.vue
│   │   ├── WineRoutesPage.vue
│   │   └── WineRouteDetailsPage.vue
│   ├── user/                  # Paginas de usuario autenticado
│   │   ├── CellarPage.vue
│   │   ├── ProfilePage.vue
│   │   └── WineComparePage.vue
│   ├── admin/                 # Paginas de administracion
│   │   ├── AdminDashboardPage.vue
│   │   ├── AdminWinesPage.vue
│   │   ├── AdminWineriesPage.vue
│   │   ├── AdminEventsPage.vue
│   │   ├── AdminRoutesPage.vue
│   │   ├── AdminUsersPage.vue
│   │   └── AdminReviewsPage.vue
│   └── partner/               # Paginas de partner
│       └── PartnerEventsPage.vue
│
├── services/                   # Capa de Aplicacion - Servicios
│   ├── auth.service.ts
│   ├── wines.service.ts
│   ├── events.service.ts
│   ├── wineries.service.ts
│   ├── routes.service.ts
│   └── reviews.service.ts
│
├── stores/                     # Capa de Aplicacion - Estado
│   ├── auth.store.ts
│   ├── wines.store.ts
│   ├── events.store.ts
│   ├── wineries.store.ts
│   ├── routes.store.ts
│   └── ui.store.ts
│
├── utils/                      # Utilidades
│   ├── date.utils.ts
│   ├── format.utils.ts
│   ├── validation.utils.ts
│   └── storage.utils.ts
│
├── App.vue                     # Componente raiz
└── main.ts                     # Punto de entrada
```

---

## 4. Responsabilidades de Capas

### 4.1 Capa de Presentacion

**Paginas (`pages/`)**
- Representan rutas completas de la aplicacion
- Componen componentes para formar vistas
- Conectan con stores para obtener/modificar estado
- Manejan logica de nivel de pagina

**Componentes (`components/`)**
- Unidades de UI reutilizables
- Reciben datos via props
- Emiten eventos para comunicacion con padres
- No acceden directamente a stores (preferiblemente)

**Composables (`composables/`)**
- Encapsulan logica reutilizable con Composition API
- Manejan efectos secundarios
- Proporcionan estado reactivo local

### 4.2 Capa de Aplicacion

**Stores (`stores/`)**
- Centralizan estado de la aplicacion
- Exponen acciones para modificar estado
- Manejan cache y sincronizacion con API
- Usan Pinia para reactividad

**Services (`services/`)**
- Orquestan llamadas a API
- Transforman datos entre capas
- Manejan logica de negocio de aplicacion

**Router (`app/router.ts`)**
- Define rutas de la aplicacion
- Configura guards de navegacion
- Maneja lazy loading de paginas

### 4.3 Capa de Dominio

**Types (`domain/types/`)**
- Definen estructuras de datos del dominio
- Son independientes de la implementacion
- Representan conceptos del negocio

**Mappers (`domain/mappers/`)**
- Transforman DTOs de API a tipos de dominio
- Transforman tipos de dominio a DTOs
- Manejan diferencias de nomenclatura (snake_case vs camelCase)

### 4.4 Capa de Infraestructura

**API Clients (`api/clients/`)**
- Realizan llamadas HTTP al backend
- Definen endpoints y parametros
- Manejan serializacion/deserializacion

**DTOs (`api/dtos/`)**
- Representan estructura de datos de la API
- Mapean directamente a respuestas JSON
- Usan nomenclatura del backend

**Config (`config/`)**
- Variables de entorno
- Constantes de configuracion

---

## 5. Flujo de Datos

### 5.1 Flujo de Lectura

```
1. Usuario navega a una pagina
2. La pagina solicita datos al store
3. El store verifica cache
4. Si no hay cache, el store llama al service
5. El service llama al API client
6. El API client hace request HTTP
7. La respuesta se transforma via mapper
8. El store actualiza su estado
9. Los componentes reactivos se actualizan
```

### 5.2 Flujo de Escritura

```
1. Usuario realiza accion (ej: crear vino)
2. El componente llama accion del store
3. El store llama al service
4. El service llama al API client
5. El API client hace request HTTP
6. En exito, el store actualiza estado
7. Los componentes reactivos se actualizan
8. Se muestra feedback al usuario
```

### 5.3 Ejemplo: Obtener Lista de Vinos

```typescript
// 1. Pagina solicita datos
// WinesPage.vue
onMounted(() => {
  winesStore.fetchWines()
})

// 2. Store maneja la solicitud
// wines.store.ts
async fetchWines() {
  this.loading = true
  try {
    const response = await winesService.getWines(this.filters)
    this.wines = response.items
    this.totalItems = response.totalItems
  } finally {
    this.loading = false
  }
}

// 3. Service llama al cliente API
// wines.service.ts
async getWines(filters: WineFilters): Promise<PaginatedResponse<Wine>> {
  const dto = await winesClient.getWines(filters)
  return {
    items: dto.items.map(wineMapper.toDomain),
    totalItems: dto.totalItems
  }
}

// 4. Cliente hace request HTTP
// wines.client.ts
async getWines(filters: WineFilters): Promise<WineListDto> {
  const response = await http.get('/wines', { params: filters })
  return response.data
}
```

---

## 6. Gestion de Estado

### 6.1 Pinia Stores

Cada store de Pinia sigue una estructura consistente:

```typescript
// wines.store.ts
export const useWinesStore = defineStore('wines', {
  // Estado
  state: () => ({
    wines: [] as Wine[],
    selectedWine: null as Wine | null,
    loading: false,
    error: null as string | null,
    filters: {} as WineFilters,
    pagination: {
      page: 0,
      size: 20,
      totalItems: 0
    }
  }),

  // Getters (computados)
  getters: {
    hasWines: (state) => state.wines.length > 0,
    featuredWines: (state) => state.wines.filter(w => w.isFeatured)
  },

  // Acciones
  actions: {
    async fetchWines() { /* ... */ },
    async fetchWineById(id: string) { /* ... */ },
    async createWine(data: CreateWineData) { /* ... */ },
    async updateWine(id: string, data: UpdateWineData) { /* ... */ },
    setFilters(filters: WineFilters) { /* ... */ },
    clearSelection() { /* ... */ }
  }
})
```

### 6.2 Stores Disponibles

| Store | Responsabilidad |
|-------|-----------------|
| `auth.store` | Autenticacion, usuario actual, tokens |
| `wines.store` | Catalogo de vinos, filtros, paginacion |
| `events.store` | Eventos, filtros, registro |
| `wineries.store` | Bodegas, busqueda |
| `routes.store` | Rutas del vino |
| `ui.store` | Estado de UI (modales, notificaciones) |

### 6.3 Persistencia de Estado

El store de autenticacion persiste tokens en localStorage:

```typescript
// auth.store.ts
actions: {
  setTokens(accessToken: string, refreshToken: string) {
    this.accessToken = accessToken
    this.refreshToken = refreshToken
    localStorage.setItem('accessToken', accessToken)
    localStorage.setItem('refreshToken', refreshToken)
  },
  
  loadFromStorage() {
    this.accessToken = localStorage.getItem('accessToken')
    this.refreshToken = localStorage.getItem('refreshToken')
  }
}
```

---

## 7. Sistema de Componentes

### 7.1 Componentes Base

Los componentes base (`components/common/`) proporcionan elementos UI reutilizables:

| Componente | Proposito |
|------------|-----------|
| `BaseButton` | Boton con variantes y estados |
| `BaseCard` | Contenedor con estilos de tarjeta |
| `BaseInput` | Input con validacion y errores |
| `BaseModal` | Modal con overlay y animaciones |
| `BaseSelect` | Selector con opciones |
| `LoadingSpinner` | Indicador de carga |
| `Pagination` | Controles de paginacion |

### 7.2 Convencion de Props

```typescript
// Usar defineProps con TypeScript
const props = defineProps<{
  // Props requeridas
  title: string
  // Props opcionales con default
  variant?: 'primary' | 'secondary' | 'danger'
  disabled?: boolean
}>()

// Defaults
withDefaults(defineProps<Props>(), {
  variant: 'primary',
  disabled: false
})
```

### 7.3 Convencion de Emits

```typescript
// Definir emits con tipos
const emit = defineEmits<{
  (e: 'click', value: MouseEvent): void
  (e: 'update:modelValue', value: string): void
}>()
```

### 7.4 Componentes de Layout

| Componente | Proposito |
|------------|-----------|
| `AppShell` | Layout principal con header/footer |
| `AppHeader` | Barra de navegacion superior |
| `AppFooter` | Pie de pagina |
| `AppSidebar` | Menu lateral (admin) |

---

## 8. Enrutamiento

### 8.1 Estructura de Rutas

```typescript
// app/router.ts
const routes = [
  // Rutas publicas
  { path: '/', component: () => import('@/pages/public/HomePage.vue') },
  { path: '/wines', component: () => import('@/pages/public/WinesPage.vue') },
  { path: '/wines/:id', component: () => import('@/pages/public/WineDetailsPage.vue') },
  
  // Rutas autenticadas
  {
    path: '/cellar',
    component: () => import('@/pages/user/CellarPage.vue'),
    meta: { requiresAuth: true }
  },
  
  // Rutas de admin
  {
    path: '/admin',
    component: () => import('@/pages/admin/AdminDashboardPage.vue'),
    meta: { requiresAuth: true, roles: ['ROLE_ADMIN'] }
  }
]
```

### 8.2 Guards de Navegacion

```typescript
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  // Verificar autenticacion
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ path: '/login', query: { redirect: to.fullPath } })
  }
  
  // Verificar roles
  if (to.meta.roles && !authStore.hasAnyRole(to.meta.roles)) {
    return next({ path: '/403' })
  }
  
  next()
})
```

### 8.3 Lazy Loading

Todas las paginas se cargan de forma lazy para optimizar el bundle inicial:

```typescript
// Lazy loading con import dinamico
component: () => import('@/pages/public/WinesPage.vue')
```

---

## 9. Internacionalizacion

### 9.1 Configuracion

```typescript
// i18n/index.ts
import { createI18n } from 'vue-i18n'
import es from './locales/es.json'
import en from './locales/en.json'

export const i18n = createI18n({
  locale: 'es',
  fallbackLocale: 'en',
  messages: { es, en }
})
```

### 9.2 Uso en Componentes

```vue
<template>
  <h1>{{ $t('wines.title') }}</h1>
  <p>{{ $t('wines.found', { count: total }) }}</p>
</template>
```

### 9.3 Estructura de Traducciones

```json
// i18n/locales/es.json
{
  "common": {
    "save": "Guardar",
    "cancel": "Cancelar",
    "delete": "Eliminar",
    "edit": "Editar",
    "loading": "Cargando..."
  },
  "wines": {
    "title": "Catalogo de Vinos",
    "found": "{count} vinos encontrados",
    "noResults": "No se encontraron vinos"
  },
  "auth": {
    "login": "Iniciar sesion",
    "logout": "Cerrar sesion",
    "register": "Registrarse"
  }
}
```

---

## 10. Instalacion

### Requisitos Previos

- Node.js 18+
- npm 9+

### Pasos de Instalacion

1. **Navegar al directorio frontend:**
```bash
cd rewinelaravel/frontend
```

2. **Instalar dependencias:**
```bash
npm install
```

3. **Copiar archivo de entorno:**
```bash
cp .env.example .env.local
```

4. **Configurar variables de entorno:**
```bash
# .env.local
VITE_API_BASE_URL=http://localhost:8000/api/v1
VITE_MOCK_API=false
VITE_GOOGLE_MAPS_API_KEY=tu-api-key
```

5. **Iniciar servidor de desarrollo:**
```bash
npm run dev
```

La aplicacion estara disponible en `http://localhost:5173`.

---

## 11. Comandos Utiles

### Desarrollo

```bash
# Iniciar servidor de desarrollo
npm run dev

# Build para produccion
npm run build

# Preview del build de produccion
npm run preview
```

### Testing

```bash
# Ejecutar tests unitarios
npm run test

# Ejecutar tests con watch
npm run test:watch

# Ejecutar tests con coverage
npm run test:coverage

# Ejecutar tests e2e (Playwright)
npm run test:e2e
```

### Calidad de Codigo

```bash
# Ejecutar linter
npm run lint

# Corregir errores de lint automaticamente
npm run lint:fix

# Verificar tipos TypeScript
npm run type-check
```

### Otros

```bash
# Verificar traducciones
npm run check-i18n

# Generar reporte de bundle
npm run build -- --report
```

---

## 12. Testing

### 12.1 Tests Unitarios (Vitest)

```typescript
// tests/unit/stores/wines.store.spec.ts
import { describe, it, expect, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useWinesStore } from '@/stores/wines.store'

describe('WinesStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('debe inicializar con estado vacio', () => {
    const store = useWinesStore()
    expect(store.wines).toEqual([])
    expect(store.loading).toBe(false)
  })

  it('debe cargar vinos correctamente', async () => {
    const store = useWinesStore()
    await store.fetchWines()
    expect(store.wines.length).toBeGreaterThan(0)
  })
})
```

### 12.2 Tests de Componentes

```typescript
// tests/unit/components/WineCard.spec.ts
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import WineCard from '@/components/wines/WineCard.vue'

describe('WineCard', () => {
  it('debe mostrar nombre del vino', () => {
    const wrapper = mount(WineCard, {
      props: {
        wine: {
          id: '1',
          name: 'Malbec Reserve',
          winery: 'Bodega Test'
        }
      }
    })
    expect(wrapper.text()).toContain('Malbec Reserve')
  })
})
```

### 12.3 Tests E2E (Playwright)

```typescript
// tests/e2e/wines.spec.ts
import { test, expect } from '@playwright/test'

test('debe mostrar lista de vinos', async ({ page }) => {
  await page.goto('/wines')
  await expect(page.locator('h1')).toContainText('Vinos')
  await expect(page.locator('.wine-card')).toHaveCount.greaterThan(0)
})
```

### 12.3 Estructura de Tests

```
tests/
├── unit/
│   ├── components/
│   ├── stores/
│   ├── services/
│   └── utils/
├── e2e/
│   ├── auth.spec.ts
│   ├── wines.spec.ts
│   └── ...
└── setup.ts
```

---

## 13. Configuracion

### 13.1 Variables de Entorno

| Variable | Descripcion | Valor por defecto |
|----------|-------------|-------------------|
| `VITE_API_BASE_URL` | URL base de la API | http://localhost:8000/api/v1 |
| `VITE_MOCK_API` | Usar API mock | false |
| `VITE_APP_ENV` | Entorno de la app | development |
| `VITE_GOOGLE_MAPS_API_KEY` | Clave de Google Maps | - |

### 13.2 Archivos de Configuracion

| Archivo | Proposito |
|---------|-----------|
| `vite.config.ts` | Configuracion de Vite |
| `tsconfig.json` | Configuracion de TypeScript |
| `tailwind.config.js` | Configuracion de Tailwind CSS |
| `eslint.config.js` | Reglas de ESLint |
| `vitest.config.ts` | Configuracion de tests unitarios |
| `playwright.config.ts` | Configuracion de tests e2e |

### 13.3 Aliases de Path

```typescript
// vite.config.ts
resolve: {
  alias: {
    '@': '/src',
    '@api': '/src/api',
    '@components': '/src/components',
    '@pages': '/src/pages',
    '@stores': '/src/stores',
    '@services': '/src/services',
    '@domain': '/src/domain',
    '@utils': '/src/utils'
  }
}
```

---

## Referencias

- [Documentacion de Vue.js](https://vuejs.org/)
- [Documentacion de Pinia](https://pinia.vuejs.org/)
- [Documentacion de Vue Router](https://router.vuejs.org/)
- [Documentacion de Vite](https://vitejs.dev/)
- [Documentacion de Tailwind CSS](https://tailwindcss.com/)
- [Documentacion de Vitest](https://vitest.dev/)

---

Ultima actualizacion: Marzo 2026


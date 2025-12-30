# UserFrosting Skeleton-like Architecture

## Definición conceptual

Esta aplicación implementa una arquitectura **skeleton-like** basada en UserFrosting, siguiendo los patrones de un framework PHP moderno orientado a aplicaciones web empresariales.

La arquitectura skeleton-like define una estructura que:

- Separa el código ejecutable en servidor del contenido estático y dinámico accesible por HTTP.
- Mantiene las dependencias, configuración sensible y código privado **fuera del webroot**.
- Expone únicamente el punto de entrada HTTP que actúa como controlador frontal (front controller).
- Permite despliegues reproducibles sin modificar configuración en el repositorio.

---

## Estructura de directorios

```
├── public/                    # Webroot — única carpeta accesible por HTTP
│   ├── index.php             # Punto de entrada (front controller)
│   ├── assets/               # Contenido estático (CSS, JavaScript, imágenes)
│   └── uploads/              # Archivos dinámicos (cuando aplique)
│
├── app/                       # Código de aplicación (no accesible vía HTTP)
│   ├── src/                  # Código fuente de la aplicación
│   ├── config/               # Configuración de la aplicación
│   └── templates/            # Plantillas de renderización
│
├── vendor/                    # Dependencias externas (no versionadas en repo)
├── storage/                   # Caché, logs, sesiones (no versionadas)
├── .env                       # Variables de entorno (no versionadas)
└── build.json                 # Metadatos de construcción
```

---

## Invariante fundamental: HTTP Access Boundary

### Principio

**Solo el contenido de la carpeta `public/` es accesible directamente mediante HTTP.**

Esto significa:

- El servidor web (Nginx, Apache) expone **exclusivamente** `public/` como webroot.
- Cualquier solicitud HTTP se dirige primero a `public/index.php` (via rewrite rules).
- El código de aplicación, configuración y dependencias **no son accesibles** directamente por HTTP, incluso si un usuario intentara navegar a ellas.
- Los archivos de configuración sensible (`.env`, credenciales) permanecen en servidor fuera del webroot.

### Beneficios

- **Seguridad**: La exposición de código y configuración es imposible por diseño.
- **Separación de responsabilidades**: Contenido público vs. lógica privada.
- **Desacoplamiento**: El repositorio no asume ninguna ruta específica en el servidor.

---

## Componentes arquitectónicos

### 1. Front Controller (`public/index.php`)

- Punto de entrada único para todas las solicitudes HTTP.
- Inicializa la aplicación y delega al framework.
- Nunca contiene lógica de negocio; es meramente un bootstrap.

### 2. Código de Aplicación (`app/`)

Alberga:
- **Controladores**: Lógica de solicitud/respuesta.
- **Modelos**: Lógica de negocio y acceso a datos.
- **Servicios**: Componentes reutilizables (email, autenticación, etc.).
- **Configuración**: Parámetros específicos de comportamiento y conexión.

### 3. Plantillas (`app/templates/`)

HTML/UI renderizado por los controladores. Nunca se sirven directamente; siempre procesadas por la aplicación.

### 4. Dependencias (`vendor/`)

Librerías externas instaladas via gestor de dependencias (Composer, npm, etc.).
**No se versionan en el repositorio.** No se versionan en el repositorio. Se reconstruyen en el entorno de construcción (Integración continua) y se incluyen en el artefacto de despliegue.or o durante el despliegue.

### 5. Configuración de Infraestructura (`.env`, archivos no versionados)

- Credenciales de base de datos.
- Claves secretas.
- URLs de servicios externos.
- Parámetros específicos del entorno.

Residen **exclusivamente en el servidor**, nunca en el repositorio.

---

## Ciclo de vida: Construcción y Despliegue

### Fase de Construcción (No ocurre en servidor)

1. Las dependencias se instalan en el entorno de construcción (local, CI/CD).
2. Los assets se procesan (compresión, bundling, minificación si aplica).
3. El código se empaqueta junto con las dependencias como un artefacto.

### Fase de Despliegue (En servidor)

1. El artefacto (código + dependencias) se transfiere al servidor.
2. La configuración sensible (`.env`) se inyecta en el servidor (no viene en el artefacto).
3. El webroot del servidor se apunta a la carpeta `public/` del artefacto.
4. La aplicación está lista para ejecutarse.

### Implicaciones

- **Sin Composer, npm, ni builds en servidor**: Todo ocurre beforehand.
- **Despliegue como artefacto**: No hay clone del repositorio en servidor.
- **Configuración por inyección**: Los valores sensibles no pasan por el repositorio.

---

## Decisiones de diseño

| Aspecto | Decisión | Justificación |
|--------|----------|---------------|
| **Webroot** | `public/` | Estándar de seguridad en aplicaciones modernas. |
| **Front Controller** | `public/index.php` | Punto de entrada único para control centralizado. |
| **Dependencias en repositorio** | No | Se reconstruyen en CI/CD; reduce tamaño y acoplamiento. |
| **Configuración sensible en repo** | No | Las credenciales y datos específicos nunca se versionan. |
| **Despliegue como artefacto** | Sí | Reproducibilidad y separación clara construcción/infraestructura. |

---

## Relación con el repositorio

El repositorio contiene:
- Código fuente de la aplicación.
- Configuraciones no sensibles.
- Metadatos de construcción (`build.json`).
- Documentación.

El repositorio **no contiene**:
- Dependencias (se reconstruyen).
- Configuración sensible (reside en servidor).
- Artefactos de construcción voluminosos.
- Datos específicos de infraestructura.

---

## Referencia

Para más información sobre arquitecturas skeleton-like y UserFrosting, véase la documentación oficial de UserFrosting y estándares PHP como PSR-12 (estilo de código) y PSR-4 (autoloading).

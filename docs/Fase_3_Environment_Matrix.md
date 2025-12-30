# Matriz mínima de entornos

## Definición de entornos

Esta aplicación se ejecuta en tres entornos estándar, cada uno con características y configuraciones específicas.

---

## Entornos definidos

### 1. Desarrollo

**Contexto**
- Código fuente en GitHub Codespaces u otro ambiente local.
- Cambios frecuentes en código e configuración.
- Acceso restringido a colaboradores del proyecto.
- Enfoque en debugging, iteración rápida y experimentación.

**Características**
- Logging detallado.
- Modo de depuración habilitado.
- Cache deshabilitado (para ver cambios inmediatos).
- Validaciones extensas.

---

### 2. Staging

**Contexto**
- Ambiente de validación previo a producción.
- Replica la infraestructura de producción lo más fielmente posible.
- Acceso restringido a equipo y stakeholders de validación.
- Pruebas de integración, rendimiento y comportamiento pre-deployment.

**Características**
- Logging moderado (información operativa).
- Modo de depuración deshabilitado.
- Cache habilitado (simula comportamiento de producción).
- Validaciones completas pero sin detalles internos expuestos.

---

### 3. Producción

**Contexto**
- Ambiente público o cliente final.
- Cambios controlados y planificados.
- Enfoque en estabilidad, rendimiento y seguridad.
- Acceso restringido; cambios únicamente mediante despliegues.

**Características**
- Logging mínimo (errores críticos y eventos de negocio).
- Modo de depuración deshabilitado.
- Cache agresivo (rendimiento).
- Validaciones optimizadas.
- Mensajes de error genéricos (sin exponer detalles internos).

---

## Matriz de variables de configuración

Las siguientes categorías de variables se configuran por entorno:

| Variable | Tipo | Desarrollo | Staging | Producción | Ubicación |
|----------|------|-----------|---------|-----------|-----------|
| **Modo de ejecución** | | | | | |
| `APP_DEBUG` | boolean | true | false | false | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |
| `APP_ENV` | enum | development | staging | production | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |
| `LOG_LEVEL` | enum | debug | info | error | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |
| **Acceso HTTP** | | | | | |
| `APP_URL` | URL | http://localhost:8000 | https://<staging-domain> | https://<prod-domain> | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |
| `TRUSTED_HOSTS` | list | localhost, 127.0.0.1 | staging domain | prod domain | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |
| **Base de Datos** | | | | | |
| `DB_HOST` | hostname | localhost | <staging-db-host> | <prod-db-host> | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |
| `DB_PORT` | integer | 3306 | 3306 | 3306 | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |
| `DB_DATABASE` | name | dev_app | staging_app | prod_app | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |
| `DB_USER` | username | dev_user | <staging-user> | <prod-user> | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |
| `DB_PASSWORD` | secret | dev_pass | <staging-pass> | <prod-pass> | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |
| **Correo (SMTP)** | | | | | |
| `MAIL_HOST` | hostname | mailhog | <staging-smtp> | <prod-smtp> | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |
| `MAIL_PORT` | integer | 1025 | 587 | 587 | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |
| `MAIL_USERNAME` | username | (none) | <staging-user> | <prod-user> | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |
| `MAIL_PASSWORD` | secret | (none) | <staging-pass> | <prod-pass> | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |
| `MAIL_ENCRYPTION` | protocol | none | TLS | TLS | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |
| `MAIL_FROM_ADDRESS` | email | noreply@localhost | noreply@domain | noreply@domain | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |
| **Seguridad** | | | | | |
| `APP_KEY` | secret | dev-key-xxx | <staging-key> | <prod-key> | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |
| `SESSION_LIFETIME` | minutes | 120 | 60 | 60 | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |
| `CSRF_ENABLED` | boolean | false (dev) | true | true | Versionado (no sensible) |
| **Integración continua** | | | | | |
| `CI_REGISTRY` | URL | N/A | N/A | N/A | Versionado |
| `DEPLOYMENT_STRATEGY` | enum | N/A | N/A | SCP-based | Archivo .env en servidor (poblado con valores procedentes de GitHub Actions Secrets durante el despliegue) |

---

## Ubicación de almacenamiento de variables

### CI/CD Secrets (GitHub Actions Secrets)

Nota de ejecución: GitHub Actions Secrets actúan como fuente de valores durante el despliegue. Sin embargo, la ejecución en servidor consume la configuración exclusivamente desde el archivo .env residente en el servidor. Por tanto, los Secrets no constituyen el runtime, sino el origen de valores para poblar o actualizar el .env del entorno destino.

Alberga información sensible requerida durante el despliegue automático:

- Credenciales de base de datos.
- Claves secretas de aplicación.
- Credenciales de SMTP.
- URLs y hostnames de infraestructura.
- Parámetros específicos del entorno.

**Principio**: Nunca se comprueban en el repositorio; existen exclusivamente en GitHub.

**Ejemplos**
```
DB_HOST_PROD
DB_PASSWORD_PROD
MAIL_PASSWORD_STAGING
APP_KEY_PRODUCTION
```

### Archivos `.env` en servidor

Residen exclusivamente en el servidor final (no en el repositorio).

Se inyectan durante el despliegue o se crean manualmente post-despliegue.

Contienen:
- Variables sensibles específicas del servidor.
- Parámetros que varían entre ejecuciones.

**Principio**: Nunca se sincronizan ni se versionan.

**Ejemplo de estructura** (solo para referencia; no aparecen valores reales)
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<domain>
DB_HOST=<db-host>
DB_PASSWORD=<db-password>
MAIL_PASSWORD=<smtp-password>
...
```

### Configuración versionada (no sensible)

Residen en el repositorio y se aplican a todos los entornos (o se sobrescriben por entorno):

- Parámetros de comportamiento de la aplicación (no secretos).
- Opciones de validación, formato, UI.
- Rutas de caché, logs (que no expongan rutas reales).
- Versiones de componentes.

**Ejemplos**
```json
{
  "CSRF_ENABLED": true,
  "PASSWORD_MIN_LENGTH": 8,
  "MAX_LOGIN_ATTEMPTS": 5,
  "TIMEZONE": "UTC"
}
```

---

## Separación de responsabilidades

| Entidad | Responsabilidad | Ejemplo |
|---------|-----------------|---------|
| **Repositorio (GitHub)** | Código, lógica, configuración no sensible | Controllers, Models, validaciones |
| **GitHub Actions Secrets** | Variables de entorno sensibles | DB credentials, API keys |
| **Servidor (.env)** | Configuración específica del entorno ejecutado | Rutas locales, parámetros de runtime |

GitHub Actions: no ejecuta la aplicación. Solo construye el artefacto de despliegue y transfiere ficheros al servidor. La aplicación se ejecuta únicamente en el Shared Hosting Server.

---

## Proceso de despliegue y configuración

### En CI/CD (GitHub Actions)

1. Se leen las variables desde **GitHub Actions Secrets** según el entorno destino.
2. Se generan artefactos (código + dependencias).
3. Se transfieren al servidor mediante SCP (u otro mecanismo).

### En servidor

1. Se descomprime el artefacto.
2. Se crea o actualiza el archivo `.env` con valores inyectados manualmente o vía script.
3. La aplicación se inicia/reinicia con la nueva configuración.

**Ninguna credencial o valor sensible viaja en el repositorio.**

---

## Invariantes

- **Confidencialidad**: No hay credenciales en el repositorio público.
- **Trazabilidad**: Las decisiones sobre variables quedan registradas en este documento.
- **Reproducibilidad**: El mismo código + mismas variables en CI/CD = mismo comportamiento en servidor.
- **Flexibilidad**: Nuevos entornos pueden añadirse replicando esta matriz sin modificar el repositorio.

---

## Referencia

Para más detalles sobre gestión de configuración en aplicaciones modernas, véase:
- The Twelve-Factor App (Factor III: Store config in the environment)
- GitHub Actions Secrets documentation
- dotenv specification

# PVUF - Proyecto de Validación y Transición a UserFrosting

Aplicación web basada en **UserFrosting 5.x** con arquitectura skeleton-like, despliegue automatizado y separación clara entre código de aplicación y recursos públicos.

**Estado:** Fase 4 - Incorporación de UserFrosting y despliegue a staging  
**Rama de trabajo:** `F3-uf-skeleton-like`  
**Staging:** `https://pvuf.plazza.xyz/`

## 🎯 Objetivo

Este proyecto implementa una aplicación **UserFrosting 5.x** completa con:

- ✅ Arquitectura skeleton-like (webroot = `public/`)
- ✅ Despliegue automatizado desde GitHub Actions
- ✅ Construcción de dependencias en CI/CD (sin Composer/npm en servidor)
- ✅ Base de datos MariaDB en staging
- ✅ Correo SMTP real configurado
- ✅ Instalación completa con usuario administrador
- ✅ **Cero secretos en el repositorio**

## 📚 Documentación

### Fase 3 - Arquitectura UserFrosting skeleton-like (Cerrada)

- **[Fase_3_UF_skeleton-like_architecture.md](docs/Fase_3_UF_skeleton-like_architecture.md)** - Arquitectura general y estructura de carpetas
- **[Fase_3_Decision_HTTP_Entry_Point.md](docs/Fase_3_Decision_HTTP_Entry_Point.md)** - Decisión sobre el punto de entrada HTTP (`public/index.php`)
- **[Fase_3_Environment_Matrix.md](docs/Fase_3_Environment_Matrix.md)** - Matriz de entornos y configuración

### Fase 4 - Incorporación de UserFrosting y Despliegue a Staging (En Progreso)

- **[Fase_4_Configuracion_Entorno_Staging.md](docs/Fase_4_Configuracion_Entorno_Staging.md)** - Variables de entorno y secretos de GitHub Actions

## 🔒 Límite de Exposición HTTP (Frontera HTTP)

**CONTRATO DE SEGURIDAD:** Solo la carpeta `public/` es accesible por HTTP.

El webroot del hosting **debe apuntar a `public/`**. Las siguientes rutas y archivos NO deben ser accesibles directamente por URL:

- `app/` - Código de aplicación
- `vendor/` - Dependencias de Composer
- `config/` - Archivos de configuración
- `storage/` - Datos persistentes y logs
- `.env` - Variables de entorno y secretos

**Punto de entrada HTTP definitivo:** `public/index.php`

> ⚠️ El cambio del webroot en el panel del hosting se realiza tras el primer despliegue que crea la estructura completa en el servidor.

## 📁 Estructura

```
PVUF/
├── public/                      # ⚠️ ÚNICO DIRECTORIO ACCESIBLE POR HTTP
│   ├── index.php               # Entry point (front controller)
│   ├── .htaccess               # Rewrite rules para Apache
│   └── assets/                 # Assets estáticos (futuro)
│
├── app/                         # Código de aplicación (privado)
│   ├── app.php                 # Bootstrap de UserFrosting
│   ├── src/                    # Código fuente
│   ├── config/                 # Configuración
│   └── templates/              # Plantillas
│
├── vendor/                      # Dependencias (NO versionado)
├── storage/                     # Cache, logs, sesiones (NO versionado)
│   ├── logs/
│   ├── cache/
│   └── sessions/
│
├── docs/                        # Documentación normativa
├── .env.example                # Plantilla de configuración
├── composer.json               # Dependencias PHP
├── composer.lock               # Lock de dependencias
└── .github/workflows/
    └── deploy.yml              # Workflow de despliegue automatizado
```
│   ├── Fase_3_UF_skeleton-like_architecture.md
│   ├── Fase_3_Decision_HTTP_Entry_Point.md
│   ├── Fase_3_Environment_Matrix.md
│   └── Fase_3_Cierre_Checklist.md
├── DEPLOYMENT.md               # Guía completa de configuración
├── SSH_KEYS.md                 # Detalles de claves SSH
├── QUICKSTART.md               # Resumen rápido e instalación
├── SSH_PASSPHRASE_PLAN.md      # Plan alternativo con contraseña
└── .github/workflows/
    └── deploy.yml              # Workflow de GitHub Actions
```

## 🚀 Despliegue a Staging

### Prerrequisitos

1. **Secretos configurados en GitHub Actions** (ver [Fase_4_Configuracion_Entorno_Staging.md](docs/Fase_4_Configuracion_Entorno_Staging.md))
2. **Base de datos MariaDB** provisionada en el servidor
3. **Credenciales SMTP** para envío de correo

### Proceso de Despliegue

El despliegue es completamente automatizado:

```bash
# 1. Commit y push a la rama F3-uf-skeleton-like
git add .
git commit -m "Update application"
git push origin F3-uf-skeleton-like

# 2. GitHub Actions automáticamente:
#    - Instala dependencias con Composer
#    - Genera archivo .env con secretos
#    - Despliega vía SCP al servidor
#    - Configura permisos

# 3. Acceder a staging
open https://pvuf.plazza.xyz/
```

### Primera Instalación

Después del primer despliegue:

1. **Cambiar webroot en el hosting** para apuntar a `{DEPLOY_PATH}/public`
2. **Acceder a** `https://pvuf.plazza.xyz/`
3. **Completar el wizard de instalación** de UserFrosting
4. **Crear usuario administrador**
5. **Verificar envío de correo** desde la aplicación

Ver documentación detallada en [docs/](docs/).

## 🔐 Seguridad y Configuración

- **Secretos:** Todos los secretos residen en GitHub Actions Secrets y en `.env` del servidor
- **Repositorio limpio:** No hay credenciales, contraseñas ni claves en el código versionado
- **Separación de entornos:** Development, Staging, Production según [Fase_3_Environment_Matrix.md](docs/Fase_3_Environment_Matrix.md)

## 📋 Checklist de Verificación Fase 4

- [ ] Estructura skeleton UserFrosting 5.x incorporada
- [ ] `public/index.php` existe y funciona como entry point
- [ ] Workflow de GitHub Actions construye dependencias
- [ ] Despliegue a staging exitoso vía SCP
- [ ] Webroot del hosting apunta a `public/`
- [ ] Instalación de UserFrosting completada
- [ ] Usuario administrador funcional
- [ ] Base de datos MariaDB operativa
- [ ] Correo SMTP funcional y verificado
- [ ] Cero secretos en el repositorio

## 🎓 Tecnologías

- **PHP** 8.3+
- **UserFrosting** 5.x
- **Composer** (gestión de dependencias)
- **MariaDB** (base de datos)
- **GitHub Actions** (CI/CD)
- **Apache** con mod_rewrite

## ❌ No Requiere en el Servidor

- ❌ Composer (las dependencias se construyen en CI/CD)
- ❌ npm o Node.js
- ❌ Git
- ❌ Herramientas de construcción

Todo el build se ejecuta en GitHub Actions. El servidor solo necesita PHP y Apache.

---

**Última actualización:** 2025-12-30  
**Versión:** Fase 4  
**Estado:** En implementación 🔧


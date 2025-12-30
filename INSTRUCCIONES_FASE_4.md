# 🚀 Instrucciones para Completar la Fase 4

El código de la aplicación UserFrosting 5.x está listo y commiteado en la rama `F3-uf-skeleton-like`. Para completar el despliegue a staging y la instalación, sigue estos pasos:

---

## ✅ Estado Actual

**Completado:**
- ✅ Estructura skeleton UserFrosting 5.x incorporada
- ✅ `public/index.php` como punto de entrada HTTP definitivo
- ✅ Dependencias configuradas en `composer.json`
- ✅ Workflow GitHub Actions actualizado para build + despliegue
- ✅ Documentación de configuración de entorno
- ✅ `.gitignore` configurado (vendor/ excluido)
- ✅ Commit realizado en rama `F3-uf-skeleton-like`

**Pendiente:**
- ⏳ Configurar secretos en GitHub Actions
- ⏳ Push a GitHub para disparar despliegue
- ⏳ Cambiar webroot en el hosting
- ⏳ Completar instalación de UserFrosting
- ⏳ Verificar MariaDB y SMTP

---

## 📋 Paso 1: Configurar Secretos en GitHub Actions

**Ubicación:** `GitHub Repository → Settings → Secrets and variables → Actions`

Debes configurar los siguientes secretos. Consulta **[docs/Fase_4_Guia_Secretos_GitHub.md](docs/Fase_4_Guia_Secretos_GitHub.md)** para detalles completos.

### Secretos Mínimos Requeridos:

#### SSH y Despliegue
```
DEPLOY_KEY           - Tu clave privada SSH
DEPLOY_KEY_PASSPHRASE - Passphrase de la clave (o vacío)
DEPLOY_HOST          - pvuf.plazza.xyz
DEPLOY_USER          - [tu usuario SSH]
DEPLOY_PORT          - 22
DEPLOY_PATH          - [ruta absoluta en el servidor]
```

#### Base de Datos
```
DB_HOST              - localhost (o hostname de MariaDB)
DB_PORT              - 3306
DB_DATABASE          - pvuf_staging (o el nombre que elijas)
DB_USERNAME          - [usuario de base de datos]
DB_PASSWORD          - [contraseña de base de datos]
```

#### Correo SMTP
```
MAIL_HOST            - [servidor SMTP del hosting]
MAIL_PORT            - 587 (TLS) o 465 (SSL)
MAIL_USERNAME        - [usuario SMTP]
MAIL_PASSWORD        - [contraseña SMTP]
MAIL_ENCRYPTION      - tls o ssl
MAIL_FROM_ADDRESS    - noreply@pvuf.plazza.xyz
```

#### Aplicación
```
APP_KEY              - [generar con: php -r "echo bin2hex(random_bytes(32));"]
```

**Importante:** Genera un `APP_KEY` único ejecutando:
```bash
php -r "echo bin2hex(random_bytes(32));"
```

---

## 📋 Paso 2: Crear Base de Datos MariaDB

Antes de desplegar, asegúrate de tener una base de datos MariaDB creada:

1. Accede al panel de control de tu hosting (cPanel, Plesk, etc.)
2. Busca "MySQL Databases" o "Bases de datos"
3. Crea una nueva base de datos: `pvuf_staging`
4. Crea un usuario con permisos completos sobre esa base de datos
5. Anota las credenciales para configurar los secretos

---

## 📋 Paso 3: Push a GitHub para Desplegar

Una vez configurados todos los secretos:

```bash
cd /workspaces/pviva-FWUF
git push origin F3-uf-skeleton-like
```

Esto disparará automáticamente el workflow de GitHub Actions que:
1. Instalará las dependencias de UserFrosting con Composer
2. Generará el archivo `.env` con los secretos configurados
3. Desplegará toda la estructura al servidor vía SCP
4. Configurará permisos correctos en `storage/`

**Verificar el despliegue:**
- Ve a: `GitHub → Actions → Deploy PVUF to Staging`
- Confirma que todos los pasos se completan en verde

---

## 📋 Paso 4: Cambiar Webroot en el Hosting

**⚠️ CRÍTICO:** Debes cambiar el webroot del dominio para apuntar al directorio `public/`

### En cPanel:
1. Buscar "Dominios" o "Domains"
2. Encontrar `pvuf.plazza.xyz`
3. Editar el "Document Root" o "Directorio raíz"
4. Cambiar de la ruta actual a: `{DEPLOY_PATH}/public`
   - Ejemplo: `/home/plazzaxy/pvuf.plazza.xyz/public`
5. Guardar cambios

### En Plesk:
1. Ir a "Dominios" → `pvuf.plazza.xyz`
2. "Configuración de Apache y nginx"
3. Cambiar "Document root" a: `{DEPLOY_PATH}/public`
4. Aplicar

### Verificar:
```bash
# Intentar acceder a estas URLs (deben fallar con 404):
https://pvuf.plazza.xyz/../app/
https://pvuf.plazza.xyz/../vendor/
https://pvuf.plazza.xyz/../.env

# Esto debe funcionar:
https://pvuf.plazza.xyz/
```

---

## 📋 Paso 5: Completar Instalación de UserFrosting

1. **Accede a staging:**
   ```
   https://pvuf.plazza.xyz/
   ```

2. **UserFrosting detectará que la instalación está pendiente** y mostrará el wizard de instalación

3. **Completa el wizard:**
   - Verifica requisitos del sistema
   - Confirma conexión a base de datos
   - Ejecuta migraciones
   - Crea usuario administrador

4. **Anota las credenciales del administrador** (usuario y contraseña que establezcas)

---

## 📋 Paso 6: Verificar MariaDB

Después de la instalación:

1. Inicia sesión con el usuario administrador
2. Verifica que puedes navegar por el panel de administración
3. Confirma que los datos se persisten (crear un usuario de prueba, cerrar sesión, volver a iniciar)

**Si hay errores de conexión:**
- Verifica los secretos `DB_*` en GitHub Actions
- Verifica que la base de datos existe en el panel del hosting
- Verifica permisos del usuario sobre la base de datos

---

## 📋 Paso 7: Verificar Correo SMTP

1. **Desde UserFrosting, intenta una acción que envíe correo:**
   - Recuperación de contraseña
   - Registro de usuario nuevo (si está habilitado)
   - Envío de notificaciones

2. **Verifica que el correo se recibe**

3. **Verifica que los enlaces del correo apuntan a `https://pvuf.plazza.xyz/`**

**Si el correo no se envía:**
- Verifica los secretos `MAIL_*` en GitHub Actions
- Verifica que `MAIL_ENCRYPTION` coincide con `MAIL_PORT` (587→tls, 465→ssl)
- Consulta con tu proveedor de hosting si SMTP está habilitado
- Revisa logs en `storage/logs/` en el servidor

---

## 📋 Paso 8: Generar Documento de Diagnóstico

Una vez completados todos los pasos anteriores, crea el documento de diagnóstico:

**Archivo:** `docs/Fase_4_Diagnostico_Verificacion.md`

Debe incluir:
- Commit hash verificado
- Fecha de verificación
- Evidencia de estructura skeleton incorporada
- Evidencia de despliegue exitoso
- Evidencia de webroot apuntando a `public/`
- Evidencia de instalación completada con administrador funcional
- Evidencia de uso de MariaDB
- Evidencia de envío de correo SMTP
- Confirmación de ausencia de secretos en repositorio

**Plantilla sugerida:**

```markdown
# Diagnóstico de Verificación - Fase 4

**Fecha:** 2025-12-30
**Commit:** bb47db7
**Revisor:** [tu nombre]

## ✅ Verificaciones Completadas

### 1. Estructura Skeleton UserFrosting 5.x
- [x] Existe `public/index.php`
- [x] Existe `app/app.php`
- [x] Existe `composer.json` con UserFrosting 5.1
- [x] `vendor/` excluido del repositorio

### 2. Despliegue a Staging
- [x] Workflow GitHub Actions ejecutado exitosamente
- [x] Archivos desplegados a `{DEPLOY_PATH}`
- [x] Estructura completa en el servidor

### 3. Webroot Configurado
- [x] Webroot apunta a `{DEPLOY_PATH}/public`
- [x] URLs a `../app/`, `../vendor/`, `../.env` retornan 404

### 4. Instalación UserFrosting
- [x] Wizard de instalación completado
- [x] Usuario administrador creado: [usuario]
- [x] Inicio de sesión funcional

### 5. Base de Datos MariaDB
- [x] Conexión a MariaDB exitosa
- [x] Tablas creadas correctamente
- [x] Datos persisten tras reinicios

### 6. Correo SMTP
- [x] Correo de prueba enviado exitosamente
- [x] Enlaces en correo apuntan a https://pvuf.plazza.xyz/
- [x] Remitente correcto: noreply@pvuf.plazza.xyz

### 7. Seguridad
- [x] No existen secretos en el repositorio
- [x] Archivo `.env` solo existe en el servidor

## 📊 Evidencias

[Agregar capturas de pantalla o logs relevantes]

## ✅ Fase 4 Cerrada

Todos los criterios de aceptación han sido cumplidos.
```

---

## 🎯 Criterios de Aceptación Final

La Fase 4 está cerrada cuando:

- ✅ Estructura skeleton oficial de UserFrosting 5.x en el repositorio
- ✅ `public/index.php` funciona como entry point
- ✅ Webroot del hosting apunta a `public/`
- ✅ Aplicación UserFrosting carga en `https://pvuf.plazza.xyz/`
- ✅ Usuario administrador funcional
- ✅ Base de datos MariaDB operativa
- ✅ Correo SMTP funcional y verificado
- ✅ Cero secretos en el repositorio
- ✅ Existe `docs/Fase_4_Diagnostico_Verificacion.md`

---

## 📚 Documentación de Referencia

- **[docs/Fase_4_Configuracion_Entorno_Staging.md](docs/Fase_4_Configuracion_Entorno_Staging.md)** - Variables de entorno requeridas
- **[docs/Fase_4_Guia_Secretos_GitHub.md](docs/Fase_4_Guia_Secretos_GitHub.md)** - Guía detallada de configuración de secretos
- **[docs/Fase_3_UF_skeleton-like_architecture.md](docs/Fase_3_UF_skeleton-like_architecture.md)** - Arquitectura del proyecto
- **[.github/workflows/deploy.yml](.github/workflows/deploy.yml)** - Workflow de despliegue

---

## 🆘 Soporte

Si encuentras problemas:

1. Consulta las guías de documentación en `docs/`
2. Revisa los logs de GitHub Actions
3. Verifica los logs en el servidor: `storage/logs/`
4. Verifica la configuración del webroot en el panel del hosting
5. Confirma que todos los secretos están correctamente configurados

---

**¡Éxito con el despliegue! 🚀**

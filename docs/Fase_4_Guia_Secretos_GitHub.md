# Guía de Configuración de Secretos de GitHub Actions

Este documento describe paso a paso cómo configurar todos los secretos necesarios en GitHub Actions para el despliegue automatizado de PVUF a staging.

## 📍 Ubicación

Los secretos se configuran en:

```
GitHub Repository → Settings → Secrets and variables → Actions → Repository secrets
```

---

## 🔑 Secretos Requeridos

### 1. Secretos de Despliegue SSH

#### `DEPLOY_KEY`
**Descripción:** Clave privada SSH para autenticación en el servidor

**Cómo obtenerla:**
```bash
# Si ya tienes una clave SSH configurada para el hosting, usa esa
# De lo contrario, genera una nueva (se recomienda usar la existente)
cat ~/.ssh/id_ed25519
# o
cat ~/.ssh/id_rsa
```

**Formato:** El contenido completo de la clave privada, incluyendo:
```
-----BEGIN OPENSSH PRIVATE KEY-----
[contenido de la clave]
-----END OPENSSH PRIVATE KEY-----
```

#### `DEPLOY_KEY_PASSPHRASE`
**Descripción:** Passphrase de la clave SSH (si la clave la tiene)

**Valor:** La contraseña que protege la clave privada, o dejar vacío si la clave no tiene passphrase

#### `DEPLOY_HOST`
**Descripción:** Hostname o IP del servidor

**Valor para staging:** `pvuf.plazza.xyz` o la IP del servidor

#### `DEPLOY_USER`
**Descripción:** Usuario SSH del servidor

**Valor:** El nombre de usuario proporcionado por el hosting (ejemplo: `plazzaxy`)

#### `DEPLOY_PORT`
**Descripción:** Puerto SSH del servidor

**Valor:** `22` (por defecto) o el puerto personalizado si el hosting usa otro

#### `DEPLOY_PATH`
**Descripción:** Ruta absoluta donde se desplegará la aplicación

**Valor ejemplo:** `/home/plazzaxy/pvuf.plazza.xyz`

**Cómo obtenerla:**
```bash
# Conectar por SSH y ejecutar
ssh usuario@pvuf.plazza.xyz
pwd
# La ruta mostrada es el valor a configurar
```

---

### 2. Secretos de Base de Datos MariaDB

#### `DB_HOST`
**Descripción:** Servidor de base de datos

**Valor típico:** `localhost` o el hostname proporcionado por el hosting

#### `DB_PORT`
**Descripción:** Puerto de MariaDB

**Valor:** `3306` (por defecto)

#### `DB_DATABASE`
**Descripción:** Nombre de la base de datos

**Valor sugerido:** `pvuf_staging`

**Cómo crearla:**
1. Acceder al panel de control del hosting (cPanel, Plesk, etc.)
2. Buscar "MySQL Databases" o "Bases de datos"
3. Crear una nueva base de datos con el nombre `pvuf_staging`

#### `DB_USERNAME`
**Descripción:** Usuario de la base de datos

**Valor:** Proporcionado por el hosting al crear la base de datos

#### `DB_PASSWORD`
**Descripción:** Contraseña del usuario de base de datos

**Valor:** Proporcionado por el hosting o establecido al crear el usuario

---

### 3. Secretos de Correo SMTP

#### `MAIL_HOST`
**Descripción:** Servidor SMTP

**Valor:** Proporcionado por el hosting o servicio de correo

**Ejemplos comunes:**
- Hosting compartido: `mail.{tu-dominio}.com` o `smtp.{tu-dominio}.com`
- Gmail: `smtp.gmail.com`
- SendGrid: `smtp.sendgrid.net`
- Mailgun: `smtp.mailgun.org`

#### `MAIL_PORT`
**Descripción:** Puerto SMTP

**Valores comunes:**
- `587` - TLS (recomendado)
- `465` - SSL
- `25` - Sin cifrado (no recomendado)

#### `MAIL_USERNAME`
**Descripción:** Usuario SMTP

**Valor:** 
- Para hosting compartido: la dirección de correo completa (ej: `noreply@pvuf.plazza.xyz`)
- Para servicios externos: el usuario proporcionado por el servicio

#### `MAIL_PASSWORD`
**Descripción:** Contraseña SMTP

**Valor:** La contraseña de la cuenta de correo o API key (según el servicio)

#### `MAIL_ENCRYPTION`
**Descripción:** Tipo de cifrado

**Valores válidos:**
- `tls` - Para puerto 587
- `ssl` - Para puerto 465
- Dejar vacío para sin cifrado (puerto 25)

#### `MAIL_FROM_ADDRESS`
**Descripción:** Dirección de correo remitente

**Valor sugerido:** `noreply@pvuf.plazza.xyz`

---

### 4. Secretos de Aplicación

#### `APP_KEY`
**Descripción:** Clave de cifrado para sesiones y datos sensibles

**Cómo generarla:**
```bash
php -r "echo bin2hex(random_bytes(32));"
```

Este comando genera una cadena hexadecimal de 64 caracteres, por ejemplo:
```
a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6a7b8c9d0e1f2
```

**Importante:** Generar una clave única para cada entorno (staging, production)

---

## 📝 Checklist de Configuración

Verificar que todos los secretos están configurados:

### SSH y Despliegue
- [ ] `DEPLOY_KEY` - Clave privada SSH
- [ ] `DEPLOY_KEY_PASSPHRASE` - Passphrase (o vacío)
- [ ] `DEPLOY_HOST` - pvuf.plazza.xyz
- [ ] `DEPLOY_USER` - Usuario SSH
- [ ] `DEPLOY_PORT` - 22 (o personalizado)
- [ ] `DEPLOY_PATH` - Ruta absoluta en el servidor

### Base de Datos
- [ ] `DB_HOST` - Servidor MariaDB
- [ ] `DB_PORT` - 3306
- [ ] `DB_DATABASE` - pvuf_staging
- [ ] `DB_USERNAME` - Usuario de BD
- [ ] `DB_PASSWORD` - Contraseña de BD

### Correo SMTP
- [ ] `MAIL_HOST` - Servidor SMTP
- [ ] `MAIL_PORT` - 587 o 465
- [ ] `MAIL_USERNAME` - Usuario SMTP
- [ ] `MAIL_PASSWORD` - Contraseña SMTP
- [ ] `MAIL_ENCRYPTION` - tls o ssl
- [ ] `MAIL_FROM_ADDRESS` - noreply@pvuf.plazza.xyz

### Aplicación
- [ ] `APP_KEY` - Clave de cifrado generada

---

## 🧪 Verificación

Después de configurar todos los secretos:

1. **Push a la rama `F3-uf-skeleton-like`**
   ```bash
   git push origin F3-uf-skeleton-like
   ```

2. **Verificar GitHub Actions**
   - Ir a: Actions → Deploy PVUF to Staging
   - Confirmar que el workflow se ejecuta sin errores

3. **Verificar conexión SSH**
   - El paso "Test SSH connection" debe mostrar "SSH Connection successful!"

4. **Verificar despliegue**
   - El paso "Deploy application via SCP" debe completarse sin errores

---

## 🔍 Solución de Problemas

### Error: "Permission denied (publickey)"
**Causa:** La clave SSH no está configurada correctamente

**Solución:**
1. Verificar que `DEPLOY_KEY` contiene la clave privada completa
2. Verificar que la clave pública correspondiente está en `~/.ssh/authorized_keys` del servidor
3. Verificar permisos: `chmod 600 ~/.ssh/authorized_keys`

### Error: "Database connection failed"
**Causa:** Credenciales de base de datos incorrectas

**Solución:**
1. Verificar que la base de datos existe en MariaDB
2. Verificar `DB_USERNAME` y `DB_PASSWORD`
3. Verificar que el usuario tiene permisos sobre la base de datos

### Error: "SMTP authentication failed"
**Causa:** Credenciales SMTP incorrectas

**Solución:**
1. Verificar `MAIL_USERNAME` y `MAIL_PASSWORD`
2. Verificar que `MAIL_ENCRYPTION` coincide con `MAIL_PORT` (587→tls, 465→ssl)
3. Confirmar con el proveedor de hosting que SMTP está habilitado

---

## 📚 Referencias

- [GitHub Actions - Encrypted secrets](https://docs.github.com/en/actions/security-guides/encrypted-secrets)
- [Fase 4 - Configuración de Entorno Staging](Fase_4_Configuracion_Entorno_Staging.md)
- [UserFrosting - Configuration](https://learn.userfrosting.com/configuration)

---

**Importante:** Nunca compartir estos valores públicamente ni commitearlos al repositorio.

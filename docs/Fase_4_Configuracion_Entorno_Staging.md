# Configuración de Entorno para Staging

Este documento describe las variables de entorno requeridas para ejecutar la aplicación UserFrosting en el entorno de staging `https://pvuf.plazza.xyz/`.

## ⚠️ Política de Seguridad

**NUNCA** se deben almacenar credenciales o secretos en el repositorio. Todos los valores sensibles deben residir en:

1. **GitHub Actions Secrets** - Para uso durante el despliegue automatizado
2. **Archivo `.env` en el servidor** - Para ejecución en runtime

---

## Variables Requeridas

### 1. Configuración de Aplicación

| Variable | Descripción | Valor Staging | Ubicación |
|----------|-------------|---------------|-----------|
| `APP_ENV` | Entorno de ejecución | `staging` | GitHub Secrets + .env servidor |
| `APP_DEBUG` | Modo depuración | `false` | GitHub Secrets + .env servidor |
| `APP_URL` | URL base pública | `https://pvuf.plazza.xyz` | GitHub Secrets + .env servidor |
| `APP_KEY` | Clave de cifrado de sesiones | *Generado con `php -r "echo bin2hex(random_bytes(32));"`* | GitHub Secrets + .env servidor |

### 2. Base de Datos MariaDB

| Variable | Descripción | Valor Ejemplo | Ubicación |
|----------|-------------|---------------|-----------|
| `DB_CONNECTION` | Tipo de conexión | `mysql` | .env servidor |
| `DB_HOST` | Servidor de base de datos | `localhost` o hostname | GitHub Secrets + .env servidor |
| `DB_PORT` | Puerto | `3306` | GitHub Secrets + .env servidor |
| `DB_DATABASE` | Nombre de base de datos | `pvuf_staging` | GitHub Secrets + .env servidor |
| `DB_USERNAME` | Usuario de base de datos | *(proporcionado por hosting)* | GitHub Secrets + .env servidor |
| `DB_PASSWORD` | Contraseña de base de datos | *(proporcionado por hosting)* | GitHub Secrets + .env servidor |

### 3. Correo SMTP

| Variable | Descripción | Valor Ejemplo | Ubicación |
|----------|-------------|---------------|-----------|
| `MAIL_MAILER` | Driver de correo | `smtp` | .env servidor |
| `MAIL_HOST` | Servidor SMTP | *(proporcionado por hosting)* | GitHub Secrets + .env servidor |
| `MAIL_PORT` | Puerto SMTP | `587` (TLS) o `465` (SSL) | GitHub Secrets + .env servidor |
| `MAIL_USERNAME` | Usuario SMTP | *(proporcionado por hosting)* | GitHub Secrets + .env servidor |
| `MAIL_PASSWORD` | Contraseña SMTP | *(proporcionado por hosting)* | GitHub Secrets + .env servidor |
| `MAIL_ENCRYPTION` | Cifrado | `tls` o `ssl` | GitHub Secrets + .env servidor |
| `MAIL_FROM_ADDRESS` | Email remitente | `noreply@pvuf.plazza.xyz` | GitHub Secrets + .env servidor |
| `MAIL_FROM_NAME` | Nombre remitente | `PVUF` | .env servidor |

### 4. Sesiones y Cache

| Variable | Descripción | Valor Staging | Ubicación |
|----------|-------------|---------------|-----------|
| `SESSION_LIFETIME` | Minutos de sesión | `120` | .env servidor |
| `SESSION_DRIVER` | Driver de sesiones | `file` | .env servidor |
| `CACHE_DRIVER` | Driver de cache | `file` | .env servidor |

### 5. Logging

| Variable | Descripción | Valor Staging | Ubicación |
|----------|-------------|---------------|-----------|
| `LOG_LEVEL` | Nivel de log | `info` | .env servidor |

---

## GitHub Actions Secrets Requeridos

Los siguientes secretos deben configurarse en GitHub → Settings → Secrets and variables → Actions:

### Secretos de Despliegue SSH

- `DEPLOY_KEY` - Clave privada SSH (con passphrase)
- `DEPLOY_KEY_PASSPHRASE` - Passphrase de la clave SSH
- `DEPLOY_HOST` - Hostname del servidor (ej: `pvuf.plazza.xyz`)
- `DEPLOY_USER` - Usuario SSH
- `DEPLOY_PORT` - Puerto SSH (normalmente `22`)
- `DEPLOY_PATH` - Ruta absoluta en el servidor

### Secretos de Base de Datos

- `DB_HOST` - Servidor MariaDB
- `DB_PORT` - Puerto MariaDB
- `DB_DATABASE` - Nombre de la base de datos
- `DB_USERNAME` - Usuario de la base de datos
- `DB_PASSWORD` - Contraseña de la base de datos

### Secretos de Correo SMTP

- `MAIL_HOST` - Servidor SMTP
- `MAIL_PORT` - Puerto SMTP
- `MAIL_USERNAME` - Usuario SMTP
- `MAIL_PASSWORD` - Contraseña SMTP
- `MAIL_ENCRYPTION` - Tipo de cifrado (`tls` o `ssl`)
- `MAIL_FROM_ADDRESS` - Email remitente

### Secretos de Aplicación

- `APP_KEY` - Clave de cifrado (generada con `php -r "echo bin2hex(random_bytes(32));"`)

---

## Generación de APP_KEY

Para generar una clave de aplicación segura:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

Este comando genera una cadena hexadecimal de 64 caracteres que debe almacenarse en GitHub Secrets como `APP_KEY`.

---

## Flujo de Despliegue

1. **GitHub Actions** lee los secretos configurados
2. **Durante el build**, se construyen las dependencias con Composer
3. **Durante el despliegue**, se crea el archivo `.env` en el servidor usando los valores de los secretos
4. **El archivo `.env`** permanece en el servidor y **nunca** se versiona en el repositorio

---

## Verificación de Configuración

Después del despliegue, verificar que:

1. ✅ El archivo `.env` existe en el servidor con todos los valores
2. ✅ Las credenciales de base de datos permiten conexión
3. ✅ Las credenciales SMTP permiten envío de correo
4. ✅ Los permisos de `storage/` permiten escritura
5. ✅ No existen secretos en el repositorio (ejecutar `git log -p | grep -i password`)

---

## Troubleshooting

### Error: "Database connection failed"
- Verificar que `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` son correctos
- Verificar que la base de datos existe en MariaDB
- Verificar que el usuario tiene permisos sobre la base de datos

### Error: "Mail sending failed"
- Verificar que `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME` y `MAIL_PASSWORD` son correctos
- Verificar que `MAIL_ENCRYPTION` coincide con el puerto (`587`→`tls`, `465`→`ssl`)
- Probar conexión SMTP desde el servidor con `telnet`

### Error: "Session data could not be written"
- Verificar permisos del directorio `storage/sessions/`
- Ejecutar: `chmod -R 775 storage/`

---

## Referencias

- [Fase 3 - Matriz de Entornos](Fase_3_Environment_Matrix.md)
- [UserFrosting Documentation - Configuration](https://learn.userfrosting.com/configuration)
- [Laravel .env Configuration](https://laravel.com/docs/11.x/configuration#environment-configuration)

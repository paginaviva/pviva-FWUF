# PVUF - Guía de Configuración y Despliegue

## 📋 Descripción General

**PVUF** es un mini proyecto PHP de validación de despliegue automatizado para servidores compartidos. Valida:

1. **Que el servidor ejecuta PHP 8.3** (o superior)
2. **Que es posible un despliegue automatizado desde GitHub Actions** hacia el servidor mediante SSH (rsync) sin ejecutar herramientas en el servidor

### Características

- ✅ **Sin dependencias**: No requiere Composer, npm, Node.js ni herramientas de construcción en el servidor
- ✅ **Identificador de despliegue**: Muestra el commit hash y marca de tiempo para verificar que cada despliegue es diferente
- ✅ **Información de servidor**: Muestra la versión exacta de PHP ejecutándose en el servidor
- ✅ **Interfaz web moderna**: Página HTML/CSS clara y legible
- ✅ **Automatización**: GitHub Actions construye y despliega automáticamente en cada push a `main`

---

## 🔐 Configuración de Autenticación SSH

### Requisitos Previos

- Acceso SSH al servidor (`plazzaxy@pvuf.plazza.xyz`)
- Clave pública generada y lista para instalar (ver [SSH_KEYS.md](SSH_KEYS.md))

### Paso 1: Instalar la Clave Pública en el Servidor

1. **Conéctate al servidor** usando tus credenciales habituales:
   ```bash
   ssh plazzaxy@pvuf.plazza.xyz
   ```

2. **Crea el directorio `.ssh` si no existe:**
   ```bash
   mkdir -p ~/.ssh
   chmod 700 ~/.ssh
   ```

3. **Añade la clave pública a `authorized_keys`:**
   
   Copia la siguiente línea completa desde [SSH_KEYS.md](SSH_KEYS.md) (sección "Clave Pública"):
   ```bash
   echo "ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIKSXyxpc3kEAdt19phJ2IF1nRnF4YVSts9rMig+DOmsm pvuf-github-actions-deploy" >> ~/.ssh/authorized_keys
   ```

4. **Configura los permisos correctamente:**
   ```bash
   chmod 600 ~/.ssh/authorized_keys
   chmod 700 ~/.ssh
   ```

5. **Verifica que fue instalada:**
   ```bash
   cat ~/.ssh/authorized_keys | grep pvuf-github-actions-deploy
   ```

### Paso 2: Crear los Secretos de GitHub

Ve a tu repositorio en GitHub y crea los siguientes secretos en **Settings > Secrets and variables > Actions**:

| Nombre Exacto | Valor | Descripción |
|---|---|---|
| `DEPLOY_HOST` | `pvuf.plazza.xyz` | Dominio del servidor |
| `DEPLOY_USER` | `plazzaxy` | Usuario SSH del servidor |
| `DEPLOY_PORT` | `22` | Puerto SSH (por defecto 22) |
| `DEPLOY_PATH` | `/home/plazzaxy/pvuf.plazza.xyz` | Ruta remota del document root |
| `DEPLOY_KEY` | *[clave privada completa]* | Clave privada SSH sin contraseña |

**⚠️ Importante:** Copia la clave privada **exactamente como aparece** en [SSH_KEYS.md](SSH_KEYS.md) (desde `-----BEGIN OPENSSH PRIVATE KEY-----` hasta `-----END OPENSSH PRIVATE KEY-----`).

### Paso 3: Verificar la Instalación (Opcional)

Desde GitHub Codespaces o tu máquina local, prueba la conexión:

```bash
# Copia la clave privada a un archivo local (desde SSH_KEYS.md)
cat > ~/.ssh/pvuf_key << 'EOF'
-----BEGIN OPENSSH PRIVATE KEY-----
[Pega aquí el contenido completo]
-----END OPENSSH PRIVATE KEY-----
EOF

chmod 600 ~/.ssh/pvuf_key

# Intenta conectar
ssh -i ~/.ssh/pvuf_key plazzaxy@pvuf.plazza.xyz "php --version"
```

Si ves algo como `PHP 8.3.0` (o superior), ¡la configuración es correcta!

---

## 🚀 Despliegue Automático

### Activación Automática

El workflow se ejecuta automáticamente cada vez que haces un push a la rama `main`:

1. **Haz un commit y push:**
   ```bash
   git add index.php build.json
   git commit -m "Initial PVUF deployment setup"
   git push origin main
   ```

2. **Verifica el workflow:**
   - Ve a tu repositorio en GitHub
   - Abre la pestaña **Actions**
   - Deberías ver un workflow llamado "Deploy PVUF to Shared Hosting"
   - Espera a que termine (usualmente 1-2 minutos)

### Fases del Workflow

El workflow ejecuta estas fases en orden:

1. **Checkout:** Descarga el código del repositorio
2. **Generate deployment identifier:** Crea `build.json` con:
   - Commit hash completo y corto
   - Marca de tiempo de construcción (UTC)
   - Fecha legible de construcción
3. **Verify build artifact:** Confirma que los archivos están listos
4. **Setup SSH Key:** Configura la autenticación SSH con los secretos
5. **Test SSH connection:** Valida que SSH funciona y que PHP está disponible
6. **Deploy files via rsync:** Sincroniza los archivos al servidor
7. **Verify deployment:** Confirma que los archivos llegaron correctamente
8. **Create deployment summary:** Genera un resumen del despliegue

---

## ✅ Verificación del Despliegue

### Verificación Visual en la Web

1. **Abre el sitio:**
   ```
   https://pvuf.plazza.xyz/
   ```

2. **Verifica que ves:**
   - ✅ **Versión de PHP:** Debe mostrar "PHP 8.3.x" o superior
   - ✅ **Commit Hash (Corto):** Debe ser el mismo que viste en GitHub Actions
   - ✅ **Marca de Tiempo de Construcción:** Debe ser reciente (dentro del último minuto del push)
   - ✅ **Entorno:** "Prueba de Despliegue"

### Evidencia de PHP 8.3

La página mostrará algo como:

```
Estado de PHP
PHP 8.3.0 - Compatible

Identificador de Despliegue
Commit Hash (Corto): abc1234
Marca de Tiempo de Construcción: 2025-12-30T15:45:32Z
Fecha de Construcción (Legible): 2025-12-30 15:45:32 UTC

Entorno
Nombre del Entorno: Prueba de Despliegue
```

### Prueba de Actualización (Segunda Verificación)

1. **Modifica un archivo cualquiera** (por ejemplo, README.md):
   ```bash
   echo "# Actualización de prueba" >> README.md
   git add README.md
   git commit -m "Test deployment update"
   git push origin main
   ```

2. **Espera a que el workflow termine** (ve a Actions)

3. **Recarga `https://pvuf.plazza.xyz/`** en el navegador (sin caché: Ctrl+Shift+R)

4. **Verifica que:**
   - El Commit Hash cambió (es diferente al anterior)
   - La Marca de Tiempo de Construcción es más reciente
   - La página se ve diferente (prueba de que hubo despliegue real)

---

## 🐛 Solución de Problemas

### Error 1: "Permission denied (publickey)"

**Síntoma:** El workflow falla en "Test SSH connection" o "Deploy files via rsync"

**Posibles causas:**
- La clave pública no está en el servidor
- Permisos incorrectos en `~/.ssh` o `~/.ssh/authorized_keys`
- El secreto `DEPLOY_KEY` está incompleto o corrupto

**Solución:**
1. Verifica en el servidor:
   ```bash
   ssh plazzaxy@pvuf.plazza.xyz
   ls -la ~/.ssh/
   cat ~/.ssh/authorized_keys | grep pvuf-github-actions-deploy
   ```
2. Los permisos deben ser:
   - `~/.ssh`: 700 (drwx------)
   - `~/.ssh/authorized_keys`: 600 (-rw-------)
3. Recopia la clave pública exactamente desde [SSH_KEYS.md](SSH_KEYS.md)

### Error 2: "rsync not found" o "scp not found"

**Síntoma:** El workflow falla con "command not found: rsync"

**Causa:** El servidor no tiene rsync instalado

**Solución:**
- Contacta al proveedor de hosting para que instale rsync o ssh
- El workflow intenta usar rsync; si falla, modifícalo para usar `scp` en su lugar (ver alternativa abajo)

**Alternativa (usar scp en lugar de rsync):**

Si rsync no está disponible, reemplaza el paso "Deploy files via rsync" en [.github/workflows/deploy.yml](.github/workflows/deploy.yml) con:

```yaml
- name: Deploy files via scp
  run: |
    scp -P ${{ secrets.DEPLOY_PORT || 22 }} \
        index.php build.json \
        ${{ secrets.DEPLOY_USER }}@${{ secrets.DEPLOY_HOST }}:${{ secrets.DEPLOY_PATH }}/
```

### Error 3: "Could not resolve hostname"

**Síntoma:** El workflow falla con "Could not resolve hostname pvuf.plazza.xyz"

**Causa:** Problema de DNS o dominio incorrecto

**Solución:**
1. Verifica que el secreto `DEPLOY_HOST` es exacto: `pvuf.plazza.xyz`
2. Desde tu máquina local o Codespaces, intenta:
   ```bash
   ping pvuf.plazza.xyz
   ssh -T plazzaxy@pvuf.plazza.xyz
   ```
3. Si falla, verifica que el dominio está activo y accesible

### Error 4: "No such file or directory: /home/plazzaxy/pvuf.plazza.xyz"

**Síntoma:** El workflow falla con error de ruta remota

**Causa:** La ruta no existe en el servidor

**Solución:**
1. Conéctate al servidor y crea la ruta:
   ```bash
   ssh plazzaxy@pvuf.plazza.xyz
   mkdir -p /home/plazzaxy/pvuf.plazza.xyz
   ```
2. Verifica que tienes permisos de escritura:
   ```bash
   touch /home/plazzaxy/pvuf.plazza.xyz/test.txt
   rm /home/plazzaxy/pvuf.plazza.xyz/test.txt
   ```
3. Si falta algo, contacta al proveedor de hosting

### Error 5: "Connection timeout" o puerto SSH incorrecto

**Síntoma:** El workflow espera indefinidamente o falla con timeout

**Causa:** Puerto SSH incorrecto o firewall bloqueando

**Solución:**
1. Verifica que el secreto `DEPLOY_PORT` es correcto (usualmente `22`)
2. Desde tu máquina local, intenta:
   ```bash
   ssh -p 22 plazzaxy@pvuf.plazza.xyz
   ```
3. Si tampoco funciona, el proveedor de hosting quizás usa otro puerto; contacta a soporte

### Error 6: La página muestra "not yet deployed"

**Síntoma:** `https://pvuf.plazza.xyz/` carga, pero muestra:
- Commit Hash: "0000000000000000000000000000000000000000"
- Marca de Tiempo: "1970-01-01T00:00:00Z"

**Causa:** El archivo `build.json` no fue actualizado en el servidor

**Solución:**
1. Verifica en el servidor:
   ```bash
   ssh plazzaxy@pvuf.plazza.xyz
   ls -la /home/plazzaxy/pvuf.plazza.xyz/
   cat /home/plazzaxy/pvuf.plazza.xyz/build.json
   ```
2. Si falta `build.json` o está desactualizado, el workflow no terminó correctamente
3. Ve a GitHub Actions y revisa los logs para errores de rsync/scp

---

## 📁 Estructura del Proyecto

```
pviva-FWUF/
├── index.php                    # Página principal con validación
├── build.json                   # Identificador de despliegue (generado por GA)
├── DEPLOYMENT.md                # Este archivo
├── SSH_KEYS.md                  # Configuración de claves SSH
├── README.md                    # Descripción del proyecto
└── .github/
    └── workflows/
        └── deploy.yml           # Workflow de despliegue automático
```

**Archivos desplegados en el servidor:**
- `index.php`
- `build.json`

**Archivos NO desplegados** (excluidos por el workflow):
- `.git*` (repositorio Git)
- `.github` (configuración de GitHub)
- `*.md` (documentación)

---

## 🔄 Ciclo de Vida del Despliegue

```
1. Haces un push a 'main' en GitHub
        ↓
2. GitHub Actions dispara el workflow "Deploy PVUF to Shared Hosting"
        ↓
3. El workflow:
   a. Genera build.json con timestamp y commit hash
   b. Configura SSH usando los secretos
   c. Prueba la conexión SSH
   d. Sincroniza index.php y build.json al servidor vía rsync
   e. Verifica que llegaron correctamente
        ↓
4. Los archivos están ahora en /home/plazzaxy/pvuf.plazza.xyz/
        ↓
5. El servidor web (Apache/Nginx) sirve index.php
        ↓
6. El navegador carga la página y muestra:
   - Versión de PHP (detectada en tiempo de ejecución)
   - Commit hash y marca de tiempo (desde build.json)
```

---

## 📋 Lista de Verificación Final

- [ ] La clave pública está instalada en `~/.ssh/authorized_keys` del servidor
- [ ] Los permisos de `.ssh` y `authorized_keys` son correctos (700 y 600)
- [ ] Todos los 5 secretos de GitHub están creados con nombres exactos
- [ ] El secreto `DEPLOY_KEY` contiene la clave privada completa (sin truncar)
- [ ] El directorio `/home/plazzaxy/pvuf.plazza.xyz` existe en el servidor
- [ ] Tengo permisos de escritura en ese directorio
- [ ] El workflow en GitHub Actions termina en verde (success)
- [ ] `https://pvuf.plazza.xyz/` muestra PHP 8.3 (o superior)
- [ ] El commit hash en la página coincide con el último push
- [ ] Un segundo push resulta en commit hash diferente

---

## 📞 Contacto y Soporte

Si encuentras problemas:

1. Revisa esta guía en la sección "Solución de Problemas"
2. Consulta los logs del workflow en GitHub Actions
3. Verifica los logs SSH del servidor:
   ```bash
   ssh plazzaxy@pvuf.plazza.xyz
   tail -50 ~/.ssh/authorized_keys
   ```
4. Contacta al proveedor de hosting si hay problemas de servidor

---

**Última actualización:** 2025-12-30  
**Versión:** 1.0  
**Soporte:** GitHub Actions + SSH + rsync

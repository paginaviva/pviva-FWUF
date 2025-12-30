# PVUF - Configuración de Claves SSH para Despliegue

## Par de Claves Generado

Este archivo contiene las instrucciones para instalar las claves SSH que permiten a GitHub Actions desplegar automáticamente el proyecto en el servidor compartido.

### Clave Privada (para GitHub Secrets)

**ADVERTENCIA:** La siguiente clave privada debe almacenarse únicamente en GitHub Secrets. No debe ser compartida ni comprometida. Si alguna vez la expones, regénerala inmediatamente.

```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtzc2gtZW
QyNTUxOQAAACCkl8saXN5BAHbdfaYSdiBdZ0ZxeGFUrbPazIoPgzprJgAAAKAQcwLWEHMC
1gAAAAtzc2gtZWQyNTUxOQAAACCkl8saXN5BAHbdfaYSdiBdZ0ZxeGFUrbPazIoPgzprJg
AAAEAZVTfJ/NuMJfnXFw1fAalHGhyG46cPScO55zjEpOXlWaSXyxpc3kEAdt19phJ2IF1n
RnF4YVSts9rMig+DOmsmAAAAGnB2dWYtZ2l0aHViLWFjdGlvbnMtZGVwbG95AQID
-----END OPENSSH PRIVATE KEY-----
```

### Clave Pública (para el servidor)

La siguiente clave debe añadirse al archivo `~/.ssh/authorized_keys` del usuario `plazzaxy` en el servidor:

```
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIKSXyxpc3kEAdt19phJ2IF1nRnF4YVSts9rMig+DOmsm pvuf-github-actions-deploy
```

### Información de la Clave

- **Tipo:** ED25519 (altamente segura, recomendada para GitHub Actions)
- **Huella Digital (SHA256):** `SHA256:ayS7UYOrxsLh1/KS5Wy8KVr9Dnp15XI28TEMcTUb9tQ`
- **Comentario:** `pvuf-github-actions-deploy`
- **Contraseña:** Ninguna (sin frase de contraseña)

### Instalación en el Servidor

1. Conecta al servidor:
   ```bash
   ssh -u plazzaxy pvuf.plazza.xyz
   ```

2. Asegúrate de que existe el directorio `.ssh`:
   ```bash
   mkdir -p ~/.ssh
   chmod 700 ~/.ssh
   ```

3. Añade la clave pública a `authorized_keys`:
   ```bash
   echo "ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIKSXyxpc3kEAdt19phJ2IF1nRnF4YVSts9rMig+DOmsm pvuf-github-actions-deploy" >> ~/.ssh/authorized_keys
   ```

4. Asegúrate de que los permisos son correctos:
   ```bash
   chmod 600 ~/.ssh/authorized_keys
   ```

5. Verifica que la clave funciona desde una máquina local (o desde GitHub Codespaces):
   ```bash
   ssh -i /path/to/private_key plazzaxy@pvuf.plazza.xyz
   ```

### Secretos de GitHub a Configurar

Ve a **Settings > Secrets and variables > Actions** en el repositorio de GitHub y crea los siguientes secretos exactamente con estos nombres:

| Nombre del Secreto | Valor | Descripción |
|---|---|---|
| `DEPLOY_HOST` | `pvuf.plazza.xyz` | Dominio o IP del servidor |
| `DEPLOY_USER` | `plazzaxy` | Usuario SSH en el servidor |
| `DEPLOY_PORT` | `22` | Puerto SSH (por defecto 22) |
| `DEPLOY_PATH` | `/home/plazzaxy/pvuf.plazza.xyz` | Ruta remota del document root |
| `DEPLOY_KEY` | *[la clave privada de arriba]* | Clave privada SSH (completa, tal cual) |

### Verificación de la Configuración

Después de instalar la clave en el servidor y configurar los secretos en GitHub:

1. Haz un push a la rama `main`
2. Ve a **Actions** en el repositorio y espera a que el workflow finalice
3. Si la construcción y despliegue son exitosos, verás un checkmark verde
4. Abre `https://pvuf.plazza.xyz/` en el navegador
5. Verifica que:
   - Se muestra **PHP 8.3** (o la versión del servidor)
   - El **commit hash corto** coincide con tu último push
   - La **marca de tiempo de construcción** es reciente

### Plan Alternativo: Clave con Contraseña

Si el servidor requiere una contraseña (frase de paso) en la clave SSH:

1. **Regenera la clave con contraseña:**
   ```bash
   ssh-keygen -t ed25519 -f ~/.ssh/pvuf_deploy_key -C "pvuf-github-actions-deploy"
   ```
   (Cuando se te pida, introduce una contraseña)

2. **Almacena la contraseña también en GitHub Secrets:**
   - Nombre: `DEPLOY_KEY_PASSPHRASE`
   - Valor: La contraseña que elegiste

3. **Modifica el workflow de GitHub Actions para usar la contraseña:**
   ```yaml
   - name: Add SSH Key with Passphrase
     env:
       SSH_KEY: ${{ secrets.DEPLOY_KEY }}
       SSH_PASSPHRASE: ${{ secrets.DEPLOY_KEY_PASSPHRASE }}
     run: |
       mkdir -p ~/.ssh
       echo "$SSH_KEY" > ~/.ssh/id_ed25519
       chmod 600 ~/.ssh/id_ed25519
       echo "$DEPLOY_HOST" >> ~/.ssh/known_hosts
   ```

   Luego usa `sshpass` o configura el agente SSH en el workflow. Ver documentación oficial de GitHub Actions.

### Regeneración de Claves

Si alguna vez necesitas regenerar las claves (por ejemplo, si sospechas que están comprometidas):

1. Genera nuevas claves siguiendo el mismo proceso
2. Reemplaza la clave pública en el servidor en `~/.ssh/authorized_keys`
3. Actualiza los secretos `DEPLOY_KEY` y `DEPLOY_KEY_PASSPHRASE` (si aplica) en GitHub
4. Prueba con un push a una rama de prueba

### Solución de Problemas

**Error: "Permission denied (publickey)"**
- Verifica que la clave pública está en `~/.ssh/authorized_keys` con permisos 600
- Verifica que el directorio `~/.ssh` tiene permisos 700
- Confirma que los secretos de GitHub están configurados correctamente

**Error: "Could not resolve hostname"**
- Verifica que `DEPLOY_HOST` es correcto (`pvuf.plazza.xyz`)
- Intenta hacer ping desde tu máquina local para confirmar que el dominio es alcanzable

**Error: "rsync not found" o "scp not found"**
- En el servidor, asegúrate de que rsync o scp están instalados
- Contacta al proveedor de hosting si no están disponibles

**El despliegue se ejecuta pero la página no se actualiza**
- Verifica que `DEPLOY_PATH` es `/home/plazzaxy/pvuf.plazza.xyz` (sin barras finales)
- Comprueba los permisos de escritura en esa carpeta en el servidor
- Revisa los logs del workflow en GitHub Actions para más detalles

---

**Generado:** 2025-12-30
**Clave SSH Fingerprint:** `SHA256:ayS7UYOrxsLh1/KS5Wy8KVr9Dnp15XI28TEMcTUb9tQ`

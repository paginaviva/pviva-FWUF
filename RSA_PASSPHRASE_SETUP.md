# PVUF - Configuración para Clave SSH RSA con Contraseña (cPanel)

## 📋 Tu Información de Clave SSH

**Tipo de Clave:** RSA 2048 (generada en cPanel)  
**Fingerprint:** `SHA256:za/KxGJVZYlyXZolyvjyD/ohBLVqOPjxfkntM0u91qY`  
**Contraseña:** `z5PiIA9ddjTPqIX8`  
**Estado:** ✅ Clave pública ya instalada en servidor

---

## 🔐 Paso 1: Descargar la Clave Privada de cPanel

Si aún no la has descargado:

1. En cPanel, ve a **SSH Access** o **SSH/TLS Certificates**
2. Busca la clave generada (nombre algo como `pvuf-deploy` o similar)
3. Descarga el archivo `.key` (contiene la clave privada RSA)
4. Guárdalo en lugar seguro (en tu máquina local, no en Codespaces)

**Contenido esperado del archivo:**
```
-----BEGIN RSA PRIVATE KEY-----
[líneas largas de caracteres base64]
-----END RSA PRIVATE KEY-----
```

---

## 💻 Paso 2: Crear Secretos en GitHub

### Secreto 1: DEPLOY_KEY (La clave privada RSA)

1. Abre: **https://github.com/paginaviva/pviva-FWUF/settings/secrets/actions**

2. Click **"New repository secret"**

3. Completa el formulario:
   - **Name:** `DEPLOY_KEY`
   - **Value:** 
     - Abre el archivo `.key` descargado de cPanel
     - Copia **TODO EL CONTENIDO** (desde `-----BEGIN RSA PRIVATE KEY-----` hasta `-----END RSA PRIVATE KEY-----`)
     - Pégalo exactamente en el campo Value
   
4. Click **"Add secret"**

### Secreto 2: DEPLOY_KEY_PASSPHRASE (La contraseña)

1. Click **"New repository secret"** de nuevo

2. Completa el formulario:
   - **Name:** `DEPLOY_KEY_PASSPHRASE`
   - **Value:** `z5PiIA9ddjTPqIX8`
   
3. Click **"Add secret"**

### Verificación

Después deberías tener estos secretos en GitHub:

```
✅ DEPLOY_HOST               (valor: pvuf.plazza.xyz)
✅ DEPLOY_USER               (valor: plazzaxy)
✅ DEPLOY_PORT               (valor: 22)
✅ DEPLOY_PATH               (valor: /home/plazzaxy/pvuf.plazza.xyz)
✅ DEPLOY_KEY                (valor: tu clave privada RSA)
✅ DEPLOY_KEY_PASSPHRASE     (valor: z5PiIA9ddjTPqIX8)
```

---

## 💾 Paso 3: Hacer el Push desde Codespaces

Abre una terminal en Codespaces y ejecuta:

```bash
# Navega al directorio del proyecto
cd /workspaces/pviva-FWUF

# Verifica que los archivos están listos
ls -la index.php build.json .github/workflows/deploy.yml

# Agrega todos los cambios
git add .

# Crea un commit
git commit -m "Deploy with RSA SSH key and passphrase support"

# Sube a GitHub
git push origin main
```

### O en una sola línea:

```bash
cd /workspaces/pviva-FWUF && git add . && git commit -m "Deploy with RSA SSH key and passphrase" && git push origin main
```

---

## 🚀 Paso 4: Verificar el Despliegue

### En GitHub Actions

1. Ve a: **https://github.com/paginaviva/pviva-FWUF/actions**
2. Deberías ver el workflow "Deploy PVUF to Shared Hosting" ejecutándose
3. Espera 2-3 minutos a que termine
4. Cuando veas ✅ verde, el despliegue fue exitoso

### En el Navegador

1. Abre: **https://pvuf.plazza.xyz/**
2. Verifica que ves:
   - ✅ **PHP 8.3+** con estado "Compatible"
   - ✅ **Commit hash** (últimos 7 caracteres del push)
   - ✅ **Build timestamp** reciente (en UTC)
   - ✅ **Entorno:** "Prueba de Despliegue"

---

## 🔧 Cómo Funciona con Contraseña

El workflow de GitHub Actions ahora:

1. **Lee la clave privada** desde el secreto `DEPLOY_KEY`
2. **Lee la contraseña** desde el secreto `DEPLOY_KEY_PASSPHRASE`
3. **Inicia ssh-agent** para manejar la contraseña
4. **Añade la clave** al agente SSH con la contraseña
5. **Usa SSH** sin necesidad de escribir la contraseña manualmente

Este proceso es seguro porque:
- Los secretos se mantienen privados en GitHub
- La contraseña nunca aparece en los logs públicos
- El runner de GitHub Actions es efímero (se elimina después)

---

## ❌ Si Algo Sale Mal

### Error: "Permission denied (publickey)"

**Posibles causas:**
1. La clave privada (`DEPLOY_KEY`) no coincide con la pública instalada
2. La contraseña (`DEPLOY_KEY_PASSPHRASE`) es incorrecta

**Solución:**
```bash
# Verifica el fingerprint en el servidor
ssh plazzaxy@pvuf.plazza.xyz "ssh-keygen -l -f ~/.ssh/authorized_keys"

# Debería mostrar: SHA256:za/KxGJVZYlyXZolyvjyD/ohBLVqOPjxfkntM0u91qY

# Si no coincide, regenera la clave en cPanel
```

### Error: "Passphrase incorrect"

**Solución:**
1. Verifica que escribiste exactamente: `z5PiIA9ddjTPqIX8` en `DEPLOY_KEY_PASSPHRASE`
2. Las contraseñas son case-sensitive
3. No debe haber espacios al principio o final

### El workflow no se dispara

**Solución:**
1. Verifica que hiciste `git push` a la rama `main` (no otra rama)
2. Ve a: **https://github.com/paginaviva/pviva-FWUF/actions**
3. Mira los logs para ver qué falló

---

## 📝 Resumen de Pasos

| # | Acción | Donde | Estado |
|---|--------|-------|--------|
| 1 | Descargar clave privada RSA de cPanel | cPanel | ✅ Hecho |
| 2 | Crear secreto `DEPLOY_KEY` | GitHub | ⬅️ Ahora |
| 3 | Crear secreto `DEPLOY_KEY_PASSPHRASE` | GitHub | ⬅️ Ahora |
| 4 | Hacer git push a main | Codespaces | ⬅️ Después |
| 5 | GitHub Actions se ejecuta | GitHub | Automático |
| 6 | Verificar en navegador | https://pvuf.plazza.xyz/ | Resultado |

---

## 🎯 Checklist Final

- [ ] Descargué la clave privada RSA de cPanel
- [ ] Creé el secreto `DEPLOY_KEY` con la clave privada completa
- [ ] Creé el secreto `DEPLOY_KEY_PASSPHRASE` con la contraseña `z5PiIA9ddjTPqIX8`
- [ ] Hice `git push` desde Codespaces
- [ ] GitHub Actions muestra checkmark verde ✅
- [ ] https://pvuf.plazza.xyz/ carga y muestra PHP 8.3+
- [ ] El commit hash en la página coincide con mi push

---

**Generado:** 2025-12-30  
**Para:** Clave SSH RSA con Contraseña  
**Status:** Listo para configurar

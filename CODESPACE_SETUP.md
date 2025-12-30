# PVUF - Configuración en Codespaces (Con Clave RSA)

## 📋 Información de tu Clave SSH Generada

Hiciste una clave SSH RSA en cPanel con contraseña. Aquí está la información que necesitas:

```
Tipo de Clave:        RSA 2048
Fingerprint:          SHA256:za/KxGJVZYlyXZolyvjyD/ohBLVqOPjxfkntM0u91qY
Contraseña:           z5PiIA9ddjTPqIX8
Estado:               ✅ Generada y lista para usar
```

---

## 🔐 Paso 2: Crear Secretos en GitHub

### Qué necesitas copiar:

**A) DEPLOY_KEY** (La clave privada RSA)
- **Dónde conseguirla:** El archivo `.key` que descargaste de cPanel (llámalo `pvuf-deploy.key` o similar)
- **Qué es:** Tu clave privada RSA completa (empieza con `-----BEGIN RSA PRIVATE KEY-----`)
- **Dónde ponerla:** GitHub Secrets, secreto `DEPLOY_KEY`

**B) DEPLOY_KEY_PASSPHRASE** (La contraseña)
- **Qué es:** La contraseña que usaste en cPanel
- **Valor exacto:** `z5PiIA9ddjTPqIX8`
- **Dónde ponerla:** GitHub Secrets, secreto `DEPLOY_KEY_PASSPHRASE`

### Pasos en GitHub:

1. Ve a: **https://github.com/paginaviva/pviva-FWUF/settings/secrets/actions**

2. Click en **"New repository secret"**

3. **Primera vez - Crea `DEPLOY_KEY`:**
   - Name: `DEPLOY_KEY`
   - Value: [Copia el contenido COMPLETO de tu archivo `.key` de cPanel]
   - Click "Add secret"

4. **Segunda vez - Crea `DEPLOY_KEY_PASSPHRASE`:**
   - Name: `DEPLOY_KEY_PASSPHRASE`
   - Value: `z5PiIA9ddjTPqIX8`
   - Click "Add secret"

5. Resultado final: Deberías ver 7 secretos en total:
   ```
   ✅ DEPLOY_HOST
   ✅ DEPLOY_USER
   ✅ DEPLOY_PORT
   ✅ DEPLOY_PATH
   ✅ DEPLOY_KEY              ← Nuevo
   ✅ DEPLOY_KEY_PASSPHRASE   ← Nuevo
   ```

---

## 💻 Paso 3: Ejecutar en Codespaces

Abre una terminal en Codespaces y ejecuta estos comandos:

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

### ¿Qué pasa después?

1. GitHub Actions se activará automáticamente
2. Verás el workflow ejecutándose en: **https://github.com/paginaviva/pviva-FWUF/actions**
3. Espera 2-3 minutos a que termine
4. Cuando veas el checkmark ✅ verde, el despliegue fue exitoso
5. Abre: **https://pvuf.plazza.xyz/**
6. Verifica que ves:
   - ✅ PHP 8.3+
   - ✅ Commit hash
   - ✅ Build timestamp reciente

---

## 🔍 Si algo falla...

### Error: "Permission denied (publickey)"
- Verifica que la clave pública está en `~/.ssh/authorized_keys` del servidor
- Fingerprint esperado: `SHA256:za/KxGJVZYlyXZolyvjyD/ohBLVqOPjxfkntM0u91qY`

### Error: "Passphrase incorrect"
- Verifica que `DEPLOY_KEY_PASSPHRASE` = `z5PiIA9ddjTPqIX8` exactamente
- La contraseña es case-sensitive

### El workflow no se dispara
- Verifica que hiciste `git push` a la rama `main`
- Ve a Actions y mira los logs: **https://github.com/paginaviva/pviva-FWUF/actions**

---

## 📝 Resumen Final

| Paso | Acción | Estado |
|------|--------|--------|
| 1 | Clave pública instalada en servidor (cPanel lo hizo) | ✅ |
| 2 | Crear secretos en GitHub (DEPLOY_KEY + DEPLOY_KEY_PASSPHRASE) | ⬅️ Ahora |
| 3 | Push a main desde Codespaces | ⬅️ Después |
| 4 | GitHub Actions se ejecuta automáticamente | Luego |
| 5 | Verificar en https://pvuf.plazza.xyz/ | Resultado |

---

## 💡 Comandos Rápidos (Copia/Pega)

Si tienes prisa, copia esto entero en la terminal de Codespaces:

```bash
cd /workspaces/pviva-FWUF && git add . && git commit -m "Deploy with RSA SSH key and passphrase" && git push origin main
```

Listo. Ya está.

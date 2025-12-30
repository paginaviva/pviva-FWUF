# Verificación y Corrección de la Clave RSA desde cPanel

## Problema Identificado
El workflow falló en la autenticación SSH con el error:
```
Permission denied (publickey,gssapi-keyex,gssapi-with-mic)
```

La causa más probable es que **la clave privada RSA fue copiada incorrectamente con espacios, saltos de línea adicionales, o caracteres invisibles**.

## Paso 1: Verificar la Clave en GitHub
1. Abre tu repositorio en GitHub
2. Ve a **Settings > Secrets and variables > Actions**
3. Encuentra el secret `DEPLOY_KEY`
4. Haz click en **"Edit"**
5. Mira el valor completo

**Qué buscar:**
- ✅ Debe empezar exactamente con: `-----BEGIN RSA PRIVATE KEY-----`
- ✅ Debe terminar exactamente con: `-----END RSA PRIVATE KEY-----`
- ❌ NO debe haber espacios antes del `BEGIN`
- ❌ NO debe haber espacios después del `END`
- ❌ NO debe haber líneas vacías al principio o al final

## Paso 2: Recopiar la Clave desde cPanel (Correcto)

### Opción A: Si tienes acceso a cPanel
1. Inicia sesión en cPanel
2. Ve a **SSH Access > Manage SSH Keys**
3. Busca tu clave (debería estar listada como "Authorized Keys")
4. Haz click en **"Manage"** > **"View Public Key"** para verificar que es la correcta
5. Luego haz click en **"Private Key"** (o ve a **Home > File Manager** si no aparece)
6. Si está en el archivo (ej: `/home/plazzaxy/.ssh/id_rsa`):
   - Haz click derecho > **View**
   - Selecciona TODA la clave (incluyendo `-----BEGIN` y `-----END`)
   - Copia (Ctrl+C)
   - **IMPORTANTE**: Asegúrate de que la selección comience en el `-----` inicial

### Opción B: Si descargaste la clave (.key file)
1. Abre el archivo `.key` con un editor de texto (Notepad++, VS Code, etc.)
2. Selecciona TODO el contenido (Ctrl+A)
3. Copia (Ctrl+C)
4. Verifica que incluya `-----BEGIN RSA PRIVATE KEY-----` al inicio
5. Verifica que incluya `-----END RSA PRIVATE KEY-----` al final

## Paso 3: Actualizar el Secret en GitHub
1. Ve a tu repositorio > **Settings > Secrets and variables > Actions**
2. Haz click en `DEPLOY_KEY` > **Edit**
3. **Borra completamente** el valor actual
4. **Pega** la clave nueva que copiaste (sin espacios extras)
5. **Ahora es importante**: Desplázate al inicio del textarea
   - Verifica que NO haya espacios antes de `-----BEGIN`
   - Desplázate al final del textarea
   - Verifica que NO haya espacios después de `-----END`
6. Haz click en **Update secret**

## Paso 4: Actualizar el Secret de Passphrase
1. Abre `DEPLOY_KEY_PASSPHRASE`
2. Verifica el valor: `z5PiIA9ddjTPqIX8`
3. Asegúrate de que sea exacto (sin espacios)
4. Si no, actualízalo

## Paso 5: Ejecutar Nuevo Despliegue

En Codespaces, haz push de una cambio pequeño para disparar el workflow:

```bash
# En la carpeta /workspaces/pviva-FWUF:
git add .github/workflows/deploy.yml
git commit -m "Fix SSH key handling and add debug output"
git push origin main
```

Luego verifica en GitHub **Actions** tab:
- Busca el nuevo workflow run
- Abre el job "Build and Deploy to pvuf.pl..."
- Busca la sección "Setup SSH Key"
- Debería decir: `Key file exists: YES` y mostrar el tipo de clave

Si ves un error como `Key format is invalid!`, significa que la clave sigue sin estar bien copiada.

## Paso 6: Validar Desde cPanel

Si el workflow sigue fallando, necesitamos validar que la clave pública está realmente autorizada en el servidor.

1. **Inicia sesión en cPanel**
2. Ve a **SSH Access > Manage SSH Keys**
3. Verifica que exista una clave en **Authorized Keys**
4. Haz click en **View Public Key** para esa clave
5. Debería empezar con `ssh-rsa AAAAB3...`

**Compara el fingerprint:**
- En el workflow log, busca: `RSA SHA256:za/KxGJVZYlyXZolyvjyD/ohBLVqOPjxfkntM0u91qY`
- En cPanel, en la lista de claves autorizadas, debería verse el mismo fingerprint

Si los fingerprints no coinciden, significa que:
- La clave privada en GitHub no corresponde con la clave pública en el servidor
- Necesitarías generar un nuevo par y actualizar ambos lados

## Paso 7: Verificar Manualmente (Opcional)

Si quieres verificar tu clave RSA sin GitHub, puedes abrir el archivo `.key` y validarlo:

```bash
# En Terminal/PowerShell del tu máquina local:
# Reemplaza path/to/key.key con la ruta al archivo

# Ver el fingerprint de la clave privada
ssh-keygen -l -f path/to/key.key

# Verificar formato
grep -c "^-----BEGIN RSA PRIVATE KEY-----$" path/to/key.key  # Debería dar: 1
grep -c "^-----END RSA PRIVATE KEY-----$" path/to/key.key    # Debería dar: 1
```

---

## Checklist Final
- [ ] Revisé el secret `DEPLOY_KEY` en GitHub sin espacios extras
- [ ] Recopié la clave directamente desde cPanel (archivo `.key` o mediante View)
- [ ] Eliminé completamente el valor anterior en GitHub
- [ ] Pegué la clave nueva SIN espacios al inicio o final
- [ ] Verifiqué que `DEPLOY_KEY_PASSPHRASE` sea `z5PiIA9ddjTPqIX8`
- [ ] Hice git push de nuevo
- [ ] Verifiqué el nuevo workflow run en GitHub Actions
- [ ] Vi "Key file exists: YES" en la sección "Setup SSH Key"

Si después de esto sigue fallando, comparte el error completo del workflow y debuggearemos desde ahí.

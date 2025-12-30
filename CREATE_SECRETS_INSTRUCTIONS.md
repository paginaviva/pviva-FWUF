# Crea los Secretos en GitHub - Instrucciones Exactas

## 🔗 Ve a Esta URL en tu Navegador

```
https://github.com/paginaviva/pviva-FWUF/settings/secrets/actions
```

---

## 🆕 SECRETO 1: DEPLOY_KEY

**Paso 1:** En la página, haz click en el botón **"New repository secret"** (verde)

**Paso 2:** Se abrirá un formulario. Complétalo así:

```
Name (Nombre):
DEPLOY_KEY

Secret (Valor):
[Abre el archivo .key que descargaste de cPanel]
[Copia TODO el contenido - desde -----BEGIN RSA PRIVATE KEY----- hasta -----END RSA PRIVATE KEY-----]
[Pégalo aquí - COMPLETO, sin truncar]
```

**Ejemplo de lo que deberías pegar (formato):**
```
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA1234567890abcdefghijklmnopqrstuvwxyz...
...muchas líneas de caracteres...
-----END RSA PRIVATE KEY-----
```

**Paso 3:** Click en **"Add secret"** (botón verde)

✅ LISTO - Secreto 1 creado

---

## 🆕 SECRETO 2: DEPLOY_KEY_PASSPHRASE

**Paso 1:** Vuelve a hacer click en **"New repository secret"** (verde)

**Paso 2:** Completa el formulario así:

```
Name (Nombre):
DEPLOY_KEY_PASSPHRASE

Secret (Valor):
z5PiIA9ddjTPqIX8
```

**⚠️ MUY IMPORTANTE:**
- Copia exactamente: `z5PiIA9ddjTPqIX8`
- Sin espacios al inicio o final
- Mayúsculas y minúsculas tal cual

**Paso 3:** Click en **"Add secret"** (botón verde)

✅ LISTO - Secreto 2 creado

---

## ✅ VERIFICACIÓN

Después de crear ambos secretos, deberías ver esta lista:

```
✅ DEPLOY_HOST                    (pvuf.plazza.xyz)
✅ DEPLOY_USER                    (plazzaxy)
✅ DEPLOY_PORT                    (22)
✅ DEPLOY_PATH                    (/home/plazzaxy/pvuf.plazza.xyz)
✅ DEPLOY_KEY                     (tu archivo .key)
✅ DEPLOY_KEY_PASSPHRASE          (z5PiIA9ddjTPqIX8)
```

---

## 🎬 SIGUIENTE PASO

Cuando hayas creado los 2 secretos, abre Codespaces y ejecuta:

```bash
cd /workspaces/pviva-FWUF && git add . && git commit -m "Deploy with RSA SSH key and passphrase from cPanel" && git push origin main
```

¡Listo! GitHub Actions se ejecutará automáticamente.

---

**Generado:** 2025-12-30  
**Para:** Configuración RSA de cPanel

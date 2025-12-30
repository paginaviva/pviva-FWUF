# GitHub Secrets - Referencia Rápida

Copiar y usar exactamente estos nombres en GitHub > Settings > Secrets and variables > Actions

## Secretos a Crear (5-6 Total)

### Secretos Básicos (Requeridos - 4)

#### 1. DEPLOY_HOST
```
Nombre: DEPLOY_HOST
Valor: pvuf.plazza.xyz
```

#### 2. DEPLOY_USER
```
Nombre: DEPLOY_USER
Valor: plazzaxy
```

#### 3. DEPLOY_PORT
```
Nombre: DEPLOY_PORT
Valor: 22
```

#### 4. DEPLOY_PATH
```
Nombre: DEPLOY_PATH
Valor: /home/plazzaxy/pvuf.plazza.xyz
```

### Secretos SSH - OPCIÓN A: Clave ED25519 sin Contraseña (Original)

Si usas la clave ED25519 que viene con el proyecto:

#### 5. DEPLOY_KEY (Clave ED25519)
```
Nombre: DEPLOY_KEY
Valor: [COPIA EXACTA DESDE SSH_KEYS.md]
```

**La clave debe incluir:**
- `-----BEGIN OPENSSH PRIVATE KEY-----`
- Toda la cadena de caracteres base64
- `-----END OPENSSH PRIVATE KEY-----`

---

### Secretos SSH - OPCIÓN B: Clave RSA con Contraseña (cPanel)

Si usas la clave RSA generada en cPanel con contraseña:

#### 5. DEPLOY_KEY (Clave RSA)
```
Nombre: DEPLOY_KEY
Valor: [Contenido COMPLETO del archivo .key descargado de cPanel]
```

**La clave debe incluir:**
- `-----BEGIN RSA PRIVATE KEY-----`
- Toda la cadena de caracteres base64
- `-----END RSA PRIVATE KEY-----`

#### 6. DEPLOY_KEY_PASSPHRASE (Contraseña)
```
Nombre: DEPLOY_KEY_PASSPHRASE
Valor: z5PiIA9ddjTPqIX8
```

**IMPORTANTE:** Si usas esta opción:
- Crea AMBOS secretos (DEPLOY_KEY + DEPLOY_KEY_PASSPHRASE)
- La contraseña es case-sensitive
- No debe haber espacios

---

## Tabla Rápida - Copia/Pega

### Con ED25519 (Sin Contraseña)
```
DEPLOY_HOST               = pvuf.plazza.xyz
DEPLOY_USER               = plazzaxy
DEPLOY_PORT               = 22
DEPLOY_PATH               = /home/plazzaxy/pvuf.plazza.xyz
DEPLOY_KEY                = [Clave ED25519 de SSH_KEYS.md]
```

### Con RSA (Con Contraseña desde cPanel)
```
DEPLOY_HOST               = pvuf.plazza.xyz
DEPLOY_USER               = plazzaxy
DEPLOY_PORT               = 22
DEPLOY_PATH               = /home/plazzaxy/pvuf.plazza.xyz
DEPLOY_KEY                = [Clave RSA .key de cPanel]
DEPLOY_KEY_PASSPHRASE     = z5PiIA9ddjTPqIX8
```

---

## Verificación Rápida

Después de crear los secretos:

1. Settings > Secrets and variables > Actions
2. Verifica que ves los secretos en la lista
3. Los nombres deben ser EXACTOS (mayúsculas)
4. Los valores deben ser COMPLETOS (sin truncar)

---

## Clave Pública (Para el Servidor)

**Clave Pública ED25519:**
```
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIKSXyxpc3kEAdt19phJ2IF1nRnF4YVSts9rMig+DOmsm pvuf-github-actions-deploy
```

**Clave Pública RSA (desde cPanel):**
```
[Ya está en el servidor - cPanel la instaló automáticamente]
Fingerprint esperado: SHA256:za/KxGJVZYlyXZolyvjyD/ohBLVqOPjxfkntM0u91qY
```

---

## ¿Qué Opción Elegir?

| Opción | Ventajas | Desventajas | Para |
|--------|----------|-------------|------|
| **ED25519** | Más moderno, sin contraseña | Necesita regenerar clave | Nuevo setup |
| **RSA (cPanel)** | Ya existe, lista para usar | Contraseña en secretos | Ya lo tienes |

---

**Nota:** Los secretos son case-sensitive. Usa mayúsculas y guiones bajos exactamente como se muestra.

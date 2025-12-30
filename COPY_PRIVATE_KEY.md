# PVUF - Instrucciones para Copiar Clave Privada a GitHub Secret

## ⚠️ IMPORTANTE

La clave privada SSH es **extremadamente sensible**. Debe:
- Copiarse exactamente (sin truncar)
- Almacenarse SOLO en GitHub Secrets
- Nunca ser compartida ni commiteada en el repositorio
- Ser regenerada si se compromete

## 🔍 Localizar la Clave Privada

La clave privada está en: [SSH_KEYS.md](SSH_KEYS.md)

Busca la sección: **"Clave Privada (para GitHub Secrets)"**

## 📋 Contenido a Copiar

La clave comienza con:
```
-----BEGIN OPENSSH PRIVATE KEY-----
```

Y termina con:
```
-----END OPENSSH PRIVATE KEY-----
```

**COPIA AMBAS LÍNEAS TAMBIÉN.**

## 🔐 Pasos para Crear el Secret en GitHub

### 1. Ve a tu Repositorio
```
https://github.com/paginaviva/pviva-FWUF
```

### 2. Abre Settings
- Haz click en **Settings** (arriba a la derecha)

### 3. Abre Secrets and Variables
- En el menú izquierdo: **Secrets and variables > Actions**

### 4. Crea un Nuevo Secret
- Haz click en **New repository secret**

### 5. Llena el Formulario

**Name:**
```
DEPLOY_KEY
```
(Exactamente así, con mayúsculas y sin espacios)

**Secret:**
- Abre [SSH_KEYS.md](SSH_KEYS.md) en esta ventana
- Encuentra la sección "Clave Privada (para GitHub Secrets)"
- Selecciona TODO desde `-----BEGIN OPENSSH PRIVATE KEY-----` hasta `-----END OPENSSH PRIVATE KEY-----` (incluyendo ambas líneas)
- Copia el contenido
- Pega en el campo "Secret"

### 6. Guarda
- Haz click en **Add secret**

---

## ✅ Verificación

Después de guardar:

1. En **Secrets and variables > Actions**, deberías ver:
   - `DEPLOY_HOST`
   - `DEPLOY_USER`
   - `DEPLOY_PORT`
   - `DEPLOY_PATH`
   - `DEPLOY_KEY` (mostrado como bullet punto •••, no el contenido)

2. Los secrets se actualizan automáticamente en el workflow siguiente

3. El workflow debería funcionar sin errores "Permission denied"

---

## 🚨 Si Cometiste un Error

Si la clave no está completa:

1. Ve a **Settings > Secrets and variables > Actions**
2. Busca `DEPLOY_KEY`
3. Haz click en el icono de lápiz (edit)
4. Borra el contenido actual
5. Pega la clave correcta y completa desde [SSH_KEYS.md](SSH_KEYS.md)
6. Haz click en **Update secret**

---

## 🔄 Pasos Completos (Resumen)

1. Abre [SSH_KEYS.md](SSH_KEYS.md)
2. Copia la sección "Clave Privada (para GitHub Secrets)" (completa)
3. Ve a GitHub > Settings > Secrets and variables > Actions
4. Crea nuevo secret:
   - Name: `DEPLOY_KEY`
   - Secret: [Pega la clave]
5. Haz click en "Add secret"

---

## 📝 Clave Privada (Lista para Copiar)

```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtzc2gtZW
QyNTUxOQAAACCkl8saXN5BAHbdfaYSdiBdZ0ZxeGFUrbPazIoPgzprJgAAAKAQcwLWEHMC
1gAAAAtzc2gtZWQyNTUxOQAAACCkl8saXN5BAHbdfaYSdiBdZ0ZxeGFUrbPazIoPgzprJg
AAAEAZVTfJ/NuMJfnXFw1fAalHGhyG46cPScO55zjEpOXlWaSXyxpc3kEAdt19phJ2IF1n
RnF4YVSts9rMig+DOmsmAAAAGnB2dWYtZ2l0aHViLWFjdGlvbnMtZGVwbG95AQID
-----END OPENSSH PRIVATE KEY-----
```

**Cópialo TODO incluyendo las líneas BEGIN y END.**

---

**Última actualización:** 2025-12-30

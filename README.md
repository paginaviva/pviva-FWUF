# PVUF - Validación de Despliegue Automatizado

Proyecto PHP mínimo para validar:
1. Que el servidor ejecuta **PHP 8.3** o superior
2. Que es posible **despliegue automatizado desde GitHub Actions** hacia servidor compartido vía SSH (rsync)

## 🎯 Objetivo

Este mini proyecto permite verificar que un servidor compartido:
- ✅ Ejecuta PHP 8.3+
- ✅ Es accesible vía SSH con autenticación por clave
- ✅ Puede recibir despliegues automáticos desde GitHub Actions
- ✅ **Sin ejecutar Composer, npm ni Node.js** en el servidor

## 📁 Estructura

```
PVUF/
├── index.php                    # Página web de validación
├── build.json                   # Identificador de despliegue (generado por GA)
├── DEPLOYMENT.md                # Guía completa de configuración
├── SSH_KEYS.md                  # Detalles de claves SSH
├── QUICKSTART.md                # Resumen rápido e instalación
├── SSH_PASSPHRASE_PLAN.md       # Plan alternativo con contraseña
└── .github/workflows/
    └── deploy.yml               # Workflow de GitHub Actions
```

## 🚀 Quick Start

1. **Instala la clave pública en el servidor:**
   ```bash
   echo "ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIKSXyxpc3kEAdt19phJ2IF1nRnF4YVSts9rMig+DOmsm pvuf-github-actions-deploy" >> ~/.ssh/authorized_keys
   chmod 600 ~/.ssh/authorized_keys
   ```

2. **Crea los secretos en GitHub** (Settings > Secrets):
   - `DEPLOY_HOST` = `pvuf.plazza.xyz`
   - `DEPLOY_USER` = `plazzaxy`
   - `DEPLOY_PORT` = `22`
   - `DEPLOY_PATH` = `/home/plazzaxy/pvuf.plazza.xyz`
   - `DEPLOY_KEY` = [Clave privada - ver SSH_KEYS.md]

3. **Haz push a main:**
   ```bash
   git push origin main
   ```

4. **Verifica en GitHub Actions** que el workflow termina en verde

5. **Abre la página:**
   ```
   https://pvuf.plazza.xyz/
   ```

Ver [QUICKSTART.md](QUICKSTART.md) para pasos detallados.

## 📖 Documentación

- **[QUICKSTART.md](QUICKSTART.md)** - Instalación rápida (5 pasos)
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Guía completa con solución de problemas
- **[SSH_KEYS.md](SSH_KEYS.md)** - Detalles de autenticación SSH
- **[SSH_PASSPHRASE_PLAN.md](SSH_PASSPHRASE_PLAN.md)** - Alternativa con contraseña

## ✅ Qué Valida

### En la Página Web (index.php)

- **Versión de PHP:** Detectada en tiempo de ejecución, p.ej. "PHP 8.3.0"
- **Commit Hash:** Primeros 7 caracteres del commit desplegado
- **Build Timestamp:** Marca de tiempo UTC de la construcción
- **Build Date:** Fecha legible de la construcción
- **Entorno:** "Prueba de Despliegue"

### En GitHub Actions (deploy.yml)

- ✅ Checkout del código
- ✅ Generación automática de build.json con info de despliegue
- ✅ Configuración SSH con secretos
- ✅ Test de conexión SSH
- ✅ Sincronización vía rsync
- ✅ Verificación de despliegue exitoso

## 🔐 Seguridad

- **Claves SSH:** ED25519 (256-bit, moderna y segura)
- **Sin contraseña:** Generadas sin passphrase para máxima compatibilidad
- **Secretos en GitHub:** La clave privada se almacena de forma segura en GitHub Secrets
- **Plan alternativo:** Si requieres contraseña, ver [SSH_PASSPHRASE_PLAN.md](SSH_PASSPHRASE_PLAN.md)

## 🐛 Solución de Problemas

Primero, lee [DEPLOYMENT.md - Solución de Problemas](DEPLOYMENT.md#-solución-de-problemas).

Errores comunes:

| Error | Solución |
|-------|----------|
| "Permission denied (publickey)" | Instala la clave pública en `~/.ssh/authorized_keys` |
| "Could not resolve hostname" | Verifica que `DEPLOY_HOST` es correcto |
| "rsync not found" | Usa scp en lugar de rsync, o contacta hosting |
| Página muestra "1970..." | El build.json no fue actualizado; revisa logs GA |

## 🔄 Ciclo de Despliegue

```
Push a main
    ↓
GitHub Actions dispara workflow
    ↓
Genera build.json (commit hash + timestamp)
    ↓
Configura SSH + rsync
    ↓
Sincroniza al servidor
    ↓
index.php lee build.json y lo muestra
    ↓
https://pvuf.plazza.xyz/ se actualiza
```

## 📋 Checklist de Verificación

Después de push:

- [ ] Workflow en GitHub Actions termina en verde
- [ ] Página en `https://pvuf.plazza.xyz/` carga sin errores
- [ ] Se ve "PHP 8.3" (o superior)
- [ ] El commit hash coincide con el último push
- [ ] El timestamp es reciente (últimos minutos)
- [ ] Un segundo push muestra commit hash diferente

## 🎓 Requisitos

- ✅ PHP 8.3+ en el servidor (validado)
- ✅ SSH access (usuario: `plazzaxy`)
- ✅ rsync en el servidor (o scp como alternativa)
- ✅ GitHub repository con Actions habilitado
- ✅ GitHub Codespaces para trabajar sin entorno local

## ❌ No Requiere

- ❌ Composer en el servidor
- ❌ npm o Node.js en el servidor
- ❌ Herramientas de construcción en el servidor
- ❌ Subdirectorios especiales (public_html, etc.)
- ❌ Bases de datos
- ❌ Entorno local Docker/local

## 📝 Licencia

Este proyecto es de código abierto. Úsalo libremente.

## 📞 Soporte

Para problemas:
1. Lee [DEPLOYMENT.md](DEPLOYMENT.md)
2. Revisa los logs de GitHub Actions
3. Verifica SSH desde línea de comandos
4. Contacta a tu proveedor de hosting si hay problemas de servidor

---

**Última actualización:** 2025-12-30  
**Versión:** 1.0  
**Estado:** Listo para usar ✅

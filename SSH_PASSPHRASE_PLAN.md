# PVUF - Plan Alternativo: SSH con Contraseña

Este documento describe el plan alternativo en caso de que el servidor requiera una contraseña (frase de paso) en la clave SSH.

## ¿Cuándo Necesitar Este Plan?

Si durante las pruebas encuentras alguno de estos errores:

```
Permission denied (publickey).
Agent admitted failure to sign using the key.
Passphrase required.
```

O si tu proveedor de hosting **exige obligatoriamente** una contraseña en la clave SSH, sigue este plan.

## 🔐 Regenerar Clave con Contraseña

### Opción A: En GitHub Codespaces (Recomendado)

```bash
# Genera una nueva clave con contraseña
ssh-keygen -t ed25519 -f ~/.ssh/pvuf_deploy_key_protected -C "pvuf-github-actions-deploy"

# Te pedirá que ingreses una contraseña dos veces
# Elige algo seguro, p.ej: "MySecurePass123!@#"

# Ver la clave privada (para copiar a GitHub Secrets)
cat ~/.ssh/pvuf_deploy_key_protected

# Ver la clave pública (para instalar en servidor)
cat ~/.ssh/pvuf_deploy_key_protected.pub
```

### Opción B: Usar la Clave Existente

Si ya generaste la clave sin contraseña, puedes protegerla:

```bash
# Cambia la contraseña de la clave
ssh-keygen -p -t ed25519 -f ~/.ssh/pvuf_deploy_key

# Déjala sin contraseña de entrada (presiona Enter)
# Ingresa la nueva contraseña dos veces
```

## 📋 Preparar GitHub Secrets con Contraseña

En GitHub, crea 2 secretos adicionales:

| Nombre del Secreto | Valor |
|---|---|
| `DEPLOY_KEY` | [Clave privada protegida] |
| `DEPLOY_KEY_PASSPHRASE` | [La contraseña que elegiste] |

**Mantén los otros 4 secretos igual:**
- `DEPLOY_HOST` = `pvuf.plazza.xyz`
- `DEPLOY_USER` = `plazzaxy`
- `DEPLOY_PORT` = `22`
- `DEPLOY_PATH` = `/home/plazzaxy/pvuf.plazza.xyz`

## 🔧 Modificar el Workflow para Usar Contraseña

En [.github/workflows/deploy.yml](.github/workflows/deploy.yml), reemplaza el paso "Setup SSH Key" con:

```yaml
- name: Setup SSH Key with Passphrase
  env:
    SSH_PRIVATE_KEY: ${{ secrets.DEPLOY_KEY }}
    SSH_PASSPHRASE: ${{ secrets.DEPLOY_KEY_PASSPHRASE }}
    DEPLOY_HOST: ${{ secrets.DEPLOY_HOST }}
    DEPLOY_PORT: ${{ secrets.DEPLOY_PORT }}
  run: |
    mkdir -p ~/.ssh
    echo "$SSH_PRIVATE_KEY" > ~/.ssh/deploy_key
    chmod 600 ~/.ssh/deploy_key
    
    # Start SSH Agent
    eval $(ssh-agent -s)
    
    # Add key with passphrase using SSH_ASKPASS
    SSH_ASKPASS=/bin/echo SSH_ASKPASS_REQUIRE=force ssh-add ~/.ssh/deploy_key
    
    # Add host to known_hosts
    ssh-keyscan -p ${DEPLOY_PORT:-22} ${DEPLOY_HOST} >> ~/.ssh/known_hosts 2>/dev/null || true
    
    # Configure SSH to use our key via agent
    cat > ~/.ssh/config <<EOF
    Host deploy
        HostName ${DEPLOY_HOST}
        User ${{ secrets.DEPLOY_USER }}
        Port ${DEPLOY_PORT:-22}
        IdentityFile ~/.ssh/deploy_key
        StrictHostKeyChecking accept-new
        UserKnownHostsFile ~/.ssh/known_hosts
    EOF
    
    chmod 600 ~/.ssh/config
```

**Explicación:**
- `eval $(ssh-agent -s)` inicia el agente SSH
- `SSH_ASKPASS` permite pasar la contraseña programáticamente
- El resto del workflow permanece igual

## 🧪 Verificar la Configuración

Después de actualizar el workflow, haz un test push:

```bash
git add .github/workflows/deploy.yml
git commit -m "Add SSH passphrase support to workflow"
git push origin main
```

Verifica en GitHub Actions que el workflow termina exitosamente.

## ⚠️ Limitaciones Conocidas

1. **GitHub Actions Runner Security:**
   - GitHub Actions runners son efímeros (se eliminan después de cada trabajo)
   - Tu contraseña es visible en los logs si los miras
   - No es un problema de seguridad si los logs del repositorio son privados

2. **ssh-agent en runners:**
   - Los runners de GitHub Actions pueden tener limitaciones con ssh-agent
   - Si el método anterior falla, considera usar un action de terceros como:
     ```yaml
     - uses: webfactory/ssh-agent@v0.8.0
       with:
         ssh-private-key: ${{ secrets.DEPLOY_KEY }}
         ssh-known-hosts: ${{ secrets.DEPLOY_HOST }}
     ```

## 🔄 Alternativa Más Segura: SSH Passphrase como Variable

Si quieres máxima seguridad:

```yaml
- uses: webfactory/ssh-agent@v0.8.0
  with:
    ssh-private-key: ${{ secrets.DEPLOY_KEY }}
    ssh-known-hosts: ${{ secrets.DEPLOY_HOST }}
```

Este action maneja automáticamente claves con contraseña de forma segura.

## 📝 Checklist para Contraseña

- [ ] He regenerado la clave con contraseña
- [ ] He creado el secreto `DEPLOY_KEY_PASSPHRASE` en GitHub
- [ ] He actualizado el workflow con el paso de contraseña
- [ ] He actualizado la clave pública en el servidor (si cambió)
- [ ] El workflow termina en éxito en GitHub Actions
- [ ] La página `https://pvuf.plazza.xyz/` muestra el deployment ID correctamente

## 🆘 Si Aún No Funciona

Si la clave con contraseña sigue sin funcionar:

1. **Verifica la contraseña:**
   ```bash
   # Intenta conectar manualmente
   ssh -i ~/.ssh/pvuf_deploy_key_protected plazzaxy@pvuf.plazza.xyz
   # Te pedirá la contraseña
   ```

2. **Revisa los logs del workflow** en GitHub Actions para errores específicos

3. **Opción nuclear:** Vuelve a la clave sin contraseña:
   ```bash
   # Regenera sin contraseña
   ssh-keygen -t ed25519 -f ~/.ssh/pvuf_deploy_key -N ""
   # Actualiza GitHub Secrets y el servidor
   ```

4. **Contacta al proveedor de hosting** si requieren una contraseña obligatoria y GitHub Actions no la soporta bien

---

**Última actualización:** 2025-12-30  
**Versión:** 1.0  
**Estado:** Alternativa disponible

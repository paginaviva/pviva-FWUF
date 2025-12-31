# Error BadClassNameException - Análisis y Solución

**Fecha:** 31 de diciembre de 2025  
**Rama:** F3-uf-skeleton-like  
**Commit desplegado:** b1fe86df35315953670fecbc1e265e27facb0979  
**URL afectada:** https://pvuf.plazza.xyz/

---

## 🔴 Problema Actual

### Error Completo
```
PHP Fatal error: Uncaught UserFrosting\Support\Exception\BadClassNameException: 
Sprinkle recipe class `/home/plazzaxy/pvuf.plazza.xyz` not found.

Stack trace:
#0 .../SprinkleManager.php(46): SprinkleManager->validateClassIsRecipe()
#1 .../Cupcake.php(52): SprinkleManager->__construct()
#2 .../Cupcake.php(43): Cupcake->init()
#3 .../app/app.php(17): Cupcake->__construct()
#4 .../public/index.php(25): require_once('/home/plazzaxy/...')
```

### Ubicación del Error
- **Archivo:** `/home/plazzaxy/pvuf.plazza.xyz/vendor/userfrosting/framework/src/Sprinkle/SprinkleManager.php:162`
- **Línea en app.php:** línea 17

---

## 🔍 Causa Raíz

El archivo `app/app.php` actual tiene este código **INCORRECTO**:

```php
// Define paths
$projectRoot = dirname(__DIR__);

// Create UserFrosting application
$uf = new UserFrosting($projectRoot);  // ❌ ERROR: pasa una ruta
```

**Problema:** El constructor de `UserFrosting` en la versión 5.x espera:
- **Un nombre de clase** (string) de un Sprinkle: `MyApp::class`
- **O una instancia** de `SprinkleRecipe`

Pero está recibiendo:
- **Una ruta de directorio:** `/home/plazzaxy/pvuf.plazza.xyz`

`SprinkleManager` intenta validar que `/home/plazzaxy/pvuf.plazza.xyz` sea una clase válida que implemente `SprinkleRecipe`, y falla porque es una ruta, no una clase.

---

## ✅ Solución

### 1. Crear el Sprinkle Principal

**Archivo:** `app/src/MyApp.php`

```php
<?php

declare(strict_types=1);

namespace UserFrosting\App;

use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Sprinkle\Account\Account;
use UserFrosting\Sprinkle\Admin\Admin;
use UserFrosting\Sprinkle\Core\Core;

class MyApp implements SprinkleRecipe
{
    public function getName(): string
    {
        return 'PVUF Application';
    }

    public function getPath(): string
    {
        return __DIR__ . '/../';
    }

    public function getSprinkles(): array
    {
        return [
            Core::class,
            Account::class,
            Admin::class,
        ];
    }

    public function getRoutes(): array
    {
        return [];
    }

    public function getServices(): array
    {
        return [];
    }
}
```

### 2. Actualizar app/app.php

**Cambiar de:**
```php
<?php

use UserFrosting\UserFrosting;

// Define paths
$projectRoot = dirname(__DIR__);

// Create UserFrosting application
$uf = new UserFrosting($projectRoot);  // ❌ INCORRECTO

return $uf;
```

**A:**
```php
<?php

declare(strict_types=1);

use UserFrosting\App\MyApp;
use UserFrosting\UserFrosting;

// Create and return UserFrosting application with main sprinkle
return new UserFrosting(MyApp::class);  // ✅ CORRECTO
```

---

## 📋 Pasos para Implementar

### Opción A: Commit y Deploy Manual

1. **Crear archivo:** `app/src/MyApp.php` con el código del Sprinkle
2. **Editar archivo:** `app/app.php` con la nueva sintaxis
3. **Commit y push:**
   ```bash
   git add app/src/MyApp.php app/app.php
   git commit -m "Fix: Crear Sprinkle principal y corregir app.php"
   git push origin F3-uf-skeleton-like
   ```
4. **Ejecutar workflow** de deployment desde GitHub Actions
5. **Verificar** en https://pvuf.plazza.xyz/

### Opción B: Merge a Main

Si el workflow solo corre en `main`:
```bash
git checkout main
git merge F3-uf-skeleton-like
git push origin main
```

---

## 🎯 Contexto de UserFrosting 5.x

### Arquitectura de Sprinkles

UserFrosting 5.x usa un sistema de **Sprinkles** para modularizar la aplicación:

- **Sprinkle Core:** Funcionalidad base del framework
- **Sprinkle Account:** Sistema de usuarios y autenticación
- **Sprinkle Admin:** Panel administrativo
- **Sprinkle Custom:** Tu aplicación (MyApp)

Cada Sprinkle debe:
1. Implementar la interfaz `SprinkleRecipe`
2. Declarar sus dependencias en `getSprinkles()`
3. Registrar sus rutas en `getRoutes()`
4. Registrar sus servicios en `getServices()`

### Cambio de API

**UserFrosting 4.x:**
```php
$uf = new UserFrosting($projectRoot);  // Recibía ruta
```

**UserFrosting 5.x:**
```php
$uf = new UserFrosting(MyApp::class);  // Recibe Sprinkle
```

---

## 🔧 Archivos Afectados

| Archivo | Estado | Acción |
|---------|--------|--------|
| `app/src/MyApp.php` | ❌ No existe | Crear |
| `app/app.php` | ⚠️ Código 4.x | Actualizar |
| `public/index.php` | ✅ OK | Sin cambios |
| `composer.json` | ✅ OK | Sin cambios |

---

## 📊 Impacto

### Antes del Fix
- ❌ Error 500 en https://pvuf.plazza.xyz/
- ❌ `BadClassNameException` en logs
- ❌ Aplicación no carga

### Después del Fix
- ✅ Aplicación carga correctamente
- ✅ Sprinkles Core/Account/Admin disponibles
- ✅ Listo para configuración de BD y setup

---

## ⚠️ Notas Importantes

1. **No afecta a la rama main** (si existe): El error está en código desplegado desde `F3-uf-skeleton-like`

2. **Requiere re-deployment:** Los cambios deben subirse al servidor mediante el workflow

3. **Compatibilidad:** Esta solución es para **UserFrosting 5.1.x** según `composer.json`

4. **Autoloader:** El namespace `UserFrosting\App\` ya está configurado en `composer.json`:
   ```json
   "autoload": {
       "psr-4": {
           "UserFrosting\\App\\": "app/src/"
       }
   }
   ```

---

## 📚 Referencias

- [UserFrosting 5.x Documentation](https://learn.userfrosting.com/)
- [Sprinkle System](https://learn.userfrosting.com/sprinkles)
- [Migration Guide 4.x → 5.x](https://learn.userfrosting.com/upgrading)

---

**Resumen:** El código actual usa sintaxis de UserFrosting 4.x. La solución es crear un Sprinkle principal (`MyApp`) y actualizar `app/app.php` para usar la API 5.x.

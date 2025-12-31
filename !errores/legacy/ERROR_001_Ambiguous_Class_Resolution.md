# ERROR #001: Ambiguous Class Resolution - UserFrosting\App\MyApp

**Severidad:** 🔴 CRÍTICA  
**Detectado:** Durante verificación de Composer  
**Estado:** 🔧 REQUIERE FIX  

---

## Descripción del Problema

Al instalar dependencias Composer, se genera la siguiente advertencia:

```
Warning: Ambiguous class resolution, "UserFrosting\App\MyApp" was found in both 
"/workspaces/pviva-FWUF/app/src/MyApp.php" 
and 
"/workspaces/pviva-FWUF/vendor/userfrosting/userfrosting/app/src/MyApp.php"
the first will be used.
```

### Causa Raíz

El framework UserFrosting 5.1.3 incluye un archivo `MyApp.php` template en:
```
vendor/userfrosting/userfrosting/app/src/MyApp.php
```

Pero nuestro proyecto también define `MyApp.php` en:
```
app/src/MyApp.php
```

Ambos archivos definen la clase `UserFrosting\App\MyApp` que implementa `SprinkleRecipe`.

### Consecuencias

1. **Autoload ambiguo:** Composer no sabe cuál usar
2. **Comportamiento impredecible:** Podría cargar cualquiera de las dos
3. **Posible conflicto en runtime:** Si el framework intenta cargar su versión
4. **Merma en performance:** Composer pierde tiempo resolviendo ambigüedad

---

## Análisis Técnico

### Archivo del Framework
**Ubicación:** `vendor/userfrosting/userfrosting/app/src/MyApp.php`
**Tamaño:** 118 líneas
**Interfaces:** 
- `SprinkleRecipe`
- `BakeryRecipe`

**Métodos:**
- `getName()` → "My Application"
- `getPath()` → Sprinkle directory path
- `getSprinkles()` → Lista de Sprinkles (Core, Account, Admin, AdminLTE, + Custom bakery)
- `getBakeryRecipe()` → BakeryRecipe implementation
- `getComposerConfig()` → Composer config

### Nuestro Archivo
**Ubicación:** `app/src/MyApp.php`
**Tamaño:** 43 líneas
**Interfaces:**
- `SprinkleRecipe`

**Métodos:**
- `getName()` → "PVUF Application"
- `getPath()` → Sprinkle directory path
- `getSprinkles()` → [Core::class, Account::class, Admin::class]

---

## Soluciones Disponibles

### Opción 1: Eliminar el archivo template del framework ❌
No viable. Es parte de la distribución de Composer.

### Opción 2: Renombrar nuestro MyApp ⚠️ 
No recomendado. El framework espera específicamente `UserFrosting\App\MyApp`.

### Opción 3: Configurar Composer para ignorar el conflicto ✅ RECOMENDADO

En `composer.json`, agregar:
```json
{
  "autoload": {
    "exclude-from-classmap": [
      "vendor/userfrosting/userfrosting/app/src/MyApp.php"
    ]
  }
}
```

### Opción 4: Usar PSR-4 psr más específico en composer.json ✅ ALTERNATIVA

```json
{
  "autoload": {
    "psr-4": {
      "UserFrosting\\App\\": "app/src/"
    }
  }
}
```

---

## Solución Recomendada: Opción 3 + 4 (Combinada)

1. Mantener `app/src/MyApp.php` (ya creado)
2. Actualizar `composer.json` para excluir template del framework
3. Regenerar autoloader

### Pasos de Implementación

```bash
# 1. Actualizar composer.json
# Editar autoload section

# 2. Regenerar autoloader
composer dump-autoload --optimize

# 3. Verificar no hay advertencias
composer install --no-dev --optimize-autoloader --dry-run
```

---

## Impacto en GitHub Actions

En el workflow, cuando Composer se ejecuta:
```
composer install --no-dev --optimize-autoloader --no-interaction
```

Genera la advertencia pero **continúa sin fallar** porque es una `Warning`, no un `Error`.

Sin embargo, esta ambigüedad puede causar:
- Comportamiento inconsistente en runtime
- Problemas si el framework luego intenta cargar su MyApp
- Confusión en el debugging

---

## Verificación Post-Fix

Después de aplicar la fix, ejecutar:

```bash
composer validate
composer install --no-dev --optimize-autoloader --dry-run
composer show --direct
```

No debe haber ninguna advertencia sobre "Ambiguous class resolution".

---

## Estado del Fix

- [ ] Actualizar composer.json
- [ ] Regenerar autoloader
- [ ] Verificar sin advertencias
- [ ] Commit y push a F3-uf-skeleton-like
- [ ] Re-ejecutar workflow (verificar build sin warnings)

---

## Referencias

- [Composer Autoload Documentation](https://getcomposer.org/doc/04-schema.md#autoload)
- [UserFrosting Sprinkle Recipe Guide](https://learn.userfrosting.com/sprinkles/recipe)
- [PHP Autoloading Standards (PSR-4)](https://www.php-fig.org/psr/psr-4/)

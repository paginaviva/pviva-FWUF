# Decisión Arquitectónica: Runtime HTTP principal

## Resumen ejecutivo

Se registra la decisión de que el punto de entrada HTTP principal de la aplicación es **`public/index.php`**, con carácter definitivo. Esta decisión cierra la Fase 3 y establece la base para fases posteriores.

---

## Situación inicial

Previamente, existía un archivo `index.php` en la raíz del repositorio (`./index.php`) que servía como runtime de prueba o prototipado durante el desarrollo inicial.

Este archivo:
- Permitía validar concepto y arquitectura.
- No forma parte de la estructura skeleton-like final.
- Fue necesario para iteración temprana, pero no persiste en la aplicación productiva.

---

## Decisión

### Punto de entrada definitivo

La aplicación web ejecutable tendrá su punto de entrada HTTP en:

```
public/index.php
```

**Justificación**

1. **Consistencia arquitectónica**: Alinea con los estándares skeleton-like definidos en [UF_skeleton-like_architecture.md](UF_skeleton-like_architecture.md).
2. **Seguridad**: El webroot expone únicamente `public/`, manteniendo el código de aplicación fuera del alcance HTTP directo.
3. **Portabilidad**: No asume rutas específicas en el servidor; funciona independientemente de la ruta física.
4. **Claridad**: Punto de entrada único y evidente para cualquier despliegue futuro.

### Arquivos de prueba previos

Cualquier archivo de runtime o prueba previo en la raíz del repositorio (incluyendo pero no limitado a `./index.php` original):

- **No forma parte del estado final de la aplicación.**
- **No se incluye en despliegues.**
- **Puede ser archivado, eliminado o repositorio internamente según criterios de limpieza.**

---

## Implicaciones técnicas

### Para la rama actual (`F3-uf-skeleton-like`)

- Se prepara la estructura de directorios skeleton-like.
- La carpeta `public/` se define como webroot único.
- `public/index.php` se configura como entry point.
- Configuración de servidor (webroot, rewrite rules) se documentará en fases posteriores.

### Para fases posteriores

- **Fase 4 (o siguientes)**: Implementación técnica de `public/index.php` y bootstrapping de la aplicación.
- **Fase 5 (o siguientes)**: Instalación y configuración de UserFrosting y sus dependencias.
- **Despliegue**: El servidor debe apuntar su webroot a `public/` y direccionar todas las solicitudes a `public/index.php`.

### Para despliegue en servidor

El servidor hosting:

1. Expone el directorio `public/` como webroot (ej: DocumentRoot en Apache, root en Nginx).
2. Configura rewrite rules para dirigir todas las solicitudes a `public/index.php`.
3. No requiere cambios en configuración después del despliegue inicial (la aplicación reside en un artefacto inmutable).

---

## Principio de compatibilidad

### Cambios futuros

Cambios técnicos implementados en fases posteriores:

- **No deben romper** la presencia de `public/index.php` como punto de entrada.
- **Deben mantener** la invariante de que solo `public/` es accesible por HTTP.
- **Pueden extender** funcionalidad sin alterar este contrato.

**Ejemplo**: Si se añaden rutas dinámicas, assets, o subrutas, todas deben ser procesadas por `public/index.php` (front controller pattern).

---

## Alcance de esta decisión

### Qué cubre

- **Especificación del punto de entrada**: `public/index.php`.
- **Invariante de arquitectura**: Webroot = `public/`, entry point = `public/index.php`.
- **Claridad para fases posteriores**: Queda registrado dónde debe estar el código ejecutable.

### Qué NO cubre

- **Implementación técnica**: El código dentro de `public/index.php` se define en fases posteriores.
- **Configuración de servidor**: Detalles de rewrite rules, directivas de hosting (se definen post-decisión).
- **Cambios funcionales**: Lógica de aplicación, rutas, controladores.
- **Instalación de dependencias**: UserFrosting, Composer, assets (fases posteriores).

---

## Decisiones relacionadas

Esta decisión se alinea con:

- **[UF_skeleton-like_architecture.md](UF_skeleton-like_architecture.md)**: Define la arquitectura skeleton-like y la separación webroot/aplicación.
- **[Environment_Matrix.md](Environment_Matrix.md)**: Especifica cómo se configura la ejecución en diferentes entornos.

---

## Aprobación y trazabilidad

| Aspecto | Valor |
|--------|-------|
| **Rama** | F3-uf-skeleton-like |
| **Fase** | 3 (Mínimos definitorios) |
| **Decisión** | Point of entry = `public/index.php` |
| **Vigencia** | Definitiva para la aplicación (hasta versionado explícito de cambio). |
| **Estado** | Registrada y documentada. |

---

## Próximos pasos

Tras esta fase de decisión y documentación, las siguientes acciones se ejecutarán en fases posteriores:

1. **Fase 4+**: Crear e implementar `public/index.php` con bootstrapping mínimo.
2. **Fase 5+**: Integrar UserFrosting y sus componentes.
3. **Despliegue**: Configurar servidor para exponer `public/` como webroot.

---

## Referencia

Para más detalles sobre front controllers y arquitecturas skeleton-like:
- UserFrosting official documentation
- PHP-FIG PSR standards (especialmente PSR-7, PSR-12)
- Common patterns in modern PHP frameworks (Laravel, Symfony)

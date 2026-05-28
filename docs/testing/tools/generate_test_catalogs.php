<?php

declare(strict_types=1);

/**
 * Genera markdown de catálogo en docs/testing/ a partir de tests/*.php
 * Uso: php docs/testing/tools/generate_test_catalogs.php
 */

$base = dirname(__DIR__, 3);

function extractTests(string $php): array
{
    $methods = [];
    if (preg_match_all('/\#\[Test\](?:\s*\([^)]*\))?\s*\n(?:\s*\#[^\n]+\s*\n)*\s*public function (\w+)\s*\(/m', $php, $m)) {
        $methods = array_merge($methods, $m[1]);
    }
    if (preg_match_all('/public function (test[A-Za-z0-9_]+)\s*\([^)]*\)\s*:\s*void/m', $php, $m2)) {
        $methods = array_merge($methods, $m2[1]);
    }

    return array_values(array_unique($methods));
}

function moduleFromPath(string $rel): string
{
    if (str_contains($rel, '/Middleware/')) {
        return 'Middleware';
    }
    if (str_contains($rel, '/Dashboard/')) {
        return 'Dashboard';
    }
    if (str_contains($rel, '/EventBus/')) {
        return 'EventBus';
    }
    if (str_contains($rel, '/Inventory/')) {
        return 'Inventario';
    }
    if (str_contains($rel, '/Orders/')) {
        return 'Pedidos';
    }
    if (str_contains($rel, '/Sales/')) {
        return 'Ventas';
    }

    return 'Transversal';
}

function layerFromRel(string $rel): string
{
    if (str_starts_with($rel, 'tests/Unit/')) {
        return 'Unit';
    }
    if (str_starts_with($rel, 'tests/Integration/')) {
        return 'Integration';
    }
    if (str_starts_with($rel, 'tests/Feature/')) {
        return 'Feature';
    }
    if (str_starts_with($rel, 'tests/E2E/')) {
        return 'E2E';
    }

    return 'Unknown';
}

function layerPreambleSpanish(string $layer): string
{
    $role = match ($layer) {
        'Unit' => 'reglas puras, VOs, normalización de configuración y catálogo sin I/O externa.',
        'Integration' => 'servicios de aplicación, bus, persistencia de trazas y límites entre capas.',
        'Feature' => 'contratos HTTP del control de middleware y rutas observable desde operación.',
        'E2E' => 'simulación multi-paso tipo instancia cliente: configuración, publicación y observabilidad.',
        default => 'validación transversal de la suite.',
    };

    return <<<MD
## 1. Objetivo de la prueba
Documentar y rastrear la suite **{$layer}** del proyecto: cada ficha siguiente describe un método de prueba y su lectura arquitectónica respecto al **middleware** (transporte/enrutado sin negocio de dominio).

## 2. Alcance
Capa **{$layer}**: {$role} No sustituye la lectura del código fuente de los asserts.

## 3. Flujo probado (capa)
Ejecución PHPUnit con entorno `phpunit.xml` (SQLite en memoria, cola `sync`). Arranque de aplicación Laravel cuando aplica.

## 4. Datos de entrada
Por método: ver implementación (fixtures, `config()->set`, cuerpos HTTP, UUIDs de evento).

## 5. Resultado esperado
Todos los tests de la capa en verde; contratos públicos estables ante refactors que preserven el middleware como integración desacoplada.

## 6. Resultado obtenido (si aplica)
Regenerar tras cambios: `php docs/testing/tools/generate_test_catalogs.php`. Ejecutar `php vendor/bin/phpunit --testsuite {$layer}`.

## 7. Relación con el middleware
Valida propagación/nombre de eventos, trazabilidad en cola, registro declarativo, o coherencia config ↔ ejecución ↔ vistas API según la capa — alineado a `docs/Modulos/Modulo_Control_Middleware.md` y planes de servicio.

---

MD;
}

function renderCase(string $layer, string $relPath, string $classBase, string $method, string $module): string
{
    $nombre = "{$classBase}::{$method}";

    $tipoLine = match ($layer) {
        'Unit' => 'Unit',
        'Integration' => 'Integration',
        'Feature' => 'Feature',
        'E2E' => 'E2E',
        default => $layer,
    };

    return <<<MD

---

# {$nombre}

## Objetivo
Validar el comportamiento descrito por el método `{$method}` en `{$relPath}`, alineado al bounded context **{$module}** y a la capa **{$layer}**.

## Tipo de prueba
{$tipoLine}

## Módulo
{$module}

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `{$classBase}`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **{$tipoLine}** en el módulo **{$module}**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.


MD;
}

function collectFiles(string $dir): array
{
    if (! is_dir($dir)) {
        return [];
    }

    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    $out = [];
    foreach ($rii as $file) {
        /** @var SplFileInfo $file */
        if ($file->getExtension() !== 'php') {
            continue;
        }
        if (! str_ends_with($file->getFilename(), 'Test.php')) {
            continue;
        }
        $out[] = $file->getPathname();
    }

    sort($out);

    return $out;
}

$targets = [
    'unit_catalogo_autogenerado.md'        => $base.'/tests/Unit',
    'integration_catalogo_autogenerado.md' => $base.'/tests/Integration',
    'feature_catalogo_autogenerado.md'     => $base.'/tests/Feature',
    'e2e_catalogo_autogenerado.md'         => $base.'/tests/E2E',
];

foreach ($targets as $outName => $scanDir) {
    $layer = match ($outName) {
        'unit_catalogo_autogenerado.md' => 'Unit',
        'integration_catalogo_autogenerado.md' => 'Integration',
        'feature_catalogo_autogenerado.md' => 'Feature',
        default => 'E2E',
    };

    $lines = [];
    $lines[] = '# Catálogo — '.$layer;
    $lines[] = '';
    $lines[] = layerPreambleSpanish($layer);
    $lines[] = 'Este archivo lista las pruebas de la capa **'.$layer.'** con la plantilla estándar del proyecto.';
    $lines[] = '';
    $lines[] = '> Generado por `docs/testing/tools/generate_test_catalogs.php`. Regenerar tras añadir o renombrar tests.';
    $lines[] = '';

    foreach (collectFiles($scanDir) as $abs) {
        $rel = str_replace($base.DIRECTORY_SEPARATOR, '', $abs);
        $rel = str_replace('\\', '/', $rel);
        $php = file_get_contents($abs);
        if ($php === false) {
            continue;
        }
        $methods = extractTests($php);
        if ($methods === []) {
            continue;
        }
        $baseClass = basename($abs, '.php');
        $module = moduleFromPath($rel);

        foreach ($methods as $method) {
            $lines[] = trim(renderCase(layerFromRel($rel), $rel, $baseClass, $method, $module));
        }
    }

    $targetPath = $base.'/docs/testing/'.$outName;
    file_put_contents($targetPath, implode("\n", $lines)."\n");
    fwrite(STDERR, "Wrote {$targetPath}\n");
}

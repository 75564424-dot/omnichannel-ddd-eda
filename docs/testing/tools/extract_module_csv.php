<?php

declare(strict_types=1);

/**
 * Extrae filas de matriz_maestra_casos.csv por filtro de módulo/proceso.
 * Uso: php docs/testing/tools/extract_module_csv.php --modulo=Control --proceso=PROC-007
 */
$base = dirname(__DIR__, 3);
$csvPath = $base.'/docs/testing/matriz_maestra_casos.csv';
$outPath = null;
$modulo = null;
$procesos = [];
$modulos = [];

foreach ($argv ?? [] as $arg) {
    if (str_starts_with($arg, '--modulo=')) {
        $modulo = substr($arg, 9);
    }
    if (str_starts_with($arg, '--modulos=')) {
        $modulos = array_filter(explode(',', substr($arg, 10)));
    }
    if (str_starts_with($arg, '--proceso=')) {
        $procesos[] = substr($arg, 10);
    }
    if (str_starts_with($arg, '--procesos=')) {
        $procesos = array_merge($procesos, array_filter(explode(',', substr($arg, 11))));
    }
    if (str_starts_with($arg, '--out=')) {
        $outPath = substr($arg, 6);
    }
}

if ($outPath === null) {
    fwrite(STDERR, "Requiere --out=\n");
    exit(1);
}

$fh = fopen($csvPath, 'r');
$header = fgetcsv($fh);
$rows = [];
while (($row = fgetcsv($fh)) !== false) {
    $rowMod = $row[4] ?? '';
    $rowProc = $row[5] ?? '';
    if ($modulo !== null && $rowMod !== $modulo) {
        continue;
    }
    if ($modulos !== [] && ! in_array($rowMod, $modulos, true)) {
        continue;
    }
    if ($procesos !== [] && ! in_array($rowProc, $procesos, true)) {
        continue;
    }
    $rows[] = $row;
}
fclose($fh);

$outFh = fopen($outPath, 'w');
fputcsv($outFh, $header);
foreach ($rows as $row) {
    fputcsv($outFh, $row);
}
fclose($outFh);

echo count($rows)." filas -> {$outPath}\n";

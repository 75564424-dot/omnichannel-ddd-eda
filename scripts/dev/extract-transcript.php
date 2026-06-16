<?php

declare(strict_types=1);

$f = $argv[1] ?? '';
$needle = $argv[2] ?? '';
if ($f === '' || $needle === '') {
    fwrite(STDERR, "Usage: php extract-transcript.php <jsonl> <filename-needle>\n");
    exit(1);
}

foreach (file($f) as $line) {
    $j = json_decode($line, true);
    if (! is_array($j) || ! isset($j['message']['content'])) {
        continue;
    }
    foreach ($j['message']['content'] as $c) {
        if (($c['type'] ?? '') !== 'tool_use') {
            continue;
        }
        $name = $c['name'] ?? '';
        $path = $c['input']['path'] ?? '';
        if ($name === 'Write' && str_contains($path, $needle)) {
            echo "=== {$path} ===\n";
            echo $c['input']['contents'] ?? '';
            echo "\n\n";
        }
    }
}

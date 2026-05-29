<?php

declare(strict_types=1);

namespace App\Quality\Application\Services\Coverage;

use RuntimeException;
use SimpleXMLElement;

final class ApplicationCoverageCalculator
{
    /**
     * @param  list<string>  $prefixes
     * @return array{covered: int, total: int, percent: float}
     */
    public function calculate(string $cloverPath, array $prefixes): array
    {
        if (! is_readable($cloverPath)) {
            throw new RuntimeException("Clover file not readable: {$cloverPath}");
        }

        $xml = simplexml_load_file($cloverPath);
        if ($xml === false) {
            throw new RuntimeException("Unable to parse clover XML: {$cloverPath}");
        }

        $statements = 0;
        $covered    = 0;

        foreach ($xml->project->file as $file) {
            if (! $this->matchesApplicationLayer((string) $file['name'], $prefixes)) {
                continue;
            }

            foreach ($file->line as $line) {
                if ((string) $line['type'] !== 'stmt') {
                    continue;
                }
                $statements++;
                if ((int) $line['count'] > 0) {
                    $covered++;
                }
            }
        }

        if ($statements === 0) {
            throw new RuntimeException('No Application-layer statements found in clover report.');
        }

        return [
            'covered' => $covered,
            'total' => $statements,
            'percent' => ($covered / $statements) * 100,
        ];
    }

    /** @param list<string> $prefixes */
    private function matchesApplicationLayer(string $filePath, array $prefixes): bool
    {
        $path = strtolower(str_replace('\\', '/', $filePath));
        if (! str_contains($path, '/application/') && ! str_contains($path, '/shared/platform/')) {
            return false;
        }

        foreach ($prefixes as $prefix) {
            $needle = strtolower(str_replace('\\', '/', $prefix));
            if (str_contains($path, $needle)) {
                return true;
            }

            $relativeNeedle = str_starts_with($needle, 'app/') ? substr($needle, 4) : $needle;
            if ($relativeNeedle !== '' && str_contains($path, $relativeNeedle)) {
                return true;
            }
        }

        return false;
    }
}

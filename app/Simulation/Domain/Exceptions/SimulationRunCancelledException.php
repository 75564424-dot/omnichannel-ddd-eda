<?php

declare(strict_types=1);

namespace App\Simulation\Domain\Exceptions;

use RuntimeException;

final class SimulationRunCancelledException extends RuntimeException
{
    public function __construct(string $message = 'Simulación cancelada por el operador.')
    {
        parent::__construct($message);
    }
}

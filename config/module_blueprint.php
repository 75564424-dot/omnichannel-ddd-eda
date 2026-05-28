<?php

declare(strict_types=1);

/**
 * Plantilla base para modules_catalog por tenant (equivalente a modules_config.json).
 */
return [
    'default_limits' => [
        'producers_max'   => 4,
        'subscribers_max' => 2,
    ],

    'default_catalog' => [
        'service_contact_message' => 'Catálogo configurado desde el panel de control SaaS.',
        'middleware'              => [
            'id'          => 'middleware',
            'name'        => 'Middleware bus',
            'description' => 'Ingesta FIFO y distribución según catálogo eventbus.',
            'role'        => 'routing',
        ],
        'producers'   => [],
        'subscribers' => [],
    ],

    'producer_template' => [
        'id'                  => '',
        'name'                => '',
        'event_types_emitted' => [],
        'channels'            => [],
    ],

    'subscriber_template' => [
        'id'                   => '',
        'name'                 => '',
        'event_types_consumed' => [],
    ],
];

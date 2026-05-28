<?php

declare(strict_types=1);

/**
 * Catálogo comercial SaaS: planes y módulos asignables al tenant (portal control / provisioning).
 * El cliente ve en su dashboard lo habilitado en tenant.settings.modules + modules_config.json.
 */
return [
    'plans' => [
        'starter' => [
            'name'        => 'Starter',
            'tagline'     => 'Primer despliegue omnicanal',
            'price_label' => 'Desde $299 / mes',
            'summary'     => 'Ideal para un canal y volumen bajo de eventos.',
            'highlights'  => [
                '1 instancia dedicada',
                'Middleware bus + cola FIFO',
                'Dashboard operativo',
                'Soporte en horario laboral',
            ],
            'limits' => [
                'events_month' => '50k eventos / mes',
                'operators'    => '3 operadores',
                'integrations' => '2 conectores',
            ],
            'modules_included' => ['middleware', 'dashboard', 'observability'],
        ],
        'growth' => [
            'name'        => 'Growth',
            'tagline'     => 'Escala multi-canal',
            'price_label' => 'Desde $899 / mes',
            'summary'     => 'Varios canales, más throughput y observabilidad avanzada.',
            'highlights'  => [
                'Todo Starter',
                'Integraciones y webhooks',
                'Alertas y SLOs',
                'Retención extendida de logs',
            ],
            'limits' => [
                'events_month' => '500k eventos / mes',
                'operators'    => '15 operadores',
                'integrations' => '10 conectores',
            ],
            'modules_included' => ['middleware', 'dashboard', 'observability', 'integrations', 'analytics'],
        ],
        'enterprise' => [
            'name'        => 'Enterprise',
            'tagline'     => 'Gobierno y compliance',
            'price_label' => 'A medida',
            'summary'     => 'SLA dedicado, seguridad reforzada y despliegue híbrido.',
            'highlights'  => [
                'Todo Growth',
                'Auditoría y trazabilidad',
                'Clusters dedicados',
                'Soporte 24/7',
            ],
            'limits' => [
                'events_month' => 'Ilimitado (contrato)',
                'operators'    => 'Ilimitado',
                'integrations' => 'Ilimitado',
            ],
            'modules_included' => [
                'middleware',
                'dashboard',
                'observability',
                'integrations',
                'analytics',
                'security_audit',
                'multi_channel',
            ],
        ],
    ],

    'modules' => [
        'middleware' => [
            'name'        => 'Middleware bus',
            'icon'        => 'hub',
            'tagline'     => 'Núcleo de mensajería',
            'description' => 'Ingesta FIFO, registro en cola y distribución a suscriptores según el catálogo eventbus.',
            'client_hint' => 'El cliente lo activa desde Middleware → sincronizar catálogo.',
            'required'    => true,
        ],
        'dashboard' => [
            'name'        => 'Dashboard operativo',
            'icon'        => 'dashboard',
            'tagline'     => 'Vista unificada',
            'description' => 'Métricas, topología de módulos y estado de nodos en tiempo real.',
            'client_hint' => 'Visible en /dashboard tras el login de operadores.',
            'required'    => false,
        ],
        'observability' => [
            'name'        => 'Observabilidad',
            'icon'        => 'monitoring',
            'tagline'     => 'Salud y latencia',
            'description' => 'Métricas de bus, colas, DLQ y evaluación de alertas configurables.',
            'client_hint' => 'Se refleja en paneles de salud y alertas del portal cliente.',
            'required'    => false,
        ],
        'integrations' => [
            'name'        => 'Integraciones',
            'icon'        => 'cable',
            'tagline'     => 'Conectores externos',
            'description' => 'Webhooks, adaptadores y registro de productores/suscriptores en el bus.',
            'client_hint' => 'Requiere sync-config tras cargar modules_config del proveedor.',
            'required'    => false,
        ],
        'analytics' => [
            'name'        => 'Analytics',
            'icon'        => 'insights',
            'tagline'     => 'Tendencias y consumo',
            'description' => 'Agregados de eventos, throughput y reportes de uso por ventana temporal.',
            'client_hint' => 'Módulo de informes en el dashboard (según catálogo contratado).',
            'required'    => false,
        ],
        'security_audit' => [
            'name'        => 'Seguridad y auditoría',
            'icon'        => 'verified_user',
            'tagline'     => 'Trazabilidad',
            'description' => 'Bitácora de cambios de configuración, roles y acciones sensibles.',
            'client_hint' => 'Auditoría disponible para roles platform_admin.',
            'required'    => false,
        ],
        'multi_channel' => [
            'name'        => 'Multi-canal',
            'icon'        => 'forum',
            'tagline'     => 'Omnicanal',
            'description' => 'Orquestación de varios canales de entrada/salida sobre el mismo bus.',
            'client_hint' => 'Habilita vistas y rutas omnicanal en la instancia.',
            'required'    => false,
        ],
    ],

    'industries' => [
        'retail'       => 'Retail / e-commerce',
        'fintech'      => 'Fintech',
        'logistics'    => 'Logística',
        'healthcare'   => 'Salud',
        'saas'         => 'SaaS / tecnología',
        'other'        => 'Otro',
    ],
];

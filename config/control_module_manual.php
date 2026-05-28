<?php

declare(strict_types=1);

/**
 * Manual operativo para configuración y mantenimiento de módulos (portal control SaaS).
 */
return [
    'title'    => 'Manual de módulos y mantenimiento',
    'sections' => [
        [
            'id'    => 'configure',
            'title' => 'Configurar módulos de una empresa',
            'body'  => 'Defina productores (emiten eventos al bus) y suscriptores (consumen eventos). Cada fila necesita id único, nombre visible y tipos de evento. Los límites del plan acotan cuántos puede agregar. Al guardar, el catálogo queda en el registro del tenant.',
        ],
        [
            'id'    => 'apply',
            'title' => 'Aplicar en la instancia actual',
            'body'  => 'Si el slug del tenant coincide con PLATFORM_CLIENT_SLUG de esta instancia, puede escribir el catálogo en config/modules/modules_config.json y el cliente verá la topología tras sync-config en su portal Middleware.',
        ],
        [
            'id'    => 'support',
            'title' => 'Soporte al cliente',
            'body'  => 'Los reportes llegan en Incidentes. Responda desde el detalle del reporte; el cliente recibe notificación en la campana de su portal. Use el log de diagnóstico adjunto para correlacionar fallos del bus.',
        ],
        [
            'id'    => 'restart',
            'title' => 'Reiniciar / recuperar módulos',
            'body'  => 'En el portal del cliente: panel Live (icono sensores) → Refrescar nodo. Si el bus está STOPPED, revise métricas en Middleware global y colas DLQ. Tras cambiar modules_config, ejecute POST sync-config desde Middleware del cliente o reinicie workers de cola.',
        ],
        [
            'id'    => 'limits',
            'title' => 'Límites por plan',
            'body'  => 'Starter: pocos conectores. Growth/Enterprise: ampliar producers_max y subscribers_max según contrato. Los módulos comerciales (dashboard, observability) se asignan en Provisioning o en Plan y módulos de la ficha empresa.',
        ],
    ],
];

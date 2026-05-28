<?php

declare(strict_types=1);

return [

    'webhook' => [
        'signature_header' => env('INTEGRATIONS_WEBHOOK_SIGNATURE_HEADER', 'X-Webhook-Signature'),
        'require_secret'   => env('INTEGRATIONS_WEBHOOK_REQUIRE_SECRET', true),
    ],

    'adapter_types' => [
        'json_validate',
        'field_map',
    ],

];

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Legacy seeds used ACTIVE (invalid) or OFFLINE with events on — normalize middleware row.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('channel_status_snapshots')) {
            return;
        }

        $row = DB::table('channel_status_snapshots')->where('node_code', 'middleware')->first();
        if ($row === null) {
            return;
        }

        $status = strtoupper((string) ($row->status ?? ''));
        $patch  = [];

        if (in_array($status, ['ACTIVE', 'OFFLINE', ''], true)) {
            $patch['status'] = 'ONLINE';
        }

        if ((bool) ($row->events_enabled ?? false) && $status === 'OFFLINE') {
            $patch['events_enabled'] = false;
        }

        if ($patch !== []) {
            $patch['updated_at'] = now();
            DB::table('channel_status_snapshots')->where('node_code', 'middleware')->update($patch);
        }
    }

    public function down(): void
    {
        // non-reversible data normalization
    }
};

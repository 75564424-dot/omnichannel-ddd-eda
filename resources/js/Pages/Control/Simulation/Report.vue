<template>
  <ControlLayout title="Reporte de simulación" active-nav="companies">
    <div class="mb-6">
      <Link href="/control/simulations" class="text-xs text-[#00dbe7] hover:underline">← Historial de simulaciones</Link>
      <h2 class="mt-2 text-2xl font-bold text-[#e1fdff]">{{ run.tenant_name }}</h2>
      <p class="mt-1 font-mono text-xs text-[#849495]">
        Run {{ run.id }} · {{ run.fixture_slug }} · {{ run.status }}
      </p>
    </div>

    <section v-if="summary" class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
      <div
        v-for="card in summaryCards"
        :key="card.label"
        class="rounded-xl border border-white/8 bg-[#121820]/70 p-4"
      >
        <p class="text-[10px] uppercase text-[#849495]">{{ card.label }}</p>
        <p class="mt-1 font-mono text-xl text-[#00f2ff]">{{ card.value }}</p>
      </div>
    </section>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <section
        v-for="panel in metricPanels"
        :key="panel.title"
        class="rounded-xl border border-white/8 bg-[#121820]/70 p-6"
      >
        <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">{{ panel.title }}</h3>
        <dl class="mt-4 space-y-2 text-sm">
          <div
            v-for="row in panel.rows"
            :key="row.label"
            class="flex justify-between gap-4 border-b border-white/5 pb-2"
          >
            <dt class="text-[#b9cacb]">{{ row.label }}</dt>
            <dd class="font-mono text-[#e1fdff]">{{ row.value }}</dd>
          </div>
        </dl>
      </section>
    </div>

    <section v-if="consumption" class="mt-6 rounded-xl border border-white/8 bg-[#121820]/70 p-6">
      <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Consumo instancia (24h)</h3>
      <dl class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4 text-sm">
        <div v-for="(val, key) in consumption" :key="key">
          <dt class="text-[10px] uppercase text-[#849495]">{{ consumptionLabel(key) }}</dt>
          <dd class="font-mono text-[#00f2ff]">{{ val }}</dd>
        </div>
      </dl>
    </section>
  </ControlLayout>
</template>

<script setup>
import ControlLayout from '@/Layouts/ControlLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  run: { type: Object, required: true },
  metrics: { type: Object, default: () => ({}) },
});

const summary = computed(() => props.metrics?.summary ?? null);
const consumption = computed(() => props.metrics?.consumption ?? null);

const summaryCards = computed(() => {
  const s = summary.value;
  if (!s) return [];
  return [
    { label: 'Duración', value: s.duration_human },
    { label: 'Publicados', value: `${s.published} / ${s.planned_total}` },
    { label: 'Cumplimiento', value: `${s.publish_rate_pct}%` },
    { label: 'Cola OK', value: String(s.queue_matches) },
  ];
});

function rowsFrom(obj, labels) {
  if (!obj) return [];
  return Object.entries(labels).map(([key, label]) => ({
    label,
    value: obj[key] ?? '—',
  }));
}

const metricPanels = computed(() => {
  const m = props.metrics;
  const panels = [];

  if (m?.throughput) {
    panels.push({
      title: 'Throughput y demoras',
      rows: rowsFrom(m.throughput, {
        target_events_per_minute: 'Objetivo (evt/min)',
        achieved_events_per_minute: 'Logrado (evt/min)',
        target_interval_ms: 'Intervalo objetivo (ms)',
        avg_actual_interval_ms: 'Intervalo medio real (ms)',
        max_interval_ms: 'Intervalo máximo (ms)',
        interval_drift_ms: 'Desviación intervalo (ms)',
      }),
    });
  }
  if (m?.latency) {
    panels.push({
      title: 'Latencia',
      rows: rowsFrom(m.latency, {
        avg_processing_ms: 'Latencia media proc. (ms)',
        p95_processing_ms: 'Latencia P95 (ms)',
        max_processing_ms: 'Latencia máx. (ms)',
        bus_latency_ms_after: 'Latencia bus post-run (ms)',
      }),
    });
  }
  if (m?.reliability) {
    panels.push({
      title: 'Fiabilidad',
      rows: rowsFrom(m.reliability, {
        error_rate_percent: 'Tasa error (%)',
        success_rate_percent: 'Tasa éxito (%)',
        failed_count: 'Fallidos',
        processed_count: 'Procesados',
        pending_count: 'Pendientes',
        dead_lettered_count: 'Dead letter',
      }),
    });
  }
  if (m?.resources) {
    panels.push({
      title: 'Recursos y entorno',
      rows: rowsFrom(m.resources, {
        peak_memory_mb_run: 'Memoria pico (MB)',
        queue_depth_before: 'Cola pendiente (antes)',
        queue_depth_after: 'Cola pendiente (después)',
        dead_letters_before: 'DLQ (antes)',
        dead_letters_after: 'DLQ (después)',
        bus_status_before: 'Bus (antes)',
        bus_status_after: 'Bus (después)',
      }),
    });
  }

  return panels;
});

function consumptionLabel(key) {
  const map = {
    events_24h: 'Eventos 24h',
    queue_pending: 'Cola pendiente',
    dead_letters: 'Dead letters',
    event_logs: 'Logs 24h',
  };
  return map[key] || key;
}
</script>

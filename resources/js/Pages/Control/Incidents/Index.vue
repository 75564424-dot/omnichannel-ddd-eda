<template>
  <ControlLayout title="Incidentes" active-nav="incidents">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-[#e1fdff]">Incidentes y alertas</h2>
        <p class="mt-1 text-sm text-[#b9cacb]">
          Monitoreo automático + reportes enviados por clientes desde el portal.
        </p>
      </div>
    </div>

    <div v-if="$page.props.flash?.message" class="mb-4 rounded-lg border border-[#00f2ff]/30 bg-[#00f2ff]/10 px-4 py-3 text-sm">
      {{ $page.props.flash.message }}
    </div>

    <!-- Métricas -->
    <div class="mb-8 grid grid-cols-2 gap-3 md:grid-cols-4 lg:grid-cols-7">
      <div v-for="card in metricCards" :key="card.key" class="rounded-xl border border-white/8 bg-[#121820]/70 p-4">
        <p class="text-[9px] font-bold uppercase tracking-widest text-[#849495]">{{ card.label }}</p>
        <p class="mt-1 font-mono text-xl" :class="card.accent ? 'text-[#00f2ff]' : 'text-[#e1fdff]'">{{ card.value }}</p>
      </div>
    </div>

    <!-- Vista unificada -->
    <section class="mb-10 rounded-xl border border-white/8 bg-[#121820]/70 p-5">
      <header class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <div>
          <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Vista unificada</h3>
          <p class="text-[10px] text-[#849495]">Alertas del sistema y reportes de clientes en una sola lista.</p>
        </div>
        <span class="rounded-full border border-white/10 px-3 py-1 text-[10px] text-[#b9cacb]">
          {{ unified_timeline.length }} eventos
        </span>
      </header>

      <div v-if="unified_timeline.length === 0" class="py-8 text-center text-sm text-[#849495]">
        Sin alertas activas ni reportes de clientes.
      </div>

      <ul v-else class="space-y-2">
        <li
          v-for="item in unified_timeline"
          :key="item.id"
          class="rounded-lg border border-white/8 bg-[#191c1f]/60"
        >
          <button
            type="button"
            class="flex w-full items-start gap-3 px-4 py-3 text-left"
            @click="toggleExpand(item.id)"
          >
            <span
              class="material-symbols-outlined mt-0.5 text-[20px]"
              :class="timelineIconClass(item.type)"
            >
              {{ timelineIcon(item.type) }}
            </span>
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-2">
                <span class="text-[10px] uppercase tracking-wide" :class="timelineLabelClass(item.type)">
                  {{ timelineLabel(item.type) }}
                </span>
                <span class="text-[10px] text-[#849495]">{{ item.sort_at }}</span>
                <span v-if="item.severity" class="rounded bg-white/5 px-1.5 py-0.5 text-[9px] font-bold uppercase">{{ item.severity }}</span>
                <span v-if="item.status" class="rounded px-1.5 py-0.5 text-[9px] uppercase" :class="statusClass(item.status)">{{ item.status }}</span>
              </div>
              <p class="mt-1 text-sm font-semibold text-[#e1fdff]">{{ item.client_label }}</p>
              <p class="mt-0.5 text-xs text-[#b9cacb] line-clamp-2">{{ item.problem }}</p>
            </div>
            <span class="material-symbols-outlined text-[#849495]">{{ expandedId === item.id ? 'expand_less' : 'expand_more' }}</span>
          </button>

          <div v-if="expandedId === item.id" class="border-t border-white/5 px-4 py-3 text-xs">
            <template v-if="item.type === 'system_alert'">
              <p class="font-medium text-[#e1fdff]">{{ item.detail.name }}</p>
              <p class="mt-1 text-[#b9cacb]">{{ item.detail.message }}</p>
              <p class="mt-2 font-mono text-[#849495]">
                Valor: {{ item.detail.current_value }} · Umbral: {{ item.detail.threshold }}
                <span v-if="item.detail.over_threshold_pct"> · +{{ item.detail.over_threshold_pct }}% sobre umbral</span>
              </p>
            </template>
            <template v-else>
              <div class="flex flex-wrap gap-2">
                <Link
                  v-if="item.simulation_run_id"
                  :href="`/control/simulations/${item.simulation_run_id}/report`"
                  class="inline-flex items-center gap-1 rounded border border-amber-400/40 px-2 py-1 text-[10px] uppercase text-amber-300 hover:bg-amber-400/10"
                >
                  <span class="material-symbols-outlined text-[14px]">analytics</span>
                  Reporte de simulación
                </Link>
                <Link
                  :href="`/control/incidents/reports/${item.detail.id}`"
                  class="inline-flex items-center gap-1 rounded border border-[#00dbe7]/40 px-2 py-1 text-[10px] uppercase text-[#00dbe7] hover:bg-[#00dbe7]/10"
                >
                  <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                  Ver detalle y log
                </Link>
              </div>
              <p class="mt-2 font-medium text-[#e1fdff]">{{ item.detail.subject }}</p>
              <p class="mt-2 whitespace-pre-wrap text-[#b9cacb]">{{ item.detail.description }}</p>
              <div v-if="item.detail.has_response" class="mt-3 rounded border border-[#00f2ff]/20 bg-[#00f2ff]/5 p-3">
                <p class="text-[10px] uppercase text-[#00dbe7]">Respuesta enviada · {{ item.detail.responded_at }}</p>
                <p class="mt-1 whitespace-pre-wrap text-[#e1fdff]">{{ item.detail.admin_response }}</p>
              </div>
              <div class="mt-3 flex flex-wrap gap-2">
                <button
                  v-for="st in ['open', 'acknowledged', 'resolved']"
                  :key="st"
                  type="button"
                  class="rounded border px-2 py-1 text-[10px] uppercase"
                  :class="item.detail.status === st ? 'border-[#00f2ff] text-[#00f2ff]' : 'border-white/10 text-[#849495] hover:text-[#e1fdff]'"
                  @click="updateReportStatus(item.detail.id, st)"
                >
                  {{ st }}
                </button>
              </div>
              <div class="mt-4">
                <button
                  type="button"
                  class="text-[10px] uppercase text-[#00dbe7] hover:underline"
                  @click="respondingId = respondingId === item.detail.id ? null : item.detail.id"
                >
                  {{ respondingId === item.detail.id ? 'Ocultar respuesta' : 'Responder al cliente' }}
                </button>
                <form
                  v-if="respondingId === item.detail.id"
                  class="mt-2 space-y-2"
                  @submit.prevent="submitResponse(item.detail.id)"
                >
                  <textarea
                    v-model="responseTexts[item.detail.id]"
                    rows="4"
                    required
                    class="w-full rounded border border-white/10 bg-[#191c1f] px-2 py-2 text-xs text-[#e1e2e7]"
                    placeholder="Mensaje para el cliente…"
                  />
                  <button type="submit" class="rounded bg-[#00f2ff] px-3 py-1.5 text-[10px] font-bold text-[#00363a]">
                    Enviar respuesta
                  </button>
                </form>
              </div>
            </template>
          </div>
        </li>
      </ul>
    </section>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
      <!-- Alertas por categoría -->
      <section>
        <h3 class="mb-3 text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Alertas del sistema</h3>
        <div v-if="alerts.length === 0" class="rounded-lg border border-[#00f2ff]/15 bg-[#00f2ff]/5 p-6 text-center text-sm text-[#b9cacb]">
          Sin alertas por encima de umbral.
        </div>
        <div v-else class="space-y-6">
          <div v-for="(items, cat) in categories" :key="cat">
            <h4 class="mb-2 text-[10px] font-bold uppercase text-[#849495]">{{ categoryLabel(cat) }}</h4>
            <ul v-if="items.length" class="space-y-2">
              <li v-for="a in items" :key="a.name" class="rounded-lg border border-[#ffb4ab]/25 bg-[#121820]/80 px-3 py-2.5 text-sm">
                <span class="font-bold text-[#ffb4ab]">{{ a.severity }}</span> — {{ a.name }}
                <p class="text-xs text-[#b9cacb]">{{ a.message }}</p>
                <p class="mt-1 font-mono text-[10px] text-[#849495]">
                  {{ a.current_value }} / {{ a.threshold }}
                  <span v-if="a.over_threshold_pct" class="text-[#ffb4ab]"> (+{{ a.over_threshold_pct }}%)</span>
                </p>
              </li>
            </ul>
            <p v-else class="text-xs text-[#849495]">Ninguna en esta categoría.</p>
          </div>
        </div>
      </section>

      <!-- Reportes clientes -->
      <section>
        <h3 class="mb-3 text-xs font-bold uppercase tracking-widest text-[#00dbe7]">
          Reportes de clientes
          <span class="ml-2 font-normal text-[#849495]">({{ client_summary.open }} abiertos)</span>
        </h3>
        <div v-if="client_reports.length === 0" class="rounded-lg border border-white/8 p-6 text-center text-sm text-[#849495]">
          Aún no hay reportes. Los clientes envían desde <strong>Support</strong> en su portal.
        </div>
        <ul v-else class="space-y-2">
          <li
            v-for="r in client_reports"
            :key="r.id"
            class="rounded-lg border border-[#00dbe7]/20 bg-[#121820]/80 px-3 py-2.5"
          >
            <div class="flex justify-between gap-2">
              <p class="text-sm font-semibold text-[#e1fdff]">{{ r.client_label }}</p>
              <span class="text-[10px] uppercase" :class="statusClass(r.status)">{{ r.status }}</span>
            </div>
            <p class="mt-1 text-xs text-[#b9cacb] line-clamp-2">{{ r.description }}</p>
            <p class="mt-2 font-mono text-[10px] text-[#849495]">
              Bus: {{ r.diagnostic_summary.bus_status ?? '—' }} ·
              {{ r.diagnostic_summary.alerts_at_capture }} alertas ·
              {{ r.diagnostic_summary.failures_at_capture }} fallos capturados
            </p>
            <p class="mt-1 text-[10px] text-[#849495]">{{ r.created_at }}</p>
            <div class="mt-3 flex flex-wrap gap-2">
              <Link
                :href="`/control/incidents/reports/${r.id}`"
                class="inline-flex items-center gap-1 rounded border border-[#00dbe7]/40 px-2 py-1 text-[10px] uppercase text-[#00dbe7]"
              >
                Ver detalle
              </Link>
              <span v-if="r.has_response" class="text-[10px] text-[#00dbe7]">· Respondido</span>
            </div>
          </li>
        </ul>
      </section>
    </div>
  </ControlLayout>
</template>

<script setup>
import ControlLayout from '@/Layouts/ControlLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';

const props = defineProps({
  metrics: { type: Object, default: () => ({}) },
  alerts: { type: Array, default: () => [] },
  categories: { type: Object, default: () => ({}) },
  client_reports: { type: Array, default: () => [] },
  client_summary: { type: Object, default: () => ({}) },
  unified_timeline: { type: Array, default: () => [] },
});

const expandedId = ref(null);
const respondingId = ref(null);
const responseTexts = reactive({});

const metricCards = computed(() => {
  const m = props.metrics;
  return [
    { key: 'bus', label: 'Bus', value: m.bus_status ?? '—', accent: true },
    { key: 'lat', label: 'Latencia', value: `${m.latency_ms ?? 0} ms` },
    { key: 'err', label: 'Error rate', value: `${m.error_rate ?? 0}%` },
    { key: 'q', label: 'Cola', value: m.queue_depth ?? 0 },
    { key: 'dlq', label: 'DLQ', value: m.dead_letters ?? 0 },
    { key: 'alerts', label: 'Alertas', value: m.active_alerts ?? 0, accent: (m.active_alerts ?? 0) > 0 },
    { key: 'reports', label: 'Reportes abiertos', value: m.open_client_reports ?? 0, accent: (m.open_client_reports ?? 0) > 0 },
  ];
});

function categoryLabel(key) {
  const map = {
    caídas: 'Caídas',
    degradaciones: 'Degradaciones',
    integraciones_rotas: 'Integraciones rotas',
    recursos: 'Recursos',
    otros: 'Otros',
  };
  return map[key] || key;
}

function statusClass(status) {
  if (status === 'resolved') return 'text-green-400';
  if (status === 'acknowledged') return 'text-amber-400';
  if (status === 'active') return 'text-[#ffb4ab]';
  return 'text-[#00dbe7]';
}

function timelineIcon(type) {
  if (type === 'simulation_failure') return 'science_off';
  if (type === 'client_report') return 'support_agent';
  return 'sensors';
}

function timelineIconClass(type) {
  if (type === 'simulation_failure') return 'text-amber-300';
  if (type === 'client_report') return 'text-[#00dbe7]';
  return 'text-[#ffb4ab]';
}

function timelineLabel(type) {
  if (type === 'simulation_failure') return 'Simulación';
  if (type === 'client_report') return 'Cliente';
  return 'Sistema';
}

function timelineLabelClass(type) {
  if (type === 'simulation_failure') return 'text-amber-300';
  if (type === 'client_report') return 'text-[#00dbe7]';
  return 'text-[#ffb4ab]';
}

function toggleExpand(id) {
  expandedId.value = expandedId.value === id ? null : id;
}

function formatLog(log) {
  try {
    return JSON.stringify(log, null, 2);
  } catch {
    return String(log);
  }
}

function updateReportStatus(id, status) {
  router.patch(`/control/incidents/reports/${id}`, { status }, { preserveScroll: true });
}

function submitResponse(id) {
  router.post(`/control/incidents/reports/${id}/respond`, {
    admin_response: responseTexts[id] || '',
  }, { preserveScroll: true });
}
</script>

<template>
  <AppLayout title="Global Dashboard" search-placeholder="Search...">
    <div class="p-6 max-w-[1600px] mx-auto space-y-6">

      <!-- Configurable KPI cards (counter_cards in config/dashboard_config.json) -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div
          v-for="card in kpiCards"
          :key="card.id"
          class="bg-white p-6 border border-slate-200 shadow-sm rounded-sm"
        >
          <div class="flex justify-between items-start mb-2">
            <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant opacity-70">{{ card.name }}</span>
            <span class="material-symbols-outlined text-on-surface-variant">analytics</span>
          </div>
          <div class="flex items-baseline gap-2">
            <span class="text-4xl font-bold text-primary">{{ formatMetric(card.value) }}</span>
            <span v-if="card.suffix" class="text-xs text-on-surface-variant font-medium">{{ card.suffix }}</span>
          </div>
          <p class="mt-2 text-[11px] text-on-surface-variant">
            {{ lastUpdatedLabel }}
          </p>
        </div>
        <div v-if="kpiCards.length === 0" class="col-span-full text-sm text-slate-500 border border-dashed border-slate-200 rounded-sm p-6">
          No hay KPIs habilitados en <span class="font-mono">dashboard_config.json</span> (<span class="font-mono">counter_cards</span>).
        </div>
      </div>

      <!-- Dynamic chart (solo si hay módulos SaaS configurados) -->
      <div v-if="metricOptions.length > 0" class="bg-white border border-slate-200 shadow-sm rounded-sm p-6">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4 mb-6">
          <div class="flex-1">
            <h2 class="text-lg font-semibold text-on-surface">{{ dynamicChart.title || 'Métrica' }}</h2>
            <p v-if="dynamicChart.subtitle" class="text-xs text-on-surface-variant mt-1 max-w-3xl">{{ dynamicChart.subtitle }}</p>
          </div>
          <div class="flex flex-col sm:flex-row sm:items-center gap-3 shrink-0">
            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap" for="metric-select">Métrica</label>
            <select
              id="metric-select"
              v-model="selectedMetricId"
              class="text-sm border border-slate-200 rounded-sm px-3 py-2 bg-white min-w-[220px] focus:ring-1 focus:ring-primary/40 outline-none"
            >
              <option v-for="m in metricOptions" :key="m.id" :value="m.id">{{ m.name }}</option>
            </select>
          </div>
        </div>

        <div v-if="chartError" class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-sm px-4 py-3">
          {{ chartError }}
        </div>

        <!-- Single bar chart -->
        <template v-else-if="singleBarSeries.points.length || chartLoading">
          <div v-if="chartLoading" class="text-sm text-slate-400 py-12 text-center">Cargando serie…</div>
          <div v-else-if="singleBarSeries.points.length === 0" class="text-sm text-slate-500 py-12 text-center border border-dashed border-slate-200 rounded-sm px-4">
            {{ dynamicSeries.meta?.empty_hint || 'Sin eventos en el período — los datos aparecerán al simular o integrar sus módulos.' }}
          </div>
          <div v-else class="flex items-end gap-1 sm:gap-2 h-56 px-1 sm:px-2 border-b border-slate-100 pb-1">
            <div
              v-for="pt in singleBarSeries.points"
              :key="pt.label + String(pt.value)"
              class="flex-1 flex flex-col items-center gap-2 min-w-0"
            >
              <div class="w-full flex flex-col justify-end h-44">
                <div
                  class="w-full max-w-[44px] mx-auto rounded-t-sm bg-primary/85 hover:bg-primary transition-colors cursor-default"
                  :style="{ height: pt.barHeightPct + '%', minHeight: pt.value > 0 ? '8px' : '2px' }"
                  :title="`${pt.label}: ${formatChartValue(pt.value)}`"
                />
              </div>
              <span class="text-[10px] text-slate-500 text-center leading-tight px-0.5 truncate w-full">{{ pt.shortLabel }}</span>
            </div>
          </div>
          <div v-if="singleBarSeries.peakLabel" class="mt-3 text-[11px] text-on-surface-variant text-right">
            <span class="font-semibold text-on-surface">{{ singleBarSeries.peakLabel }}</span>
            <span class="block">{{ dynamicSeries.value_label || 'Valor' }} — pico en período</span>
          </div>
        </template>

        <!-- Dual bar (origin / consumer) -->
        <template v-else-if="isDualPanel">
          <div v-if="chartLoading" class="text-sm text-slate-400 py-12 text-center">Cargando serie…</div>
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div v-for="panel in dualPanels" :key="panel.panel_id" class="space-y-3">
              <h3 class="text-sm font-semibold text-slate-800">{{ panel.title }}</h3>
              <p v-if="panel.description" class="text-[11px] text-slate-500 leading-snug">{{ panel.description }}</p>
              <div v-if="!panel.points || panel.points.length === 0" class="text-xs text-slate-400 py-6 border border-dashed border-slate-100 rounded-sm text-center">
                Sin datos
              </div>
              <div v-else class="flex items-end gap-1 h-48 border-b border-slate-100 pb-1">
                <div
                  v-for="pt in panelRenderedPoints(panel.points)"
                  :key="panel.panel_id + pt.label"
                  class="flex-1 flex flex-col items-center gap-2 min-w-0"
                >
                  <div class="w-full flex flex-col justify-end h-36">
                    <div
                      class="w-full max-w-[36px] mx-auto rounded-t-sm bg-slate-700/85 hover:bg-slate-800 transition-colors"
                      :style="{ height: pt.barHeightPct + '%', minHeight: pt.value > 0 ? '6px' : '2px' }"
                      :title="`${pt.label}: ${formatChartValue(pt.value)}`"
                    />
                  </div>
                  <span class="text-[9px] text-slate-500 text-center leading-tight truncate w-full">{{ pt.label }}</span>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>

      <div
        v-else
        class="rounded-sm border border-dashed border-slate-200 bg-slate-50/80 px-6 py-8 text-center text-sm text-slate-500"
      >
        Las métricas de gráfico se habilitan cuando su proveedor asigna módulos (productores o suscriptores) a su instancia.
      </div>

      <!-- Event flow — generic topology (no business module names) -->
      <div class="grid grid-cols-12 gap-5">
        <EventFlowTopology :modules-catalog="modules_catalog" :flow-active="middlewareFlowActive" />

        <div class="col-span-12 lg:col-span-3 space-y-5">
          <div class="bg-white border border-slate-200 shadow-sm rounded-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50">
              <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">System Nodes</span>
            </div>
            <div class="p-4 space-y-4">
              <div v-if="displayNodes.length === 0" class="text-xs text-slate-400 py-2">
                Sin estado de nodos en la última lectura. Verifique <span class="font-mono">GET /api/dashboard/nodes/status</span>.
              </div>
              <div v-for="node in displayNodes" :key="node.name" class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div :class="['w-2 h-2 rounded-full', nodeColor(node.status)]" />
                    <span class="text-sm font-medium">{{ node.name }}</span>
                  </div>
                  <span :class="['text-[10px] px-2 py-0.5 rounded-full font-bold', nodeStatusClass(node.status)]">
                    {{ node.status ?? 'ONLINE' }}
                  </span>
                </div>
                <div v-if="node.middleware_events_enabled === false" class="pl-5 text-[10px] font-bold uppercase tracking-wider text-amber-700">
                  Eventos middleware apagados
                </div>
              </div>
            </div>
          </div>

          <div class="bg-primary p-6 rounded-sm text-white space-y-4">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-slate-200">speed</span>
              <span class="text-xs font-bold tracking-widest text-slate-200">ENGINE METRICS</span>
            </div>
            <div>
              <p class="text-xs opacity-60">Avg Latency</p>
              <p class="text-2xl font-semibold">{{ formatMetric(middlewareMetrics.latency_ms) }} ms</p>
            </div>
            <div class="pt-4 border-t border-white/10">
              <p class="text-xs opacity-60">Stream Status</p>
              <p class="text-xl font-semibold text-emerald-300">{{ middlewareMetrics.stream_status ?? '—' }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12 lg:col-span-8 bg-white border border-slate-200 shadow-sm rounded-sm">
          <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <span class="text-lg font-semibold">Real-Time Event Feed</span>
            <div class="flex gap-4 text-xs text-slate-400">
              <span>Eventos en tabla: {{ displayFeed.length }}</span>
              <span>Middleware EPS: {{ formatMetric(middlewareMetrics.processing_rate_eps) }}</span>
              <span>Cola FIFO: {{ formatMetric(middlewareMetrics.queue_size) }}</span>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                  <th class="px-6 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Timestamp</th>
                  <th class="px-6 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Event</th>
                  <th class="px-6 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Origin</th>
                  <th class="px-6 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Impact</th>
                  <th class="px-6 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Status</th>
                  <th class="px-6 py-3" />
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="event in displayFeed" :key="event.id" class="hover:bg-slate-50 transition-colors cursor-pointer group">
                  <td class="px-6 py-3 text-xs text-slate-400 font-mono">{{ formatFeedTimestamp(event.occurred_at ?? event.created_at) }}</td>
                  <td class="px-6 py-3 text-sm font-bold">{{ event.event_type ?? event.type }}</td>
                  <td class="px-6 py-3 text-sm text-slate-600">{{ event.origin ?? event.source ?? '—' }}</td>
                  <td class="px-6 py-3 text-sm" :class="impactClass(event.impact)">{{ event.impact ?? 'N/A' }}</td>
                  <td class="px-6 py-3">
                    <span :class="feedStatusClass(event.status)" class="flex items-center gap-1.5 text-[10px] font-bold">
                      <span class="material-symbols-outlined text-[14px]">{{ event.status === 'FAILED' ? 'error' : 'check_circle' }}</span>
                      {{ event.status ?? '—' }}
                    </span>
                  </td>
                  <td class="px-6 py-3 text-right">
                    <span class="material-symbols-outlined text-slate-300 group-hover:text-primary transition-colors">chevron_right</span>
                  </td>
                </tr>
                <tr v-if="displayFeed.length === 0">
                  <td colspan="6" class="py-10 text-center text-slate-400 text-sm">No recent events</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="col-span-12 lg:col-span-4 space-y-5">
          <div class="bg-white border border-slate-200 shadow-sm rounded-sm p-6">
            <div class="flex justify-between items-center mb-4">
              <span class="text-lg font-semibold">Engine Performance</span>
              <span class="material-symbols-outlined text-slate-400">analytics</span>
            </div>
            <div class="space-y-4">
              <div class="flex justify-between items-center">
                <span class="text-xs text-slate-500">Queue Status</span>
                <span class="text-sm font-bold">{{ formatMetric(middlewareMetrics.queue_size) }} eventos (FIFO)</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-xs text-slate-500">Processing Rate</span>
                <span class="text-sm font-bold">{{ formatMetric(middlewareMetrics.processing_rate_eps) }} eps</span>
              </div>
              <div v-if="sparkBars.length" class="h-12 w-full flex items-end gap-1 overflow-hidden">
                <div
                  v-for="(h, i) in sparkBars"
                  :key="i"
                  class="w-full rounded-t-sm bg-slate-100"
                  :class="{ 'bg-primary': i === sparkBars.length - 1 }"
                  :style="`height: ${h}%`"
                />
              </div>
            </div>
          </div>

          <div class="bg-slate-900 rounded-sm p-6 text-white">
            <p class="text-[10px] font-bold text-slate-300 tracking-[0.2em] mb-1">REAL-TIME STREAM</p>
            <h4 class="text-white text-lg font-semibold">Cluster Activity Monitoring</h4>
            <div class="mt-4 flex gap-6">
              <div>
                <p class="text-[10px] text-white/50 uppercase font-bold">Active Nodes</p>
                <p class="text-white text-xl font-semibold">{{ nodeCount }}</p>
              </div>
              <div>
                <p class="text-[10px] text-white/50 uppercase font-bold">Stream Status</p>
                <p class="text-white text-xl font-semibold text-emerald-400">{{ middlewareMetrics.stream_status ?? '—' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import EventFlowTopology from '@/Components/Dashboard/EventFlowTopology.vue';
import { SYSTEM_MODULE_ROWS, parseSystemNode } from '@/lib/systemModules';
import { onNodesChanged } from '@/platform-node-events';

export default {
  name: 'DashboardIndex',
  components: { AppLayout, EventFlowTopology },
  props: {
    metrics: { type: Object, default: () => ({}) },
    metrics_catalog: { type: Array, default: () => [] },
    initial_metric_id: { type: String, default: '' },
    initial_metric_series: { type: Object, default: () => ({}) },
    modules_catalog: { type: Object, default: () => ({}) },
    event_envelope: { type: [Object, Array], default: () => ({}) },
    feed: { type: Array, default: () => [] },
    nodes: { type: Object, default: () => ({}) },
    middlewareMetrics: { type: Object, default: () => ({}) },
    system_module_rows: { type: Array, default: () => [] },
  },
  setup(props) {
    const page = usePage();
    const liveFeed = ref(props.feed);
    const liveNodes = ref(props.nodes ?? {});
    const liveMetrics = ref({ ...(props.metrics ?? {}) });
    const metricOptions = ref([...(props.metrics_catalog ?? [])]);
    const selectedMetricId = ref(props.initial_metric_id || '');
    const dynamicSeries = ref(
      props.initial_metric_series && Object.keys(props.initial_metric_series).length
        ? { ...props.initial_metric_series }
        : {},
    );
    const chartLoading = ref(false);
    const chartError = ref('');
    const simulationPulse = ref({ active: false });
    const middlewareFlowActive = computed(() => simulationPulse.value.active === true);
    let refreshTimer = null;
    const POLL_IDLE_MS = 30000;
    const POLL_ACTIVE_MS = 2000;

    watch(() => props.feed, (v) => { liveFeed.value = [...(v ?? [])]; }, { deep: true });
    watch(() => props.nodes, (v) => { liveNodes.value = v ?? {}; }, { deep: true });
    watch(() => props.metrics, (v) => { liveMetrics.value = { ...(v ?? {}) }; }, { deep: true });
    watch(() => props.metrics_catalog, (v) => {
      metricOptions.value = [...(v ?? [])];
      syncDefaultMetric();
    }, { deep: true });

    const kpiCards = computed(() => {
      const raw = liveMetrics.value?.counters;
      if (!Array.isArray(raw)) return [];
      return raw.map((c) => ({
        id: c.id,
        name: c.name,
        value: Number(c.value ?? 0),
        suffix: c.suffix ?? '',
      }));
    });

    const lastUpdatedLabel = computed(() => {
      const ts = liveMetrics.value?.last_updated;
      if (!ts) return '—';
      return `Actualizado: ${formatDashboardDateTime(ts)}`;
    });

    const moduleRows = computed(() => {
      const fromInstance = page.props.instance?.live_modules;
      if (Array.isArray(fromInstance) && fromInstance.length > 0) {
        return fromInstance;
      }
      if (Array.isArray(props.system_module_rows) && props.system_module_rows.length > 0) {
        return props.system_module_rows;
      }
      return SYSTEM_MODULE_ROWS;
    });

    const displayFeed = computed(() => liveFeed.value.slice(0, 8));

    const sparkBars = computed(() => {
      const q = Number(props.middlewareMetrics?.queue_size ?? 0);
      const eps = Number(props.middlewareMetrics?.processing_rate_eps ?? 0);
      if (q === 0 && eps === 0) return [];
      const peak = Math.min(100, 20 + q * 4 + eps * 5);
      return Array.from({ length: 9 }, (_, i) => Math.min(100, Math.max(12, Math.round(peak * (0.5 + (i % 6) * 0.08)))));
    });

    const displayNodes = computed(() => {
      const n = liveNodes.value;
      if (!n || typeof n !== 'object' || Array.isArray(n)) return Array.isArray(n) ? n : [];
      return moduleRows.value.map((row) => {
        const parsed = parseSystemNode(n, row.key);
        return {
          name: row.label,
          status: parsed.status,
          middleware_events_enabled: parsed.middleware_events_enabled,
        };
      });
    });

    const nodeCount = computed(() => displayNodes.value.length);

    const valueFormat = computed(() => dynamicSeries.value?.value_format ?? 'number');

    const isDualPanel = computed(() => dynamicSeries.value?.chart === 'dual_bar');

    const dualPanels = computed(() => (Array.isArray(dynamicSeries.value?.panels) ? dynamicSeries.value.panels : []));

    const dynamicChart = computed(() => {
      const env = props.event_envelope;
      const envHint = env && typeof env === 'object' && env.description
        ? `Contrato genérico: ${env.description}`
        : 'Las series provienen solo de tablas de observabilidad (feed / bus).';
      return {
        title: dynamicSeries.value?.title ?? '',
        subtitle: envHint,
      };
    });

    function formatNumber(n) { return Number(n).toLocaleString(); }

    function formatMetric(v) {
      if (v === undefined || v === null || v === '') return '—';
      return formatNumber(Number(v));
    }

    function formatCurrency(n) {
      if (n === undefined || n === null || n === '') return '—';
      return new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'USD', minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(Number(n));
    }

    function formatChartValue(v) {
      if (valueFormat.value === 'currency') return formatCurrency(v);
      return formatMetric(v);
    }

    function formatDashboardDateTime(ts) {
      if (!ts) return '—';
      const d = new Date(ts);
      if (Number.isNaN(d.getTime())) return String(ts);
      return new Intl.DateTimeFormat('es-PE', { dateStyle: 'short', timeStyle: 'medium' }).format(d);
    }

    function formatFeedTimestamp(ts) {
      if (!ts) return '—';
      const d = new Date(ts);
      if (Number.isNaN(d.getTime())) return String(ts);
      return new Intl.DateTimeFormat('es-PE', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
      }).format(d);
    }

    function feedStatusClass(status) {
      const s = String(status ?? '').toUpperCase();
      const base = '';
      if (s === 'FAILED' || s === 'ERROR') return `${base}flex items-center gap-1.5 text-[10px] font-bold text-red-600`;
      if (s.includes('SUCCESS') || s === 'PROCESSED' || s === 'OK' || s === 'COMPLETED') {
        return `${base}flex items-center gap-1.5 text-[10px] font-bold text-emerald-600`;
      }
      return `${base}flex items-center gap-1.5 text-[10px] font-bold text-slate-500`;
    }

    function impactClass(impact) {
      if (!impact) return 'text-slate-400';
      if (String(impact).startsWith('-')) return 'text-error';
      if (String(impact).startsWith('+')) return 'text-emerald-600';
      return 'text-slate-400';
    }

    function nodeColor(status) {
      switch (String(status).toUpperCase()) {
        case 'ONLINE': return 'bg-green-500';
        case 'SYNCING': return 'bg-amber-500';
        case 'HI-LOAD': return 'bg-primary animate-pulse';
        case 'OFFLINE': return 'bg-red-500';
        default: return 'bg-green-500';
      }
    }

    function nodeStatusClass(status) {
      switch (String(status).toUpperCase()) {
        case 'ONLINE': return 'bg-green-50 text-green-700';
        case 'SYNCING': return 'bg-amber-50 text-amber-700';
        case 'HI-LOAD': return 'bg-slate-900 text-white';
        case 'OFFLINE': return 'bg-red-50 text-red-700';
        default: return 'bg-green-50 text-green-700';
      }
    }

    function shortDayLabel(isoDate) {
      const parts = String(isoDate).split('-').map((x) => parseInt(x, 10));
      const [y, mo, day] = parts;
      if (y && mo && day) {
        const d = new Date(y, mo - 1, day, 12, 0, 0);
        if (!Number.isNaN(d.getTime())) {
          return new Intl.DateTimeFormat('es-PE', { weekday: 'short', day: 'numeric' }).format(d);
        }
      }
      return String(isoDate).slice(5);
    }

    const singleBarSeries = computed(() => {
      const ptsIn = Array.isArray(dynamicSeries.value?.points) ? dynamicSeries.value.points : [];
      if (isDualPanel.value || ptsIn.length === 0) {
        return { points: [], max: 1, peakLabel: '' };
      }
      const totals = ptsIn.map((p) => Number(p.value) || 0);
      const max = Math.max(...totals, 1);
      const points = ptsIn.map((p) => {
        const total = Number(p.value) || 0;
        return {
          label: p.label,
          value: total,
          shortLabel: shortDayLabel(p.label),
          barHeightPct: total <= 0 ? 2 : Math.max(8, Math.round((total / max) * 100)),
        };
      });
      const peak = points.reduce((a, b) => (b.value > a.value ? b : a), { value: 0, label: '' });
      const peakLabel = peak.value > 0 ? formatChartValue(peak.value) : '';
      return { points, max, peakLabel };
    });

    function panelRenderedPoints(points) {
      const vals = points.map((p) => Number(p.value) || 0);
      const max = Math.max(...vals, 1);
      return points.map((p) => {
        const value = Number(p.value) || 0;
        return {
          label: p.label,
          value,
          barHeightPct: value <= 0 ? 2 : Math.max(10, Math.round((value / max) * 100)),
        };
      });
    }

    function syncDefaultMetric() {
      if (!metricOptions.value.length) {
        selectedMetricId.value = '';
        return;
      }
      const ids = metricOptions.value.map((m) => m.id);
      if (!selectedMetricId.value || !ids.includes(selectedMetricId.value)) {
        [selectedMetricId.value] = ids;
      }
    }

    async function fetchMetricSeries() {
      if (!selectedMetricId.value) {
        dynamicSeries.value = {};
        return;
      }
      chartLoading.value = true;
      chartError.value = '';
      try {
        const { data } = await window.axios.get(
          `/dashboard/metrics/series/${encodeURIComponent(selectedMetricId.value)}`,
          { params: { days: 14 } },
        );
        dynamicSeries.value = data ?? {};
      } catch (err) {
        console.error(err);
        const status = err.response?.status;
        chartError.value = status === 404
          ? 'Métrica no disponible para su instancia.'
          : 'No se pudo cargar la métrica seleccionada.';
        dynamicSeries.value = {};
      } finally {
        chartLoading.value = false;
      }
    }

    watch(selectedMetricId, () => { fetchMetricSeries(); });

    watch(
      () => simulationPulse.value?.sequence,
      (seq, prev) => {
        if (seq != null && seq !== prev && middlewareFlowActive.value) {
          refreshData();
        }
      },
    );

    async function fetchSimulationPulse() {
      try {
        const res = await window.axios.get('/api/middleware/simulation-pulse');
        simulationPulse.value = res.data?.data ?? res.data ?? { active: false };
      } catch {
        simulationPulse.value = { active: false };
      }
    }

    function applyDashboardPollCadence() {
      const ms = middlewareFlowActive.value ? POLL_ACTIVE_MS : POLL_IDLE_MS;
      clearInterval(refreshTimer);
      refreshTimer = setInterval(refreshData, ms);
    }

    async function refreshData() {
      try {
        const requests = [
          window.axios.get('/api/dashboard/events/feed'),
          window.axios.get('/api/dashboard/nodes/status'),
          window.axios.get('/api/dashboard/metrics'),
        ];
        const [feedRes, nodesRes, metricsRes] = await Promise.all(requests);
        liveFeed.value = feedRes.data?.data ?? feedRes.data ?? [];
        liveNodes.value = nodesRes.data?.data ?? nodesRes.data ?? {};
        const metricsPayload = metricsRes.data?.data ?? metricsRes.data ?? {};
        liveMetrics.value = { ...metricsPayload };
        await fetchSimulationPulse();
        applyDashboardPollCadence();
      } catch (e) {
        console.error('Dashboard refresh failed:', e);
      }
    }

    function applyNodesPayload(payload) {
      if (payload && typeof payload === 'object') {
        liveNodes.value = payload;
      }
    }

    let stopNodesListener = null;

    onMounted(() => {
      syncDefaultMetric();
      const hasInitialSeries = Array.isArray(dynamicSeries.value?.points)
        || Array.isArray(dynamicSeries.value?.panels);
      if (!hasInitialSeries) {
        fetchMetricSeries();
      }
      refreshData();
      applyDashboardPollCadence();
      stopNodesListener = onNodesChanged((payload) => {
        if (payload) {
          applyNodesPayload(payload);
        } else {
          refreshData();
        }
      });
    });
    onUnmounted(() => {
      clearInterval(refreshTimer);
      stopNodesListener?.();
    });

    return {
      middlewareFlowActive,
      liveFeed,
      liveNodes,
      liveMetrics,
      metricOptions,
      selectedMetricId,
      dynamicSeries,
      chartLoading,
      chartError,
      kpiCards,
      lastUpdatedLabel,
      displayFeed,
      displayNodes,
      sparkBars,
      nodeCount,
      singleBarSeries,
      isDualPanel,
      dualPanels,
      dynamicChart,
      panelRenderedPoints,
      formatNumber,
      formatMetric,
      formatCurrency,
      formatChartValue,
      formatDashboardDateTime,
      formatFeedTimestamp,
      impactClass,
      feedStatusClass,
      nodeColor,
      nodeStatusClass,
    };
  },
};
</script>

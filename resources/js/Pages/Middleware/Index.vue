<template>
  <AppLayout title="Middleware Control Center" search-placeholder="Search event ID...">
    <div class="p-6 max-w-[1600px] mx-auto flex flex-col gap-6">

      <!-- Page Header -->
      <div class="flex justify-between items-end">
        <div>
          <h2 class="text-2xl font-semibold text-on-surface mb-1">Event Streams</h2>
          <p class="text-sm text-on-surface-variant">Real-time technical view of message broker throughput and state.</p>
        </div>
        <div class="flex items-center gap-2">
          <span class="flex items-center gap-2 px-3 py-1.5 bg-surface-container-highest rounded-full text-xs font-medium text-on-surface">
            <span
              class="w-2 h-2 rounded-full"
              :class="middlewareFlowActive ? 'bg-emerald-500 live-pulse' : (isBusActive ? 'bg-emerald-500' : 'bg-slate-400')"
            ></span>
            {{ middlewareFlowActive ? 'Simulación activa' : (isBusActive ? 'Bus Active' : 'Bus Offline') }}
          </span>
        </div>
      </div>

      <!-- Metrics Bento Grid -->
      <div class="grid grid-cols-12 gap-6">
        <!-- Latency Metric -->
        <div class="col-span-12 md:col-span-4 bg-white rounded-lg border border-outline-variant p-6 shadow-ambient-1 flex flex-col justify-between">
          <div class="flex justify-between items-start mb-4">
            <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Global Latency</span>
            <span class="material-symbols-outlined text-outline">speed</span>
          </div>
          <div>
            <div class="flex items-baseline gap-1">
              <span class="text-4xl font-bold text-on-surface">{{ formatMetric(liveMetrics.latency_ms) }}</span>
              <span class="text-sm text-on-surface-variant">ms</span>
            </div>
            <div class="mt-1 text-[13px] text-on-surface-variant">
              Estado bus: <span class="font-medium text-on-surface">{{ liveMetrics.bus_status ?? '—' }}</span>
            </div>
          </div>
        </div>

        <!-- EPS Metric -->
        <div class="col-span-12 md:col-span-4 bg-white rounded-lg border border-outline-variant p-6 shadow-ambient-1 flex flex-col justify-between relative overflow-hidden">
          <div class="flex justify-between items-start mb-4 relative z-10">
            <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Events Per Second (EPS)</span>
            <span class="material-symbols-outlined text-outline">bolt</span>
          </div>
          <div class="relative z-10">
            <div class="flex items-baseline gap-1">
              <span class="text-4xl font-bold text-on-surface">{{ formatMetric(liveMetrics.events_per_second) }}</span>
              <span class="text-sm text-on-surface-variant">evt/s</span>
            </div>
            <div class="mt-1 text-[13px] text-on-surface-variant">Registrado: {{ liveMetrics.recorded_at ?? '—' }}</div>
          </div>
          <div class="absolute bottom-0 left-0 w-full h-1/2 bg-gradient-to-t from-secondary-fixed/30 to-transparent pointer-events-none"></div>
        </div>

        <!-- Error Rate Metric -->
        <div class="col-span-12 md:col-span-4 bg-white rounded-lg border border-outline-variant p-6 shadow-ambient-1 flex flex-col justify-between">
          <div class="flex justify-between items-start mb-4">
            <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Error Rate</span>
            <span class="material-symbols-outlined text-outline">warning</span>
          </div>
          <div>
            <div class="flex items-baseline gap-1">
              <span class="text-4xl font-bold text-on-surface">{{ formatErrorRate(liveMetrics.error_rate) }}</span>
              <span class="text-sm text-on-surface-variant">%</span>
            </div>
            <div class="mt-1 text-[13px] text-on-surface-variant">
              <span class="text-error font-medium">{{ formatMetric(liveMetrics.dead_letters) }}</span> dead letters
            </div>
          </div>
        </div>
      </div>

      <!-- Topology Visualization -->
      <div class="bg-white rounded-lg border border-outline-variant p-6 shadow-ambient-1">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h3 class="text-xl font-semibold text-on-surface">System Topology</h3>
            <p class="text-[11px] text-on-surface-variant mt-1">
              Snapshot: <span class="font-mono text-on-surface">{{ topologyGeneratedAt }}</span>
              · Auto-refresh <span class="font-medium">{{ pollSeconds }}s</span>
              · Tipos con actividad reciente en cola: <span class="font-medium text-primary">{{ activeTypesSummary }}</span>
            </p>
          </div>
          <div class="flex flex-wrap items-center gap-2 shrink-0 justify-end">
            <button
              type="button"
              @click="syncConfiguredModules"
              :disabled="syncingRegistry"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors"
              :class="syncingRegistry
                ? 'border-outline-variant/60 text-on-surface-variant cursor-not-allowed opacity-60'
                : 'border-primary/40 bg-primary-container/30 text-on-surface hover:bg-primary-container/50'"
            >
              <span class="material-symbols-outlined text-[16px]" :class="syncingRegistry ? 'animate-spin' : ''">
                {{ syncingRegistry ? 'progress_activity' : 'library_add' }}
              </span>
              {{ syncingRegistry ? 'Sincronizando…' : 'Añadir módulos configurados' }}
            </button>
            <span class="text-xs text-on-surface-variant bg-surface-container-low px-3 py-1 rounded-full border border-outline-variant">
              {{ topologyProducers.length }} producers · {{ topologyConsumers.length }} consumers
            </span>
          </div>
        </div>
        <div
          class="flex items-stretch gap-4 bg-surface-container-low rounded-xl border border-surface-container-highest p-6"
          :class="{ 'middleware-flow-active': middlewareFlowActive }"
        >

          <!-- Producer Nodes -->
          <div class="flex flex-col gap-3 w-[280px] shrink-0">
            <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-1">Producers</p>
            <div
              v-for="producer in topologyProducers"
              :key="producer.id"
              :class="producerCardClass(producer)"
            >
              <div class="flex items-center gap-2 mb-2">
                <div class="w-7 h-7 bg-secondary-fixed rounded flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-on-secondary-fixed text-[16px]">{{ producer.icon }}</span>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold text-on-surface leading-tight truncate">{{ producer.label }}</p>
                  <p class="text-[10px] text-on-surface-variant">{{ producer.protocol }}</p>
                </div>
              </div>
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="evt in producer.events"
                  :key="evt"
                  :class="producerEventChipClass(evt)"
                >
                  ↑ {{ evt }}<template v-if="recentEventTypeCounts[evt] > 1">&nbsp;×{{ recentEventTypeCounts[evt] }}</template>
                </span>
              </div>
            </div>
          </div>

          <!-- Arrow In + Label -->
          <div class="flex flex-col items-center justify-center gap-1 shrink-0 px-2 relative min-h-[120px]">
            <span v-if="middlewareFlowActive" class="flow-packet flow-packet-in" aria-hidden="true"></span>
            <div class="flex-1 w-px flow-connector-line bg-outline-variant"></div>
            <span class="material-symbols-outlined text-outline text-[20px]">arrow_forward</span>
            <p class="text-[9px] font-bold text-on-surface-variant uppercase tracking-widest rotate-90 whitespace-nowrap my-2">Publishes</p>
            <span class="material-symbols-outlined text-outline text-[20px]">arrow_forward</span>
            <div class="flex-1 w-px flow-connector-line bg-outline-variant"></div>
          </div>

          <!-- Event Bus -->
          <div class="flex flex-col items-center justify-center shrink-0 px-4">
            <div
              class="bg-primary-container border-2 rounded-2xl py-6 px-5 flex flex-col items-center justify-center shadow-lg relative transition-all duration-300"
              :class="middlewareFlowActive || busHasRecentActivity ? 'border-emerald-500 ring-4 ring-emerald-400/40 scale-[1.02]' : 'border-primary'"
            >
              <div
                class="absolute -top-1.5 -right-1.5 w-3.5 h-3.5 rounded-full ring-2 ring-white transition-colors"
                :class="middlewareFlowActive || busHasRecentActivity ? 'bg-emerald-400 animate-pulse' : 'bg-emerald-500'"
              ></div>
              <span class="material-symbols-outlined text-on-primary-container text-[36px] mb-2">hub</span>
              <span class="text-[11px] font-black text-on-primary-container tracking-[0.15em]">EVENT BUS</span>
              <span class="mt-2 text-[9px] text-on-primary-container/70 font-medium">FIFO · Async</span>
            </div>
          </div>

          <!-- Arrow Out + Label -->
          <div class="flex flex-col items-center justify-center gap-1 shrink-0 px-2 relative min-h-[120px]">
            <span v-if="middlewareFlowActive" class="flow-packet flow-packet-out" aria-hidden="true"></span>
            <div class="flex-1 w-px flow-connector-line bg-outline-variant"></div>
            <span class="material-symbols-outlined text-outline text-[20px]">arrow_forward</span>
            <p class="text-[9px] font-bold text-on-surface-variant uppercase tracking-widest rotate-90 whitespace-nowrap my-2">Dispatches</p>
            <span class="material-symbols-outlined text-outline text-[20px]">arrow_forward</span>
            <div class="flex-1 w-px flow-connector-line bg-outline-variant"></div>
          </div>

          <!-- Consumer Nodes -->
          <div class="flex flex-col gap-3 flex-1 min-w-0">
            <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-1">Consumers</p>
            <div
              v-for="consumer in topologyConsumers"
              :key="consumer.id"
              :class="consumerCardClass(consumer)"
            >
              <div class="flex items-center gap-2 mb-2">
                <div class="w-7 h-7 bg-surface-variant rounded flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-on-surface-variant text-[16px]">{{ consumer.icon }}</span>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold text-on-surface leading-tight truncate">{{ consumer.label }}</p>
                  <p class="text-[10px] text-on-surface-variant">Topic: {{ consumer.topic }}</p>
                </div>
              </div>
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="evt in consumer.subscribedTo"
                  :key="evt"
                  :class="consumerEventChipClass(consumer, evt)"
                >
                  ↓ {{ evt }}<template v-if="recentEventTypeCounts[evt] > 1">&nbsp;×{{ recentEventTypeCounts[evt] }}</template>
                </span>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Event Queue Table -->
      <div class="bg-white rounded-lg border border-outline-variant shadow-ambient-1 overflow-hidden">
        <div class="p-6 border-b border-surface-container-highest flex justify-between items-center">
          <h3 class="text-xl font-semibold text-on-surface">Live Event Queue (FIFO)</h3>
          <div class="flex items-center gap-2">
            <span class="text-xs text-on-surface-variant">Actualización en {{ countdown }}s</span>
            <button
              @click="refreshData"
              class="px-3 py-1.5 border border-outline-variant rounded text-sm font-medium text-on-surface hover:bg-surface-container transition-colors flex items-center gap-1"
            >
              <span class="material-symbols-outlined text-[16px]">refresh</span>
              Refresh
            </button>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-surface-container-low border-b border-surface-container-highest">
                <th class="py-2 px-6 text-xs font-bold uppercase tracking-wider text-on-surface-variant w-24">ID</th>
                <th class="py-2 px-6 text-xs font-bold uppercase tracking-wider text-on-surface-variant">Event Type</th>
                <th class="py-2 px-6 text-xs font-bold uppercase tracking-wider text-on-surface-variant">Origin</th>
                <th class="py-2 px-6 text-xs font-bold uppercase tracking-wider text-on-surface-variant">Consumers</th>
                <th class="py-2 px-6 text-xs font-bold uppercase tracking-wider text-on-surface-variant">Timestamp</th>
                <th class="py-2 px-6 text-xs font-bold uppercase tracking-wider text-on-surface-variant text-right">Status</th>
              </tr>
            </thead>
            <tbody class="text-sm text-on-surface">
              <tr
                v-for="event in displayQueue"
                :key="event.id ?? event.event_id"
                class="border-b border-surface-container-highest hover:bg-surface-container-low transition-colors"
              >
                <td class="py-4 px-6 text-outline font-mono text-xs">#{{ truncateId(event.id ?? event.event_id) }}</td>
                <td class="py-4 px-6 font-medium">{{ event.event_type ?? event.type }}</td>
                <td class="py-4 px-6 text-on-surface-variant">{{ event.origin ?? event.source ?? '—' }}</td>
                <td class="py-4 px-6 text-on-surface-variant">{{ formatConsumers(event.consumers) }}</td>
                <td class="py-4 px-6 text-on-surface-variant font-mono text-xs">{{ formatTimestamp(event.published_at ?? event.publishedAt ?? event.dispatched_at ?? event.dispatchedAt ?? event.created_at ?? event.timestamp) }}</td>
                <td class="py-4 px-6 text-right">
                  <span :class="statusBadgeClass(event.status)">
                    <span v-if="event.status === 'Pendiente' || event.status === 'PENDING'" class="w-1.5 h-1.5 rounded-full bg-primary mr-1.5 inline-block"></span>
                    {{ event.status ?? 'Procesado' }}
                  </span>
                </td>
              </tr>
              <tr v-if="displayQueue.length === 0">
                <td colspan="6" class="py-12 text-center text-on-surface-variant text-sm">
                  <span class="material-symbols-outlined text-[40px] block mb-2 text-outline-variant">inbox</span>
                  No events in queue
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Dead Letters Panel -->
      <div v-if="deadLetters.length > 0" class="bg-white rounded-lg border border-error/30 shadow-ambient-1 overflow-hidden">
        <div class="p-6 border-b border-error/20 bg-error-container/20 flex items-center gap-3">
          <span class="material-symbols-outlined text-error">error</span>
          <h3 class="text-xl font-semibold text-on-surface">Dead Letter Queue</h3>
          <span class="ml-auto bg-error-container text-on-error-container text-xs font-bold px-2 py-0.5 rounded">{{ deadLetters.length }} items</span>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-surface-container-low border-b border-surface-container-highest">
                <th class="py-2 px-6 text-xs font-bold uppercase tracking-wider text-on-surface-variant">Event ID</th>
                <th class="py-2 px-6 text-xs font-bold uppercase tracking-wider text-on-surface-variant">Type</th>
                <th class="py-2 px-6 text-xs font-bold uppercase tracking-wider text-on-surface-variant">Reason</th>
                <th class="py-2 px-6 text-xs font-bold uppercase tracking-wider text-on-surface-variant">Failed At</th>
                <th class="py-2 px-6 text-xs font-bold uppercase tracking-wider text-on-surface-variant text-right">Action</th>
              </tr>
            </thead>
            <tbody class="text-sm text-on-surface">
              <tr v-for="dl in deadLetters" :key="dl.id" class="border-b border-surface-container-highest hover:bg-surface-container-low transition-colors">
                <td class="py-4 px-6 font-mono text-xs text-outline">#{{ truncateId(dl.id ?? dl.event_id) }}</td>
                <td class="py-4 px-6 font-medium">{{ dl.event_type ?? dl.type }}</td>
                <td class="py-4 px-6 text-error text-xs">{{ dl.reason ?? dl.failure_reason ?? 'Processing error' }}</td>
                <td class="py-4 px-6 text-on-surface-variant font-mono text-xs">{{ formatTimestamp(dl.failed_at ?? dl.created_at) }}</td>
                <td class="py-4 px-6 text-right">
                  <button class="text-xs font-medium text-primary hover:underline">Retry</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { onNodesChanged } from '@/platform-node-events';

export default {
  name: 'MiddlewareIndex',
  components: { AppLayout },
  props: {
    metrics: { type: Object, default: () => ({}) },
    queue: { type: Array, default: () => [] },
    topology: { type: Object, default: () => ({}) },
    deadLetters: { type: Array, default: () => [] },
    busStatus: { type: [Boolean, String, Object], default: true },
  },
  setup(props) {
    const POLL_SECONDS_IDLE = 45;
    const POLL_SECONDS_ACTIVE = 1;
    const POLL_QUEUE_ACTIVE = 1;

    const syncingRegistry = ref(false);
    const simulationPulse = ref({ active: false });

    const liveQueue = ref([...(props.queue ?? [])]);
    const liveMetrics = ref({ ...(props.metrics ?? {}) });
    const pollSeconds = ref(POLL_SECONDS_IDLE);
    const countdown = ref(POLL_SECONDS_IDLE);
    let refreshTimer = null;
    let queueTimer = null;
    let countdownTimer = null;

    watch(() => props.queue, (q) => { liveQueue.value = [...(q ?? [])]; }, { deep: true });
    watch(() => props.metrics, (m) => { liveMetrics.value = { ...(m ?? {}) }; }, { deep: true });

    const isBusActive = computed(() => {
      const s = String(liveMetrics.value.bus_status ?? props.busStatus ?? '').toUpperCase();
      if (['ACTIVE', 'RUNNING', 'ONLINE', 'OK'].includes(s)) return true;
      if (['STOPPED', 'OFFLINE', 'DOWN'].includes(s)) return false;
      return !!props.busStatus && props.busStatus !== 'STOPPED';
    });

    function formatMetric(v) {
      if (v === undefined || v === null || v === '') return '—';
      return formatNumber(Number(v));
    }

    function formatErrorRate(v) {
      if (v === undefined || v === null || v === '') return '—';
      return Number(v).toFixed(2);
    }

    function nodeIcon(id) {
      const map = {
        ventas_web: 'language', retail_pos: 'point_of_sale', pos: 'point_of_sale',
        inventario: 'inventory_2', pedidos: 'receipt_long', dashboard: 'dashboard',
        erp_sync: 'sync_alt', middleware: 'hub',
      };
      return map[String(id).toLowerCase()] ?? 'device_hub';
    }

    const liveTopology = ref({
      producers: [...(props.topology?.producers ?? [])],
      consumers: [...(props.topology?.consumers ?? [])],
      bus: { ...(props.topology?.bus ?? {}) },
      generated_at: props.topology?.generated_at ?? null,
    });

    watch(() => props.topology, (t) => {
      liveTopology.value = {
        producers: [...(t?.producers ?? [])],
        consumers: [...(t?.consumers ?? [])],
        bus: { ...(t?.bus ?? {}) },
        generated_at: t?.generated_at ?? null,
      };
    }, { deep: true });

    const topologyGeneratedAt = computed(() => liveTopology.value.generated_at ?? '—');

    /** Same window as dashboard idle reconciliation (GetSystemNodeStatusUseCase) + topology subtitle. */
    const FLOW_ACTIVITY_MS = 45 * 1000;

    function publishedAtMillis(entry) {
      const raw =
        entry.published_at ?? entry.publishedAt ?? entry.created_at ?? entry.createdAt ?? entry.timestamp ?? null;
      if (raw == null || raw === '') return null;
      let s = String(raw).trim();
      if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/.test(s)) {
        s = s.replace(' ', 'T');
      }
      const t = new Date(s).getTime();
      return Number.isNaN(t) ? null : t;
    }

    function queueStatusUpper(entry) {
      return String(entry.status ?? entry.state ?? '').trim().toUpperCase();
    }

    /** Pending rows always paint flow; processed rows only if published within FLOW_ACTIVITY_MS. */
    function entryCountsForTopologyFlow(entry, nowMs) {
      const st = queueStatusUpper(entry);
      if (st === 'PENDING' || st === 'PENDIENTE') return true;
      const ts = publishedAtMillis(entry);
      if (ts == null) return false;
      return nowMs - ts <= FLOW_ACTIVITY_MS;
    }

    const recentEventTypeCounts = computed(() => {
      const counts = {};
      const now = Date.now();
      const slice = liveQueue.value.slice(0, 100);
      for (const entry of slice) {
        if (!entryCountsForTopologyFlow(entry, now)) continue;
        const t = entry.event_type ?? entry.eventType ?? entry.type;
        if (!t) continue;
        counts[t] = (counts[t] ?? 0) + 1;
      }
      return counts;
    });

    /** True when the queue slice shows event types pending in the recent window — drives topology paint (independent of bus_status / EPS). */
    const hasQueuedFlowActivity = computed(
      () => Object.keys(recentEventTypeCounts.value).length > 0,
    );

    const busHasRecentActivity = computed(() => hasQueuedFlowActivity.value);

    const middlewareFlowActive = computed(
      () => simulationPulse.value.active === true || hasQueuedFlowActivity.value,
    );

    const activeTypesSummary = computed(() => {
      const c = recentEventTypeCounts.value;
      const parts = Object.entries(c)
        .filter(([, n]) => n > 0)
        .sort((a, b) => b[1] - a[1])
        .map(([t, n]) => `${t}(${n})`);
      return parts.length ? parts.slice(0, 10).join(', ') : '— (sin eventos pendientes ni recientes en 45s)';
    });

    function producerEventChipClass(evt) {
      const n = recentEventTypeCounts.value[evt] ?? 0;
      const base = 'px-1.5 py-0.5 text-[10px] font-medium rounded transition-all duration-300';
      if (n > 0) {
        return `${base} bg-emerald-600 text-white ring-2 ring-emerald-400/70 shadow-sm`;
      }
      return `${base} bg-secondary-fixed/50 text-on-secondary-fixed`;
    }

    function consumerEventChipClass(consumer, evt) {
      const counts = recentEventTypeCounts.value;
      const n = counts[evt] ?? 0;
      const base = 'px-1.5 py-0.5 text-[10px] font-medium rounded transition-all duration-300';
      if (n > 0) {
        return `${base} bg-blue-600 text-white ring-2 ring-blue-400/70 shadow-sm`;
      }
      if (!hasQueuedFlowActivity.value) {
        return `${base} bg-surface-container-highest text-on-surface-variant`;
      }
      const subs = consumer.subscribedTo ?? [];
      const receivingOther = subs.some((e) => e !== evt && (counts[e] ?? 0) > 0);
      if (receivingOther) {
        return `${base} bg-red-100 text-red-900 ring-1 ring-red-300/90`;
      }
      return `${base} bg-surface-container-highest text-on-surface-variant`;
    }

    function producerCardClass(producer) {
      const base = 'rounded-lg px-3 py-2.5 shadow-sm border transition-all duration-300';
      const events = producer.events ?? [];
      if (!hasQueuedFlowActivity.value) {
        return `${base} bg-white border-outline-variant`;
      }
      const publishing = events.some((e) => (recentEventTypeCounts.value[e] ?? 0) > 0);
      if (publishing) {
        return `${base} bg-emerald-50/70 border-emerald-400 ring-2 ring-emerald-500/45`;
      }
      return `${base} bg-white border-outline-variant opacity-80`;
    }

    function consumerCardClass(consumer) {
      const base = 'rounded-lg px-3 py-2.5 shadow-sm border transition-all duration-300';
      const subs = consumer.subscribedTo ?? [];
      if (!hasQueuedFlowActivity.value) {
        return `${base} bg-white border-outline-variant`;
      }
      const counts = recentEventTypeCounts.value;
      const receives = subs.some((e) => (counts[e] ?? 0) > 0);
      if (receives) {
        return `${base} bg-blue-50/70 border-blue-400 ring-2 ring-blue-500/45`;
      }
      return `${base} bg-red-50/60 border-red-300 ring-2 ring-red-400/55`;
    }

    const topologyProducers = computed(() => {
      const t = liveTopology.value;
      if (t?.producers?.length) {
        return t.producers.map((p) => ({
          id: p.id ?? p,
          label: p.label ?? p.name ?? String(p.id ?? p),
          icon: nodeIcon(p.id ?? p),
          events: Array.isArray(p.events) ? p.events : [],
          protocol: p.protocol ?? 'REST API',
        }));
      }
      return [];
    });

    const topologyConsumers = computed(() => {
      const t = liveTopology.value;
      if (t?.consumers?.length) {
        return t.consumers.map((c) => ({
          id: c.id ?? c,
          label: c.label ?? c.name ?? String(c.id ?? c),
          icon: nodeIcon(c.id ?? c),
          subscribedTo: Array.isArray(c.subscribed_to)
            ? c.subscribed_to
            : Array.isArray(c.subscribedTo)
              ? c.subscribedTo
              : [],
          topic: c.topic ?? 'domain.events',
        }));
      }
      return [];
    });

    const displayQueue = computed(() => liveQueue.value.slice(0, 20));

    function formatNumber(n) {
      return Number(n).toLocaleString();
    }

    function truncateId(id) {
      if (!id) return '------';
      const str = String(id);
      return str.length > 6 ? str.slice(-6).toUpperCase() : str.toUpperCase();
    }

    function formatTimestamp(ts) {
      if (!ts) return '—';
      try {
        const d = new Date(ts);
        return d.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' }) +
               '.' + String(d.getMilliseconds()).padStart(3, '0');
      } catch {
        return String(ts);
      }
    }

    function formatConsumers(consumers) {
      if (!consumers) return '—';
      if (Array.isArray(consumers)) return consumers.join(', ');
      return String(consumers);
    }

    function statusBadgeClass(status) {
      const base = 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium';
      switch (status) {
        case 'PENDING':
        case 'Pendiente':
          return `${base} bg-secondary-fixed text-on-secondary-fixed`;
        case 'Fallido':
        case 'Failed':
          return `${base} bg-error-container text-on-error-container`;
        case 'Procesado':
        case 'Processed':
        default:
          return `${base} bg-surface-container-highest text-on-surface-variant`;
      }
    }

    async function syncConfiguredModules() {
      if (syncingRegistry.value) return;
      syncingRegistry.value = true;
      try {
        await window.axios.post('/api/middleware/registry/sync-config');
        await refreshData();
      } catch (e) {
        console.error('Registry sync failed:', e);
      } finally {
        syncingRegistry.value = false;
      }
    }

    async function fetchSimulationPulse() {
      try {
        const res = await window.axios.get('/api/middleware/simulation-pulse');
        simulationPulse.value = res.data?.data ?? res.data ?? { active: false };
      } catch {
        simulationPulse.value = { active: false };
      }
    }

    async function refreshQueueOnly() {
      try {
        const [queueRes, pulseRes] = await Promise.all([
          window.axios.get('/api/middleware/queue', { params: { limit: 100 } }),
          window.axios.get('/api/middleware/simulation-pulse'),
        ]);
        liveQueue.value = queueRes.data?.data ?? queueRes.data ?? [];
        simulationPulse.value = pulseRes.data?.data ?? pulseRes.data ?? { active: false };
      } catch (e) {
        console.error('Middleware queue refresh failed:', e);
      }
    }

    function applyPollCadence() {
      const active = middlewareFlowActive.value;
      const next = active ? POLL_SECONDS_ACTIVE : POLL_SECONDS_IDLE;
      if (pollSeconds.value !== next) {
        pollSeconds.value = next;
        countdown.value = next;
      }
      clearInterval(refreshTimer);
      refreshTimer = setInterval(refreshData, next * 1000);
      clearInterval(queueTimer);
      if (active) {
        queueTimer = setInterval(refreshQueueOnly, POLL_QUEUE_ACTIVE * 1000);
      }
    }

    async function refreshData() {
      try {
        const [metricsRes, queueRes, topologyRes] = await Promise.all([
          window.axios.get('/api/middleware/metrics'),
          window.axios.get('/api/middleware/queue', { params: { limit: 100 } }),
          window.axios.get('/api/middleware/topology'),
        ]);
        await fetchSimulationPulse();
        const metricsPayload = metricsRes.data?.data ?? metricsRes.data ?? {};
        liveMetrics.value = { ...metricsPayload };
        liveQueue.value = queueRes.data?.data ?? queueRes.data ?? [];
        const topo = topologyRes.data?.data ?? topologyRes.data;
        if (topo && typeof topo === 'object') {
          liveTopology.value = {
            producers: [...(topo.producers ?? [])],
            consumers: [...(topo.consumers ?? [])],
            bus: { ...(topo.bus ?? {}) },
            generated_at: topo.generated_at ?? null,
          };
        }
      } catch (e) {
        console.error('Middleware refresh failed:', e);
      }
      countdown.value = pollSeconds.value;
      applyPollCadence();
    }

    function startTimers() {
      refreshTimer = setInterval(refreshData, pollSeconds.value * 1000);
      countdownTimer = setInterval(() => {
        countdown.value = Math.max(0, countdown.value - 1);
      }, 1000);
    }

    watch(middlewareFlowActive, () => applyPollCadence());

    let stopNodesListener = null;

    onMounted(() => {
      refreshData();
      startTimers();
      stopNodesListener = onNodesChanged(() => {
        refreshData();
        countdown.value = pollSeconds.value;
      });
    });
    onUnmounted(() => {
      clearInterval(refreshTimer);
      clearInterval(queueTimer);
      clearInterval(countdownTimer);
      stopNodesListener?.();
    });

    return {
      liveQueue, liveMetrics, liveTopology, countdown, topologyProducers, topologyConsumers,
      topologyGeneratedAt, activeTypesSummary, recentEventTypeCounts, busHasRecentActivity,
      middlewareFlowActive, simulationPulse,
      pollSeconds,
      producerEventChipClass, consumerEventChipClass, producerCardClass, consumerCardClass,
      isBusActive,
      displayQueue, formatNumber, formatMetric, formatErrorRate, truncateId, formatTimestamp, formatConsumers,
      statusBadgeClass, refreshData, syncConfiguredModules, syncingRegistry, nodeIcon,
    };
  },
};
</script>

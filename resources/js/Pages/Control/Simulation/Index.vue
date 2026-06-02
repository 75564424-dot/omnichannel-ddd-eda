<template>
  <ControlLayout title="Historial de simulaciones" active-nav="simulations">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-[#e1fdff]">Simulaciones ejecutadas</h2>
        <p class="mt-1 text-sm text-[#b9cacb]">
          Listado por fecha y hora. Los reportes detallados están disponibles al finalizar cada ejecución.
        </p>
        <p v-if="hasActiveRuns" class="mt-2 flex items-center gap-2 text-xs text-[#00dbe7]">
          <span class="inline-block h-2 w-2 rounded-full bg-[#00dbe7] animate-pulse"></span>
          Actualizando progreso en vivo cada 2 s
        </p>
      </div>
      <Link
        href="/control/companies"
        class="rounded-lg border border-[#00dbe7]/40 px-4 py-2 text-xs font-bold uppercase tracking-wide text-[#00dbe7] hover:bg-[#00dbe7]/10"
      >
        Nueva simulación
      </Link>
    </div>

    <form class="mb-6 flex flex-wrap items-end gap-4" @submit.prevent="applyFilter">
      <div class="min-w-[220px]">
        <label class="mb-1 block text-[10px] uppercase text-[#849495]">Empresa</label>
        <select v-model="filterForm.tenant_id" class="input-dark w-full">
          <option value="">Todas</option>
          <option v-for="t in tenants" :key="t.id" :value="t.id">{{ t.name }} ({{ t.slug }})</option>
        </select>
      </div>
      <button
        type="submit"
        class="rounded-lg bg-[#00f2ff]/20 px-4 py-2 text-xs font-bold uppercase text-[#00f2ff]"
      >
        Filtrar
      </button>
      <Link
        v-if="filters.tenant_id"
        href="/control/simulations"
        class="py-2 text-xs text-[#b9cacb] hover:text-[#e1fdff]"
      >
        Limpiar filtro
      </Link>
    </form>

    <section class="overflow-hidden rounded-xl border border-white/8 bg-[#121820]/70">
      <table class="w-full text-sm">
        <thead class="bg-[#191c1f]/80 text-left text-[10px] uppercase text-[#b9cacb]">
          <tr>
            <th class="px-6 py-3">Fecha / hora</th>
            <th class="px-6 py-3">Empresa</th>
            <th class="px-6 py-3">Configuración</th>
            <th class="px-6 py-3">Progreso</th>
            <th class="px-6 py-3">Estado</th>
            <th class="px-6 py-3 text-right">Reporte</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
          <tr
            v-for="run in displayRuns"
            :key="run.id"
            class="hover:bg-white/5"
            :class="run.id === highlightRunId ? 'bg-[#00f2ff]/5' : ''"
          >
            <td class="px-6 py-3 font-mono text-xs text-[#e1fdff]">
              <div>{{ run.created_at }}</div>
              <div v-if="run.finished_at" class="mt-0.5 text-[10px] text-[#849495]">Fin: {{ run.finished_at }}</div>
            </td>
            <td class="px-6 py-3">
              <p class="font-medium">{{ run.tenant_name }}</p>
              <p class="font-mono text-[10px] text-[#849495]">{{ run.tenant_slug }}</p>
            </td>
            <td class="px-6 py-3 text-xs text-[#b9cacb]">
              {{ run.events_per_minute }}/min × {{ run.duration_minutes }} min
              <span class="block font-mono text-[10px] text-[#849495]">{{ run.fixture_slug }}</span>
            </td>
            <td class="px-6 py-3 font-mono text-xs">
              {{ run.published }} / {{ run.planned_total }}
              <span v-if="isRunActive(run.status)" class="ml-1 text-[#00dbe7]">({{ run.progress_percent }}%)</span>
              <div
                v-if="isRunActive(run.status)"
                class="mt-1.5 h-1.5 w-full max-w-[140px] overflow-hidden rounded-full bg-white/10"
              >
                <div
                  class="h-full rounded-full bg-[#00dbe7] transition-all duration-500"
                  :style="{ width: `${run.progress_percent ?? 0}%` }"
                ></div>
              </div>
            </td>
            <td class="px-6 py-3">
              <span class="text-xs uppercase" :class="statusClass(run.status)">{{ statusLabel(run.status) }}</span>
              <p v-if="run.status === 'failed' && run.error_message" class="mt-1 max-w-xs truncate text-[10px] text-red-300">
                {{ run.error_message }}
              </p>
            </td>
            <td class="px-6 py-3 text-right">
              <Link
                v-if="run.can_view_report"
                :href="`/control/simulations/${run.id}/report`"
                class="rounded-lg bg-[#00f2ff] px-3 py-1.5 text-xs font-bold text-[#00363a] hover:opacity-90"
              >
                Ver reporte
              </Link>
              <span v-else-if="isRunActive(run.status)" class="text-[10px] text-[#849495]">En ejecución…</span>
              <span v-else class="text-[10px] text-[#849495]">—</span>
            </td>
          </tr>
          <tr v-if="!displayRuns.length">
            <td colspan="6" class="px-6 py-12 text-center text-[#849495]">
              No hay simulaciones registradas.
              <Link href="/control/companies" class="text-[#00dbe7] hover:underline">Iniciar una</Link>.
            </td>
          </tr>
        </tbody>
      </table>
    </section>

    <div v-if="runs.links?.length > 3" class="mt-4 flex flex-wrap gap-2">
      <Link
        v-for="link in runs.links"
        :key="link.label"
        :href="link.url || '#'"
        class="rounded px-3 py-1 text-xs"
        :class="link.active ? 'bg-[#00f2ff] text-[#00363a]' : link.url ? 'text-[#b9cacb] hover:bg-white/5' : 'pointer-events-none text-[#849495]'"
        v-html="link.label"
      />
    </div>
  </ControlLayout>
</template>

<script setup>
import ControlLayout from '@/Layouts/ControlLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';

const props = defineProps({
  runs: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  tenants: { type: Array, default: () => [] },
  highlight_run_id: { type: String, default: null },
});

const highlightRunId = computed(() => props.highlight_run_id);

const filterForm = reactive({
  tenant_id: props.filters.tenant_id ?? '',
});

const liveRuns = ref([...(props.runs?.data ?? [])]);
let pollTimer = null;
let pollCycles = 0;
const MAX_POLL_CYCLES = 150; // 5 min at 2 s/cycle — forces server reload (stale guard) if exceeded

function mergeRunsFromServer(rows) {
  if (!Array.isArray(rows) || rows.length === 0) {
    return;
  }
  const byId = Object.fromEntries(liveRuns.value.map((r) => [r.id, r]));
  liveRuns.value = rows.map((row) => {
    const local = byId[row.id];
    if (!local || !isRunActive(local.status)) {
      return { ...row };
    }
    const localProgress = Number(local.published ?? 0);
    const serverProgress = Number(row.published ?? 0);
    return serverProgress >= localProgress ? { ...row } : { ...row, ...local };
  });
}

watch(
  () => props.runs?.data,
  (rows) => mergeRunsFromServer(rows),
  { deep: true },
);

const displayRuns = computed(() => liveRuns.value);

const hasActiveRuns = computed(() => liveRuns.value.some((run) => isRunActive(run.status)));

function applyFilter() {
  router.get('/control/simulations', {
    tenant_id: filterForm.tenant_id || undefined,
  }, { preserveState: true, replace: true });
}

function isRunActive(status) {
  return status === 'pending' || status === 'running';
}

function statusLabel(status) {
  const map = {
    pending: 'Pendiente',
    running: 'En curso',
    completed: 'Completada',
    failed: 'Fallida',
  };
  return map[status] || status;
}

function statusClass(status) {
  if (status === 'running' || status === 'pending') return 'text-[#00dbe7]';
  if (status === 'completed') return 'text-emerald-400';
  if (status === 'failed') return 'text-red-400';
  return 'text-[#849495]';
}

function mergeRunFromStatus(payload) {
  if (!payload?.run) return;
  const updated = payload.run;
  const idx = liveRuns.value.findIndex((r) => r.id === updated.id);
  if (idx < 0) return;

  liveRuns.value[idx] = {
    ...liveRuns.value[idx],
    status: updated.status,
    published: updated.published ?? liveRuns.value[idx].published,
    planned_total: updated.planned_total ?? liveRuns.value[idx].planned_total,
    progress_percent: updated.progress_percent ?? liveRuns.value[idx].progress_percent,
    finished_at: updated.finished_at ?? liveRuns.value[idx].finished_at,
    error_message: updated.error_message ?? liveRuns.value[idx].error_message,
    can_view_report: updated.can_view_report ?? liveRuns.value[idx].can_view_report,
  };
}

async function fetchRunStatus(runId) {
  const res = await fetch(`/control/simulations/${runId}/status`, {
    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    credentials: 'same-origin',
  });
  if (!res.ok) return null;
  return res.json();
}

async function pollActiveRuns() {
  const active = liveRuns.value.filter((run) => isRunActive(run.status));
  if (active.length === 0) {
    stopPolling();
    return;
  }

  pollCycles++;
  if (pollCycles > MAX_POLL_CYCLES) {
    stopPolling();
    pollCycles = 0;
    router.reload({ only: ['runs'] });
    return;
  }

  await Promise.all(
    active.map(async (run) => {
      const data = await fetchRunStatus(run.id);
      if (data) mergeRunFromStatus(data);
    }),
  );

  if (!liveRuns.value.some((run) => isRunActive(run.status))) {
    stopPolling();
  }
}

function stopPolling() {
  if (pollTimer) {
    clearInterval(pollTimer);
    pollTimer = null;
  }
}

function startPolling() {
  stopPolling();
  pollCycles = 0;
  if (!hasActiveRuns.value) return;
  pollActiveRuns();
  pollTimer = setInterval(pollActiveRuns, 2000);
}

onMounted(() => {
  startPolling();
});

onUnmounted(() => {
  stopPolling();
});

watch(hasActiveRuns, (active) => {
  if (active) startPolling();
  else stopPolling();
});
</script>

<style scoped>
.input-dark {
  @apply rounded-lg border border-white/10 bg-[#191c1f] px-3 py-2 text-sm text-[#e1e2e7] outline-none focus:border-[#00dbe7]/50;
}
</style>

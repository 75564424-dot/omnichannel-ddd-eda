<template>
  <ControlLayout title="Historial de simulaciones" active-nav="simulations">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-[#e1fdff]">Simulaciones ejecutadas</h2>
        <p class="mt-1 text-sm text-[#b9cacb]">
          Listado por fecha y hora. Los reportes detallados están disponibles al finalizar cada ejecución.
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
            v-for="run in runs.data"
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
          <tr v-if="!runs.data?.length">
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
import { computed, reactive } from 'vue';

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
</script>

<style scoped>
.input-dark {
  @apply rounded-lg border border-white/10 bg-[#191c1f] px-3 py-2 text-sm text-[#e1e2e7] outline-none focus:border-[#00dbe7]/50;
}
</style>

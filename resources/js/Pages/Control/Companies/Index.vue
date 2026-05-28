<template>

  <ControlLayout title="Gestión de empresas" active-nav="companies">

    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">

      <div>

        <h2 class="text-2xl font-bold text-[#e1fdff]">Empresas (tenants)</h2>

        <p class="mt-1 text-sm text-[#b9cacb]">

          Simulación de tráfico y administración. Cada empresa en producción requiere su propia instancia — ver

          <Link href="/control/simulations" class="text-[#00dbe7] hover:underline">historial</Link>

          o la ficha de empresa. Nuevas empresas:

          <Link href="/control/provisioning" class="text-[#00dbe7] hover:underline">Provisioning</Link>.

        </p>

      </div>

    </div>



    <div v-if="$page.props.flash?.message" class="mb-4 rounded-lg border border-[#00f2ff]/30 bg-[#00f2ff]/10 px-4 py-3 text-sm">

      {{ $page.props.flash.message }}

    </div>



    <div
      v-if="activeRun && isRunActive(activeRun.run?.status)"
      class="mb-4 rounded-xl border border-[#00dbe7]/30 bg-[#121820]/90 p-4"
    >
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Simulación en curso</p>
          <p class="mt-1 text-sm text-[#b9cacb]">
            {{ activeRun.run?.tenant_name }} · {{ activeRun.run?.published ?? 0 }} / {{ activeRun.run?.planned_total }} eventos
          </p>
        </div>
        <p class="font-mono text-lg text-[#00f2ff]">{{ activeRun.run?.progress_percent ?? 0 }}%</p>
      </div>
      <div class="mt-3 h-2 overflow-hidden rounded-full bg-[#191c1f]">
        <div
          class="h-full bg-[#00f2ff] transition-all duration-500"
          :style="{ width: `${activeRun.run?.progress_percent ?? 0}%` }"
        />
      </div>
      <p class="mt-2 text-[10px] text-[#849495]">Puede cerrar esta pestaña; el proceso continúa en segundo plano.</p>
    </div>



    <div
      v-else-if="activeRun?.run?.status === 'completed'"
      class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm"
    >
      <span>Simulación finalizada: {{ activeRun.run?.published }} eventos publicados.</span>
      <div class="flex gap-2">
        <Link
          :href="`/control/simulations?run=${activeRun.run.id}`"
          class="rounded-lg border border-[#00dbe7]/40 px-4 py-1.5 text-xs font-bold text-[#00dbe7]"
        >
          Ver en historial
        </Link>
        <Link
          :href="`/control/simulations/${activeRun.run.id}/report`"
          class="rounded-lg bg-[#00f2ff] px-4 py-1.5 text-xs font-bold text-[#00363a]"
        >
          Ver reporte
        </Link>
      </div>
    </div>



    <div
      v-else-if="activeRun?.run?.status === 'failed'"
      class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300"
    >
      Simulación fallida: {{ activeRun.run?.error_message || 'Error desconocido' }}
    </div>



    <form

      class="mb-8 rounded-xl border border-[#00dbe7]/25 bg-[#121820]/70 p-6"

      @submit.prevent="runSimulation"

    >

      <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Automatización de simulación</h3>

      <p class="mt-2 text-xs text-[#849495]">

        Publica eventos en el bus de la instancia desplegada (<span class="font-mono text-[#b9cacb]">{{ instanceSlug }}</span>).

        El proceso puede tardar varios minutos según la duración.

        Consulte resultados y reportes en

        <Link href="/control/simulations" class="text-[#00dbe7] hover:underline">Historial de simulaciones</Link>.

      </p>



      <p v-if="simForm.errors.simulation" class="mt-3 rounded border border-red-500/30 bg-red-500/10 px-3 py-2 text-xs text-red-300">

        {{ simForm.errors.simulation }}

      </p>



      <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">

        <div>

          <label class="mb-1 block text-[10px] uppercase text-[#849495]">Empresa</label>

          <select v-model="simForm.tenant_id" required class="input-dark w-full">

            <option value="" disabled>Seleccione…</option>

            <option

              v-for="t in tenants"

              :key="t.id"

              :value="t.id"

              :disabled="!t.can_simulate"

            >

              {{ t.name }} ({{ t.slug }}){{ t.can_simulate ? '' : ' — no disponible' }}

            </option>

          </select>

          <p v-if="selectedTenant && !selectedTenant.can_simulate" class="mt-1 text-xs text-amber-400/90">

            {{ selectedTenant.simulate_block_reason }}

          </p>

        </div>



        <div>

          <label class="mb-1 block text-[10px] uppercase text-[#849495]">Eventos por minuto</label>

          <input

            v-model.number="simForm.events_per_minute"

            type="number"

            min="1"

            max="600"

            required

            class="input-dark w-full"

          />

        </div>



        <div>

          <label class="mb-1 block text-[10px] uppercase text-[#849495]">Duración (minutos)</label>

          <input

            v-model.number="simForm.duration_minutes"

            type="number"

            min="1"

            max="120"

            required

            class="input-dark w-full"

          />

        </div>



        <div>

          <label class="mb-1 block text-[10px] uppercase text-[#849495]">Total eventos</label>

          <input

            :value="plannedTotal"

            type="number"

            readonly

            class="input-dark w-full opacity-80"

          />

          <p class="mt-1 text-[10px] text-[#849495]">= eventos/min × minutos</p>

        </div>

      </div>



      <label class="mt-4 flex items-center gap-2 text-xs text-[#b9cacb]">

        <input v-model="simForm.prepare_first" type="checkbox" class="rounded" />

        Preparar antes (sync catálogo + registry)

      </label>



      <div class="mt-4 flex flex-wrap items-center gap-3">

        <button

          type="submit"

          class="rounded-lg bg-[#00f2ff] px-5 py-2 text-sm font-bold text-[#00363a] disabled:opacity-50"

          :disabled="simForm.processing || !canSubmitSimulation"

        >

          {{ simForm.processing ? 'Iniciando…' : 'Iniciar simulación' }}

        </button>

        <p v-if="hasActiveSimulation" class="text-xs text-[#00dbe7]">

          Simulación activa (~{{ estimatedMinutes }} min estimados).

        </p>

      </div>

    </form>



    <section class="overflow-hidden rounded-xl border border-white/8 bg-[#121820]/70">

      <table class="w-full text-sm">

        <thead class="bg-[#191c1f]/80 text-left text-[10px] uppercase text-[#b9cacb]">

          <tr>

            <th class="px-6 py-3">Empresa</th>

            <th class="px-6 py-3">Slug</th>

            <th class="px-6 py-3">Estado</th>

            <th class="px-6 py-3">Módulos</th>

            <th class="px-6 py-3"></th>

          </tr>

        </thead>

        <tbody class="divide-y divide-white/5">

          <tr v-for="t in tenants" :key="t.id" class="hover:bg-white/5">

            <td class="px-6 py-3 font-medium">{{ t.name }}</td>

            <td class="px-6 py-3 font-mono text-xs">{{ t.slug }}</td>

            <td class="px-6 py-3 uppercase text-xs">{{ t.status }}</td>

            <td class="px-6 py-3 font-mono text-xs text-[#b9cacb]">

              {{ t.modules_catalog?.producers_count ?? 0 }}P / {{ t.modules_catalog?.subscribers_count ?? 0 }}S

            </td>

            <td class="px-6 py-3 text-right">

              <Link :href="`/control/companies/${t.id}/modules`" class="mr-3 text-[#00dbe7] hover:underline">Configurar módulos</Link>

              <Link :href="`/control/companies/${t.id}`" class="text-[#b9cacb] hover:text-[#e1fdff]">Gestionar</Link>

            </td>

          </tr>

          <tr v-if="tenants.length === 0">

            <td colspan="5" class="px-6 py-10 text-center text-[#849495]">

              Sin empresas. Cree una en <Link href="/control/provisioning" class="text-[#00dbe7]">Provisioning</Link>.

            </td>

          </tr>

        </tbody>

      </table>

    </section>

  </ControlLayout>

</template>



<script setup>

import ControlLayout from '@/Layouts/ControlLayout.vue';

import { Link, useForm, usePage } from '@inertiajs/vue3';

import { computed, onMounted, onUnmounted, ref, watch } from 'vue';



const props = defineProps({

  tenants: { type: Array, default: () => [] },

  instance_slug: { type: String, default: '' },

  simulation_defaults: {

    type: Object,

    default: () => ({ events_per_minute: 10, duration_minutes: 1 }),

  },

  active_simulation_run_id: { type: String, default: null },

});



const activeRun = ref(null);

let pollTimer = null;



const hasActiveSimulation = computed(() =>

  activeRun.value && isRunActive(activeRun.value.run?.status),

);



const simForm = useForm({

  tenant_id: '',

  events_per_minute: props.simulation_defaults.events_per_minute ?? 10,

  duration_minutes: props.simulation_defaults.duration_minutes ?? 1,

  total_events: 0,

  prepare_first: true,

});



const plannedTotal = computed(() => {

  const rate = Number(simForm.events_per_minute) || 0;

  const mins = Number(simForm.duration_minutes) || 0;

  return rate * mins;

});



watch(plannedTotal, (total) => {

  simForm.total_events = total;

});



const selectedTenant = computed(() =>

  props.tenants.find((t) => t.id === simForm.tenant_id) ?? null,

);



const canSubmitSimulation = computed(

  () => simForm.tenant_id && selectedTenant.value?.can_simulate,

);



const estimatedMinutes = computed(() => Number(simForm.duration_minutes) || 1);



// Pre-select instance tenant when available

const defaultTenant = props.tenants.find((t) => t.slug === props.instance_slug && t.can_simulate)

  ?? props.tenants.find((t) => t.can_simulate);

if (defaultTenant && !simForm.tenant_id) {

  simForm.tenant_id = defaultTenant.id;

}



function runSimulation() {

  simForm.total_events = plannedTotal.value;

  simForm.post('/control/companies/simulation', {

    preserveScroll: true,

    onSuccess: () => {

      const runId = usePage().props.active_simulation_run_id;

      if (runId) beginPolling(runId);

    },

  });

}



function isRunActive(status) {

  return status === 'pending' || status === 'running';

}



async function fetchRunStatus(runId) {

  const res = await fetch(`/control/simulations/${runId}/status`, {

    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },

    credentials: 'same-origin',

  });

  if (!res.ok) return null;

  return res.json();

}



function stopPolling() {

  if (pollTimer) {

    clearInterval(pollTimer);

    pollTimer = null;

  }

}



async function pollOnce(runId) {

  const data = await fetchRunStatus(runId);

  if (!data) return;

  activeRun.value = data;

  const status = data.run?.status;

  if (status === 'completed' || status === 'failed') {

    stopPolling();

  }

}



function beginPolling(runId) {

  stopPolling();

  pollOnce(runId);

  pollTimer = setInterval(() => pollOnce(runId), 2000);

}



onMounted(() => {

  if (props.active_simulation_run_id) {

    beginPolling(props.active_simulation_run_id);

  }

});



watch(

  () => props.active_simulation_run_id,

  (id) => {

    if (id) beginPolling(id);

  },

);



onUnmounted(stopPolling);

</script>



<style scoped>

.input-dark {

  @apply rounded-lg border border-white/10 bg-[#191c1f] px-3 py-2 text-sm text-[#e1e2e7] outline-none focus:border-[#00dbe7]/50;

}

</style>



<template>
  <ControlLayout title="Detalle de reporte" active-nav="incidents">
    <div class="mb-6">
      <Link href="/control/incidents" class="inline-flex items-center gap-1 text-xs text-[#00dbe7] hover:underline">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
        Volver a incidentes
      </Link>
    </div>

    <div v-if="$page.props.flash?.message" class="mb-4 rounded-lg border border-[#00f2ff]/30 bg-[#00f2ff]/10 px-4 py-3 text-sm">
      {{ $page.props.flash.message }}
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <div class="space-y-6 lg:col-span-2">
        <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-6">
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <p class="text-[10px] uppercase text-[#849495]">{{ report.created_at }}</p>
              <h2 class="mt-1 text-xl font-bold text-[#e1fdff]">{{ report.subject }}</h2>
              <p class="mt-1 text-sm text-[#00dbe7]">{{ report.client_label }}</p>
            </div>
            <span class="rounded px-2 py-1 text-[10px] font-bold uppercase" :class="statusClass(report.status)">{{ report.status }}</span>
          </div>
          <p class="mt-4 whitespace-pre-wrap text-sm leading-relaxed text-[#b9cacb]">{{ report.description }}</p>
          <p v-if="report.page_url" class="mt-3 text-[10px] text-[#849495]">Página: {{ report.page_url }}</p>
        </section>

        <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-6">
          <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Log de diagnóstico automático</h3>
          <p class="mt-1 text-[10px] text-[#849495]">Capturado al enviar el reporte (bus, alertas, fallos recientes).</p>
          <pre class="mt-4 max-h-[28rem] overflow-auto rounded-lg bg-black/40 p-4 font-mono text-[11px] leading-relaxed text-[#b9cacb]">{{ formatLog(report.diagnostic_log) }}</pre>
        </section>

        <section v-if="report.has_response" class="rounded-xl border border-[#00f2ff]/25 bg-[#00f2ff]/5 p-6">
          <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Respuesta enviada al cliente</h3>
          <p class="mt-1 text-[10px] text-[#849495]">{{ report.responded_by_name }} · {{ report.responded_at }}</p>
          <p class="mt-3 whitespace-pre-wrap text-sm text-[#e1fdff]">{{ report.admin_response }}</p>
        </section>
      </div>

      <aside class="space-y-4">
        <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-5">
          <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Resumen técnico</h3>
          <dl class="mt-3 space-y-2 text-sm">
            <div><dt class="text-[10px] uppercase text-[#849495]">Bus</dt><dd class="font-mono text-[#e1fdff]">{{ report.diagnostic_summary?.bus_status ?? '—' }}</dd></div>
            <div><dt class="text-[10px] uppercase text-[#849495]">Alertas al capturar</dt><dd class="font-mono">{{ report.diagnostic_summary?.alerts_at_capture ?? 0 }}</dd></div>
            <div><dt class="text-[10px] uppercase text-[#849495]">Fallos capturados</dt><dd class="font-mono">{{ report.diagnostic_summary?.failures_at_capture ?? 0 }}</dd></div>
            <div><dt class="text-[10px] uppercase text-[#849495]">Severidad</dt><dd class="uppercase">{{ report.severity }}</dd></div>
          </dl>
        </section>

        <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-5">
          <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Responder al cliente</h3>
          <p class="mt-1 text-[10px] text-[#849495]">El cliente verá esto en el icono de notificaciones de su portal.</p>
          <form class="mt-4 space-y-3" @submit.prevent="submitResponse">
            <textarea
              v-model="responseForm.admin_response"
              rows="6"
              required
              minlength="5"
              class="input-dark w-full resize-none"
              placeholder="Explicación, pasos a seguir, resolución…"
            />
            <button type="submit" class="btn-primary w-full" :disabled="responseForm.processing">
              {{ responseForm.processing ? 'Enviando…' : 'Enviar respuesta' }}
            </button>
          </form>
        </section>

        <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-5">
          <h3 class="text-xs font-bold uppercase text-[#849495]">Estado</h3>
          <div class="mt-2 flex flex-wrap gap-2">
            <button
              v-for="st in ['open', 'acknowledged', 'resolved']"
              :key="st"
              type="button"
              class="rounded border px-2 py-1 text-[10px] uppercase"
              :class="report.status === st ? 'border-[#00f2ff] text-[#00f2ff]' : 'border-white/10 text-[#849495]'"
              @click="updateStatus(st)"
            >
              {{ st }}
            </button>
          </div>
        </section>
      </aside>
    </div>
  </ControlLayout>
</template>

<script setup>
import ControlLayout from '@/Layouts/ControlLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
  report: { type: Object, required: true },
});

const responseForm = useForm({
  admin_response: props.report.admin_response || '',
});

function formatLog(log) {
  try {
    return JSON.stringify(log, null, 2);
  } catch {
    return String(log ?? '{}');
  }
}

function statusClass(status) {
  if (status === 'resolved') return 'text-green-400 bg-green-400/10';
  if (status === 'acknowledged') return 'text-amber-400 bg-amber-400/10';
  return 'text-[#00dbe7] bg-[#00dbe7]/10';
}

function submitResponse() {
  responseForm.post(`/control/incidents/reports/${props.report.id}/respond`, { preserveScroll: true });
}

function updateStatus(status) {
  router.patch(`/control/incidents/reports/${props.report.id}`, { status }, { preserveScroll: true });
}
</script>

<style scoped>
.input-dark {
  @apply rounded-lg border border-white/10 bg-[#191c1f] px-3 py-2 text-sm text-[#e1e2e7];
}
.btn-primary {
  @apply rounded-lg bg-[#00f2ff] py-2.5 text-sm font-bold text-[#00363a] disabled:opacity-60;
}
</style>

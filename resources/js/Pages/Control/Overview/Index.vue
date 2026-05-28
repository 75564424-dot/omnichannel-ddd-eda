<template>
  <ControlLayout title="Global Overview" active-nav="overview">
    <div class="mb-8">
      <h2 class="text-2xl font-bold tracking-tight text-[#e1fdff]">Resumen global</h2>
      <p class="mt-1 text-sm text-[#b9cacb]">Métricas reales de esta instancia de control (sin datos simulados).</p>
    </div>

    <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-4">
      <div class="rounded-xl border border-white/8 bg-[#121820]/70 p-5">
        <p class="text-[10px] uppercase tracking-widest text-[#b9cacb]">Empresas registradas</p>
        <p class="mt-2 font-mono text-3xl text-[#00f2ff]">{{ tenants.length }}</p>
      </div>
      <div class="rounded-xl border border-white/8 bg-[#121820]/70 p-5">
        <p class="text-[10px] uppercase tracking-widest text-[#b9cacb]">Estado del bus</p>
        <p class="mt-2 font-mono text-xl text-[#e1fdff]">{{ middleware.bus_status }}</p>
      </div>
      <div class="rounded-xl border border-white/8 bg-[#121820]/70 p-5">
        <p class="text-[10px] uppercase tracking-widest text-[#b9cacb]">Cola (depth)</p>
        <p class="mt-2 font-mono text-3xl text-[#e1fdff]">{{ middleware.queue_depth }}</p>
      </div>
      <div class="rounded-xl border border-white/8 bg-[#121820]/70 p-5">
        <p class="text-[10px] uppercase tracking-widest text-[#b9cacb]">Alertas activas</p>
        <p class="mt-2 font-mono text-3xl" :class="alerts_count > 0 ? 'text-[#ffb4ab]' : 'text-[#00f2ff]'">{{ alerts_count }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-6">
        <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Empresas</h3>
        <ul class="mt-4 space-y-2">
          <li v-for="t in tenants" :key="t.id">
            <Link :href="`/control/companies/${t.id}`" class="flex justify-between rounded-lg border border-white/5 px-4 py-3 hover:bg-white/5">
              <span>{{ t.name }}</span>
              <span class="text-xs uppercase text-[#849495]">{{ t.status }}</span>
            </Link>
          </li>
          <li v-if="tenants.length === 0" class="text-sm text-[#849495]">No hay tenants en la base de datos.</li>
        </ul>
      </section>

      <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-6">
        <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Alertas recientes</h3>
        <ul class="mt-4 space-y-3">
          <li v-for="a in alerts" :key="a.name" class="rounded-lg border border-[#ffb4ab]/20 bg-[#ffb4ab]/5 px-4 py-3 text-sm">
            <span class="font-bold text-[#ffb4ab]">{{ a.severity }}</span> — {{ a.message }}
          </li>
          <li v-if="alerts.length === 0" class="text-sm text-[#849495]">Sin alertas por encima de umbral.</li>
        </ul>
      </section>
    </div>
  </ControlLayout>
</template>

<script setup>
import ControlLayout from '@/Layouts/ControlLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
  tenants: { type: Array, default: () => [] },
  middleware: { type: Object, default: () => ({}) },
  alerts_count: { type: Number, default: 0 },
  alerts: { type: Array, default: () => [] },
});
</script>

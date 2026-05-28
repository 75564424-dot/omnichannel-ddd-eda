<template>
  <ControlLayout title="Middleware global" active-nav="middleware">
    <div class="mb-8">
      <h2 class="text-2xl font-bold text-[#e1fdff]">Middleware global</h2>
      <p class="mt-1 text-sm text-[#b9cacb]">Broker, colas, latencia, nodos y dead letters — datos del bus en esta instancia.</p>
    </div>

    <div class="mb-8 grid grid-cols-2 gap-4 md:grid-cols-4">
      <div class="rounded-xl border border-white/8 bg-[#121820]/70 p-4">
        <p class="text-[10px] uppercase text-[#b9cacb]">Broker</p>
        <p class="mt-1 font-mono text-lg">{{ snapshot.broker.driver }}</p>
        <p class="text-xs text-[#849495]">{{ snapshot.broker.status }}</p>
      </div>
      <div class="rounded-xl border border-white/8 bg-[#121820]/70 p-4">
        <p class="text-[10px] uppercase text-[#b9cacb]">Latencia</p>
        <p class="mt-1 font-mono text-2xl">{{ snapshot.metrics.latency_ms }} ms</p>
      </div>
      <div class="rounded-xl border border-white/8 bg-[#121820]/70 p-4">
        <p class="text-[10px] uppercase text-[#b9cacb]">Throughput</p>
        <p class="mt-1 font-mono text-2xl">{{ snapshot.metrics.events_per_second }} evt/s</p>
      </div>
      <div class="rounded-xl border border-white/8 bg-[#121820]/70 p-4">
        <p class="text-[10px] uppercase text-[#b9cacb]">Dead letters</p>
        <p class="mt-1 font-mono text-2xl text-[#ffb4ab]">{{ snapshot.metrics.dead_letters }}</p>
      </div>
    </div>

    <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
      <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-6">
        <h3 class="text-xs font-bold uppercase text-[#00dbe7]">Cola ({{ snapshot.queues.connection }})</h3>
        <p class="mt-2 text-sm">Profundidad total: <strong>{{ snapshot.queues.depth }}</strong></p>
        <ul class="mt-4 max-h-64 space-y-2 overflow-y-auto text-xs font-mono">
          <li v-for="e in snapshot.queues.recent" :key="e.id" class="rounded border border-white/5 px-2 py-1">
            {{ e.event_type }} · {{ e.status }}
          </li>
          <li v-if="!snapshot.queues.recent?.length" class="text-[#849495]">Cola vacía.</li>
        </ul>
      </section>

      <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-6">
        <h3 class="text-xs font-bold uppercase text-[#00dbe7]">Nodos / clusters</h3>
        <ul class="mt-4 space-y-2">
          <li v-for="(status, node) in snapshot.nodes" :key="node" class="flex justify-between text-sm">
            <span>{{ node }}</span>
            <span class="uppercase">{{ status }}</span>
          </li>
        </ul>
        <p class="mt-2 text-[10px] text-[#849495]">{{ snapshot.nodes_updated_at }}</p>
      </section>
    </div>

    <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-6">
      <h3 class="text-xs font-bold uppercase text-[#00dbe7]">Dead letter queue</h3>
      <p v-if="!snapshot.dead_letters?.length" class="mt-2 text-sm text-[#849495]">Sin entradas en DLQ.</p>
      <ul v-else class="mt-4 space-y-2 text-sm">
        <li v-for="(dl, i) in snapshot.dead_letters" :key="i" class="rounded border border-[#ffb4ab]/20 px-3 py-2">
          {{ dl.event_type || dl.reason || 'DLQ entry' }}
        </li>
      </ul>
    </section>
  </ControlLayout>
</template>

<script setup>
import ControlLayout from '@/Layouts/ControlLayout.vue';

defineProps({
  snapshot: { type: Object, required: true },
});
</script>

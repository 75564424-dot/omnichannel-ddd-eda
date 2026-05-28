<template>
  <ControlLayout title="Infraestructura" active-nav="infrastructure">
    <div class="mb-8">
      <h2 class="text-2xl font-bold text-[#e1fdff]">Infraestructura</h2>
      <p class="mt-1 text-sm text-[#b9cacb]">
        Modo: {{ snapshot.deployment.mode }} · Cliente: {{ snapshot.deployment.client_slug }} · {{ snapshot.deployment.app_env }}
      </p>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      <div
        v-for="c in snapshot.components"
        :key="c.label"
        class="flex items-start justify-between rounded-xl border border-white/8 bg-[#121820]/70 p-5"
      >
        <div class="flex items-center gap-3">
          <span class="material-symbols-outlined text-[#00dbe7]">{{ c.icon }}</span>
          <div>
            <p class="font-medium">{{ c.label }}</p>
            <p v-if="c.note" class="text-xs text-[#849495]">{{ c.note }}</p>
          </div>
        </div>
        <span :class="statusBadge(c.status)" class="rounded px-2 py-0.5 text-[10px] font-bold uppercase">{{ c.status }}</span>
      </div>
    </div>
  </ControlLayout>
</template>

<script setup>
import ControlLayout from '@/Layouts/ControlLayout.vue';

defineProps({
  snapshot: { type: Object, required: true },
});

function statusBadge(status) {
  if (status === 'ok' || status === 'detected' || status === 'configured') return 'bg-[#00f2ff]/15 text-[#00f2ff]';
  if (status === 'not_configured' || status === 'local' || status === 'docker_compose') return 'bg-white/10 text-[#b9cacb]';
  return 'bg-[#ffb4ab]/15 text-[#ffb4ab]';
}
</script>

<template>
  <div class="col-span-12 lg:col-span-9 bg-white border border-slate-200 shadow-sm rounded-sm overflow-hidden">
    <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
      <span class="text-xl font-semibold text-primary flex items-center gap-2">
        <span class="material-symbols-outlined">account_tree</span>
        Event flow
      </span>
      <span class="px-2 py-1 bg-white border border-slate-200 text-[10px] font-bold rounded-sm">TOPOLOGY</span>
    </div>
    <div class="p-12 min-h-[280px] flex items-center justify-center bg-[radial-gradient(#e2e8f0_1px,transparent_1px)] [background-size:20px_20px]">
      <div class="flex items-center w-full max-w-4xl justify-between relative">
        <!-- Producers -->
        <div
          class="flex flex-col items-center gap-4 z-10 rounded-xl outline-none transition-opacity cursor-pointer select-none hover:opacity-95 focus-visible:ring-2 focus-visible:ring-primary/40"
          :class="{ 'opacity-45': availableProducers.length === 0 }"
          role="button"
          tabindex="0"
          :aria-expanded="panelOpen && panelKind === 'producers'"
          aria-label="Ver módulos productores configurados"
          @click="openProducers"
          @keydown.enter.prevent="openProducers"
          @keydown.space.prevent="openProducers"
        >
          <div class="w-20 h-20 bg-white border-2 border-primary flex items-center justify-center rounded-xl shadow-lg">
            <span class="material-symbols-outlined text-primary text-3xl">upload</span>
          </div>
          <div class="text-center pointer-events-none">
            <p class="text-sm font-semibold">Producers</p>
            <p class="text-[10px] text-emerald-600 font-bold uppercase tracking-widest">Ingress</p>
            <p class="text-[10px] text-slate-400 mt-1">Clic para detalle</p>
          </div>
        </div>

        <div class="flex-1 h-px bg-slate-200 relative mx-4 overflow-hidden pointer-events-none">
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/30 to-transparent w-full h-full animate-[shimmer_2s_infinite]" />
        </div>

        <!-- Middleware -->
        <div
          class="flex flex-col items-center gap-4 z-10 rounded-full outline-none cursor-pointer select-none hover:scale-[1.02] transition-transform focus-visible:ring-2 focus-visible:ring-white/80"
          role="button"
          tabindex="0"
          :aria-expanded="panelOpen && panelKind === 'middleware'"
          aria-label="Ver información del middleware"
          @click="openMiddleware"
          @keydown.enter.prevent="openMiddleware"
          @keydown.space.prevent="openMiddleware"
        >
          <div class="w-24 h-24 bg-primary flex items-center justify-center rounded-full shadow-2xl relative">
            <span class="material-symbols-outlined text-white text-4xl">hub</span>
            <div class="absolute inset-0 rounded-full border-4 border-white opacity-20 scale-110" />
          </div>
          <div class="text-center pointer-events-none">
            <p class="text-sm font-semibold text-slate-900">Middleware bus</p>
            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">FIFO / routing</p>
            <p class="text-[10px] text-slate-600 mt-1">Clic para detalle</p>
          </div>
        </div>

        <div class="flex-1 h-px bg-slate-200 relative mx-4 overflow-hidden pointer-events-none">
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-slate-400/30 to-transparent w-full h-full animate-[shimmer_3s_infinite_reverse]" />
        </div>

        <!-- Subscribers -->
        <div
          class="flex flex-col items-center gap-4 z-10 rounded-xl outline-none transition-opacity cursor-pointer select-none hover:opacity-95 focus-visible:ring-2 focus-visible:ring-primary/40"
          :class="{ 'opacity-45': availableSubscribers.length === 0 }"
          role="button"
          tabindex="0"
          :aria-expanded="panelOpen && panelKind === 'subscribers'"
          aria-label="Ver módulos suscriptores configurados"
          @click="openSubscribers"
          @keydown.enter.prevent="openSubscribers"
          @keydown.space.prevent="openSubscribers"
        >
          <div class="w-20 h-20 bg-white border-2 border-slate-200 flex items-center justify-center rounded-xl shadow-lg">
            <span class="material-symbols-outlined text-slate-400 text-3xl">download</span>
          </div>
          <div class="text-center pointer-events-none">
            <p class="text-sm font-semibold">Subscribers</p>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Packs / services</p>
            <p class="text-[10px] text-slate-400 mt-1">Clic para detalle</p>
          </div>
        </div>
      </div>
    </div>

    <ModulesTopologyPanel
      :open="panelOpen"
      :kind="panelKind"
      :producers="producers"
      :subscribers="subscribers"
      :available-producers="availableProducers"
      :available-subscribers="availableSubscribers"
      :visible-producer-ids="visibleProducerIds"
      :visible-subscriber-ids="visibleSubscriberIds"
      :middleware="middlewareMeta"
      :contact-message="serviceMessage"
      @close="closePanel"
      @visibility-updated="onVisibilityUpdated"
    />
  </div>
</template>

<script>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import ModulesTopologyPanel from '@/Components/Dashboard/ModulesTopologyPanel.vue';
import { normalizeModulesCatalogPayload, fetchModulesCatalog } from '@/services/modulesCatalog';

/** @typedef {{ producers?: unknown[], subscribers?: unknown[], middleware?: Record<string, unknown>, service_contact_message?: string }} Catalog */

export default {
  name: 'EventFlowTopology',
  components: { ModulesTopologyPanel },
  props: {
    modulesCatalog: { type: Object, default: () => ({}) },
  },
  setup(props) {
    const panelOpen = ref(false);
    const panelKind = ref('producers');

    const catalog = ref(normalizeModulesCatalogPayload(props.modulesCatalog));

    watch(
      () => props.modulesCatalog,
      (v) => {
        catalog.value = normalizeModulesCatalogPayload(v);
      },
      { deep: true },
    );

    const producers = computed(() => catalog.value.producers);
    const subscribers = computed(() => catalog.value.subscribers);
    const availableProducers = computed(() => catalog.value.available_producers);
    const availableSubscribers = computed(() => catalog.value.available_subscribers);
    const visibleProducerIds = computed(() => catalog.value.visible_producer_ids);
    const visibleSubscriberIds = computed(() => catalog.value.visible_subscriber_ids);
    const middlewareMeta = computed(() => catalog.value.middleware);
    const serviceMessage = computed(() => catalog.value.service_contact_message);

    function onVisibilityUpdated(nextCatalog) {
      catalog.value = nextCatalog;
    }

    function openProducers() {
      panelKind.value = 'producers';
      panelOpen.value = true;
    }
    function openSubscribers() {
      panelKind.value = 'subscribers';
      panelOpen.value = true;
    }
    function openMiddleware() {
      panelKind.value = 'middleware';
      panelOpen.value = true;
    }
    function closePanel() {
      panelOpen.value = false;
    }

    let hydrated = false;
    function onGlobalEscape(e) {
      if (e.key === 'Escape') closePanel();
    }

    onMounted(async () => {
      window.addEventListener('keydown', onGlobalEscape);

      if (hydrated) return;
      const c = catalog.value;
      const hasModules = c.available_producers.length > 0 || c.available_subscribers.length > 0
        || c.producers.length > 0 || c.subscribers.length > 0;
      if (!hasModules && typeof window !== 'undefined' && window.axios) {
        try {
          catalog.value = await fetchModulesCatalog(window.axios);
        } catch {
          /* usar props SSR */
        }
      }
      hydrated = true;
    });

    onUnmounted(() => {
      window.removeEventListener('keydown', onGlobalEscape);
    });

    return {
      panelOpen,
      panelKind,
      producers,
      subscribers,
      availableProducers,
      availableSubscribers,
      visibleProducerIds,
      visibleSubscriberIds,
      middlewareMeta,
      serviceMessage,
      onVisibilityUpdated,
      openProducers,
      openSubscribers,
      openMiddleware,
      closePanel,
    };
  },
};
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-[56] bg-slate-900/35 backdrop-blur-[2px]"
      role="presentation"
      aria-hidden="true"
      @click.self="$emit('close')"
    />

    <aside
      v-if="open"
      :id="panelId"
      class="fixed top-0 right-0 z-[61] flex h-screen w-full max-w-md flex-col border-l border-slate-200 bg-white shadow-2xl"
      role="dialog"
      aria-modal="true"
      :aria-labelledby="titleId"
    >
      <header class="flex items-start justify-between border-b border-slate-100 bg-slate-50/90 px-5 py-4">
        <div>
          <h2 :id="titleId" class="text-sm font-bold uppercase tracking-wider text-primary">{{ title }}</h2>
          <p class="mt-1 text-[13px] text-slate-600">{{ subtitle }}</p>
        </div>
        <button
          type="button"
          class="rounded-full p-2 text-slate-500 hover:bg-white hover:text-slate-900"
          aria-label="Cerrar panel"
          @click="$emit('close')"
        >
          <span class="material-symbols-outlined text-[22px]">close</span>
        </button>
      </header>

      <div class="flex-1 overflow-y-auto px-5 py-4">
        <template v-if="kind === 'middleware'">
          <p class="text-lg font-semibold text-slate-900">{{ middleware.name }}</p>
          <p class="mt-2 text-xs font-mono text-slate-500">id: {{ middleware.id }}</p>
          <p v-if="middleware.role" class="mt-1 text-[11px] uppercase tracking-wide text-slate-400">rol: {{ middleware.role }}</p>
          <p class="mt-4 text-sm text-slate-600 leading-relaxed">{{ middleware.description || '—' }}</p>
          <p class="mt-6 text-[11px] text-slate-500 leading-snug border-t border-slate-100 pt-4">
            La configuración operativa del bus no se edita desde este panel; es responsabilidad de la plataforma.
          </p>
        </template>

        <template v-else>
          <!-- Selector de visibilidad (módulos asignados por SaaS) -->
          <div v-if="pickerOpen" class="flex flex-col gap-4">
            <p class="text-sm text-slate-700 leading-relaxed">
              Marque los módulos que su proveedor configuró para su empresa y que desea mostrar en este panel.
            </p>

            <p v-if="!availableList.length" class="rounded-sm border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
              Aún no hay módulos asignados desde el panel de control SaaS. Contacte a su proveedor.
            </p>

            <ul v-else class="flex flex-col gap-2">
              <li
                v-for="item in availableList"
                :key="item.id"
                class="flex items-start gap-3 rounded-sm border border-slate-200 bg-white p-3"
              >
                <input
                  :id="`mod-vis-${kind}-${item.id}`"
                  v-model="pickerSelection"
                  type="checkbox"
                  class="mt-1 h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary/30"
                  :value="item.id"
                />
                <label :for="`mod-vis-${kind}-${item.id}`" class="min-w-0 flex-1 cursor-pointer">
                  <p class="text-sm font-semibold text-slate-900">{{ item.name }}</p>
                  <p class="text-[11px] font-mono text-slate-500">{{ item.id }}</p>
                  <div v-if="tags(item).length" class="mt-2 flex flex-wrap gap-1">
                    <span
                      v-for="t in tags(item)"
                      :key="item.id + t"
                      class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-medium"
                    >
                      {{ t }}
                    </span>
                  </div>
                </label>
              </li>
            </ul>

            <p v-if="saveError" class="text-xs text-red-600">{{ saveError }}</p>

            <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-4">
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-sm bg-primary px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
                :disabled="saving || !availableList.length"
                @click="savePicker"
              >
                {{ saving ? 'Guardando…' : 'Guardar selección' }}
              </button>
              <button
                type="button"
                class="rounded-sm border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50"
                :disabled="saving"
                @click="closePicker"
              >
                Cancelar
              </button>
            </div>
          </div>

          <!-- Listado visible -->
          <template v-else>
            <div
              v-if="!list.length"
              class="flex flex-col items-center justify-center rounded-sm border border-dashed border-slate-200 bg-slate-50/50 px-6 py-10 text-center"
            >
              <span class="material-symbols-outlined text-slate-300 text-[56px] font-light" aria-hidden="true">post_add</span>
              <p class="mt-4 text-sm font-medium text-slate-800">Ningún módulo visible</p>
              <p class="mt-2 text-xs text-slate-500 max-w-xs leading-relaxed">
                {{ emptyHint }}
              </p>
              <button
                v-if="availableList.length"
                type="button"
                class="mt-6 inline-flex items-center gap-2 rounded-sm bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-95"
                @click="openPicker"
              >
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">add</span>
                Elegir módulos
              </button>
            </div>

            <template v-else>
              <div class="mb-3 flex justify-end">
                <button
                  type="button"
                  class="text-xs font-semibold text-primary hover:underline"
                  @click="openPicker"
                >
                  Gestionar visibilidad
                </button>
              </div>
              <ul class="flex flex-col gap-3">
                <li
                  v-for="item in list"
                  :key="item.id"
                  class="rounded-sm border border-slate-200 bg-white p-4 shadow-sm"
                >
                  <div class="flex items-start justify-between gap-2">
                    <div>
                      <p class="text-sm font-semibold text-slate-900">{{ item.name }}</p>
                      <p class="text-[11px] font-mono text-slate-500 mt-0.5">{{ item.id }}</p>
                    </div>
                  </div>
                  <div v-if="tags(item).length" class="mt-3 flex flex-wrap gap-1.5">
                    <span
                      v-for="t in tags(item)"
                      :key="item.id + t"
                      class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-medium"
                    >
                      {{ t }}
                    </span>
                  </div>
                </li>
              </ul>
            </template>

            <p class="mt-5 text-[11px] text-slate-500 leading-snug">
              {{ footerNote }}
            </p>
          </template>
        </template>
      </div>
    </aside>
  </Teleport>
</template>

<script>
import { saveModulesVisibility } from '@/services/modulesCatalog';

export default {
  name: 'ModulesTopologyPanel',
  props: {
    open: { type: Boolean, default: false },
    kind: { type: String, default: 'producers' },
    producers: { type: Array, default: () => [] },
    subscribers: { type: Array, default: () => [] },
    availableProducers: { type: Array, default: () => [] },
    availableSubscribers: { type: Array, default: () => [] },
    visibleProducerIds: { type: Array, default: () => [] },
    visibleSubscriberIds: { type: Array, default: () => [] },
    middleware: { type: Object, default: () => ({}) },
    contactMessage: { type: String, default: '' },
  },
  emits: ['close', 'visibility-updated'],
  data() {
    return {
      pickerOpen: false,
      pickerSelection: [],
      saving: false,
      saveError: '',
    };
  },
  computed: {
    panelId() {
      return 'modules-topology-panel';
    },
    titleId() {
      return 'modules-topology-panel-title';
    },
    title() {
      if (this.kind === 'middleware') return 'Middleware';
      if (this.kind === 'subscribers') return 'Subscribers';
      return 'Producers';
    },
    subtitle() {
      if (this.kind === 'middleware') return 'Núcleo de enrutamiento y observabilidad';
      if (this.kind === 'subscribers') return 'Módulos visibles como consumidores';
      return 'Módulos visibles como productores';
    },
    list() {
      if (this.kind === 'subscribers') return this.subscribers;
      if (this.kind === 'producers') return this.producers;
      return [];
    },
    availableList() {
      if (this.kind === 'subscribers') return this.availableSubscribers;
      if (this.kind === 'producers') return this.availableProducers;
      return [];
    },
    emptyHint() {
      if (this.availableList.length) {
        return 'Pulse «Elegir módulos» para seleccionar cuáles de los asignados por su proveedor desea mostrar aquí.';
      }
      return this.contactMessage || 'Su proveedor aún no ha asignado módulos a esta instancia.';
    },
    footerNote() {
      if (this.availableList.length) {
        return 'Solo puede mostrar u ocultar módulos ya configurados por su proveedor SaaS. No puede crear integraciones nuevas desde aquí.';
      }
      return 'Los módulos los define su proveedor desde el panel de control.';
    },
  },
  watch: {
    open(val) {
      if (val) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = '';
        this.closePicker();
      }
    },
    kind() {
      this.closePicker();
    },
  },
  unmounted() {
    document.body.style.overflow = '';
  },
  methods: {
    openPicker() {
      this.saveError = '';
      this.pickerSelection = this.kind === 'subscribers'
        ? [...this.visibleSubscriberIds]
        : [...this.visibleProducerIds];
      this.pickerOpen = true;
    },
    closePicker() {
      this.pickerOpen = false;
      this.pickerSelection = [];
      this.saveError = '';
    },
    async savePicker() {
      if (!window.axios) {
        this.saveError = 'No se pudo guardar. Recargue la página.';
        return;
      }
      this.saving = true;
      this.saveError = '';
      try {
        const payload = this.kind === 'subscribers'
          ? {
              producers: [...this.visibleProducerIds],
              subscribers: [...this.pickerSelection],
            }
          : {
              producers: [...this.pickerSelection],
              subscribers: [...this.visibleSubscriberIds],
            };
        const catalog = await saveModulesVisibility(window.axios, payload);
        this.$emit('visibility-updated', catalog);
        this.closePicker();
      } catch (err) {
        this.saveError = err.response?.data?.message || 'No se pudo guardar la selección.';
      } finally {
        this.saving = false;
      }
    },
    tags(item) {
      if (this.kind === 'subscribers') {
        const c = item.event_types_consumed;
        return Array.isArray(c) ? c : [];
      }
      const e = item.event_types_emitted;
      return Array.isArray(e) ? e : [];
    },
  },
};
</script>

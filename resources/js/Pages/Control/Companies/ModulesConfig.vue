<template>
  <ControlLayout :title="`Módulos — ${tenant.name}`" active-nav="companies">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <Link href="/control/companies" class="inline-flex items-center gap-1 text-xs text-[#00dbe7] hover:underline">
          <span class="material-symbols-outlined text-[16px]">arrow_back</span>
          Empresas
        </Link>
        <p class="mt-2 font-mono text-xs text-[#849495]">{{ tenant.slug }}</p>
        <h2 class="mt-1 text-xl font-bold text-[#e1fdff]">Catálogo técnico de módulos</h2>
        <p class="mt-1 text-sm text-[#b9cacb]">
          Productores (emiten eventos) y suscriptores (reciben). Equivalente a <span class="font-mono text-[#00dbe7]">modules_config.json</span>.
        </p>
      </div>
      <div class="flex flex-wrap gap-2">
        <button type="button" class="btn-ghost" @click="manualOpen = true">
          <span class="material-symbols-outlined text-[18px]">menu_book</span>
          Manual
        </button>
        <form v-if="can_apply_to_instance" method="post" :action="applyUrl" @submit.prevent="applyToInstance">
          <button type="submit" class="btn-ghost" :disabled="applyForm.processing">
            Aplicar en esta instancia
          </button>
        </form>
      </div>
    </div>

    <div v-if="$page.props.flash?.message" class="mb-4 rounded-lg border border-[#00f2ff]/30 bg-[#00f2ff]/10 px-4 py-3 text-sm">
      {{ $page.props.flash.message }}
    </div>
    <div v-if="form.errors.catalog" class="mb-4 rounded-lg border border-[#ffb4ab]/40 bg-[#ffb4ab]/10 px-4 py-3 text-sm text-[#ffb4ab]">
      {{ form.errors.catalog }}
    </div>
    <div v-else-if="validationErrors.length" class="mb-4 rounded-lg border border-[#ffb4ab]/40 bg-[#ffb4ab]/10 px-4 py-3 text-sm text-[#ffb4ab]">
      <ul class="list-inside list-disc space-y-1">
        <li v-for="(message, idx) in validationErrors" :key="idx">{{ message }}</li>
      </ul>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-4">
      <div class="rounded-xl border border-white/8 bg-[#121820]/70 p-4 text-center">
        <p class="text-[10px] uppercase text-[#849495]">Productores</p>
        <p class="mt-1 font-mono text-2xl text-[#00f2ff]">{{ form.catalog.producers.length }} / {{ form.limits.producers_max }}</p>
      </div>
      <div class="rounded-xl border border-white/8 bg-[#121820]/70 p-4 text-center">
        <p class="text-[10px] uppercase text-[#849495]">Suscriptores</p>
        <p class="mt-1 font-mono text-2xl text-[#00f2ff]">{{ form.catalog.subscribers.length }} / {{ form.limits.subscribers_max }}</p>
      </div>
      <div class="rounded-xl border border-white/8 bg-[#121820]/70 p-4 md:col-span-2">
        <p class="text-[10px] uppercase text-[#849495]">Instancia local</p>
        <p class="mt-1 text-xs text-[#b9cacb]">
          <span v-if="can_apply_to_instance" class="text-[#00f2ff]">Puede aplicar catálogo a esta instancia ({{ instance_slug }})</span>
          <span v-else>Solo registro SaaS — instancia desplegada: {{ instance_slug || '—' }}</span>
        </p>
      </div>
    </div>

    <form class="space-y-6" @submit.prevent="save">
      <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-5">
        <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Límites del plan</h3>
        <div class="mt-4 grid grid-cols-2 gap-4 md:max-w-md">
          <label class="block">
            <span class="label">Máx. productores</span>
            <input v-model.number="form.limits.producers_max" type="number" min="0" max="50" class="input-dark w-full" />
          </label>
          <label class="block">
            <span class="label">Máx. suscriptores</span>
            <input v-model.number="form.limits.subscribers_max" type="number" min="0" max="50" class="input-dark w-full" />
          </label>
        </div>
      </section>

      <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-5">
        <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Middleware (núcleo)</h3>
        <div class="mt-4 grid gap-3 md:grid-cols-2">
          <input v-model="form.catalog.middleware.name" class="input-dark" placeholder="Nombre" />
          <input v-model="form.catalog.service_contact_message" class="input-dark md:col-span-2" placeholder="Mensaje de contacto en catálogo" />
          <textarea v-model="form.catalog.middleware.description" rows="2" class="input-dark md:col-span-2 resize-none" placeholder="Descripción" />
        </div>
      </section>

      <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-5">
        <div class="flex items-center justify-between">
          <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Productores (crean eventos)</h3>
          <button type="button" class="btn-ghost text-xs" :disabled="!canAddProducer" @click="addProducer">+ Agregar</button>
        </div>
        <p class="mt-1 text-[10px] text-[#849495]">Tipos de evento separados por coma. Canales opcionales (POS, WEB, …).</p>
        <div v-if="form.catalog.producers.length === 0" class="mt-4 text-sm text-[#849495]">Sin productores configurados.</div>
        <div v-for="(p, idx) in form.catalog.producers" :key="idx" class="mt-4 rounded-lg border border-white/10 bg-[#191c1f]/60 p-4">
          <div class="mb-2 flex justify-end">
            <button type="button" class="text-[10px] text-[#ffb4ab] hover:underline" @click="removeProducer(idx)">Eliminar</button>
          </div>
          <div class="grid gap-3 md:grid-cols-2">
            <input v-model="p.id" required class="input-dark font-mono text-xs" placeholder="id (slug)" />
            <input v-model="p.name" required class="input-dark" placeholder="Nombre visible" />
            <input v-model="p.event_types_emitted" class="input-dark md:col-span-2" placeholder="Event types emitidos, ej. Order.Created, Stock.Updated" />
            <input v-model="p.channels" class="input-dark md:col-span-2" placeholder="Canales: POS, WEB" />
          </div>
        </div>
      </section>

      <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-5">
        <div class="flex items-center justify-between">
          <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Suscriptores (reciben eventos)</h3>
          <button type="button" class="btn-ghost text-xs" :disabled="!canAddSubscriber" @click="addSubscriber">+ Agregar</button>
        </div>
        <div v-if="form.catalog.subscribers.length === 0" class="mt-4 text-sm text-[#849495]">Sin suscriptores configurados.</div>
        <div v-for="(s, idx) in form.catalog.subscribers" :key="idx" class="mt-4 rounded-lg border border-white/10 bg-[#191c1f]/60 p-4">
          <div class="mb-2 flex justify-end">
            <button type="button" class="text-[10px] text-[#ffb4ab] hover:underline" @click="removeSubscriber(idx)">Eliminar</button>
          </div>
          <div class="grid gap-3 md:grid-cols-2">
            <input v-model="s.id" required class="input-dark font-mono text-xs" placeholder="id" />
            <input v-model="s.name" required class="input-dark" placeholder="Nombre" />
            <input v-model="s.event_types_consumed" class="input-dark md:col-span-2" placeholder="Event types consumidos" />
          </div>
        </div>
      </section>

      <details class="rounded-xl border border-white/8 bg-[#121820]/70 p-5">
        <summary class="cursor-pointer text-xs font-bold uppercase text-[#00dbe7]">Vista JSON (preview)</summary>
        <pre class="mt-3 max-h-64 overflow-auto rounded bg-black/40 p-3 font-mono text-[10px] text-[#b9cacb]">{{ jsonPreview }}</pre>
      </details>

      <button type="submit" class="btn-primary w-full py-3" :disabled="form.processing">
        {{ form.processing ? 'Guardando…' : 'Guardar catálogo de módulos' }}
      </button>
    </form>

  </ControlLayout>

  <Teleport to="body">
    <div v-if="manualOpen" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/50 p-4" @click.self="manualOpen = false">
      <div class="max-h-[85vh] w-full max-w-lg overflow-y-auto rounded-xl border border-white/10 bg-[#121820] p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-4">
          <h3 class="text-lg font-bold text-[#e1fdff]">{{ manual.title || 'Manual' }}</h3>
          <button type="button" class="text-[#849495] hover:text-white" @click="manualOpen = false">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <article v-for="sec in manual.sections || []" :key="sec.id" class="mt-5 border-t border-white/10 pt-4">
          <h4 class="text-sm font-semibold text-[#00dbe7]">{{ sec.title }}</h4>
          <p class="mt-2 text-sm leading-relaxed text-[#b9cacb]">{{ sec.body }}</p>
        </article>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import ControlLayout from '@/Layouts/ControlLayout.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
  tenant: { type: Object, required: true },
  limits: { type: Object, required: true },
  catalog: { type: Object, required: true },
  can_apply_to_instance: { type: Boolean, default: false },
  instance_slug: { type: String, default: '' },
  manual: { type: Object, default: () => ({}) },
});

const page = usePage();
const manualOpen = ref(false);
const applyUrl = `/control/companies/${props.tenant.id}/modules-catalog/apply`;

function listToCsv(arr) {
  return Array.isArray(arr) ? arr.join(', ') : '';
}

function buildFormCatalog(catalog) {
  return {
    service_contact_message: catalog.service_contact_message || '',
    middleware: { ...(catalog.middleware || {}) },
    producers: (catalog.producers || []).map((p) => ({
      id: p.id || '',
      name: p.name || '',
      event_types_emitted: typeof p.event_types_emitted === 'string' ? p.event_types_emitted : listToCsv(p.event_types_emitted),
      channels: typeof p.channels === 'string' ? p.channels : listToCsv(p.channels),
    })),
    subscribers: (catalog.subscribers || []).map((s) => ({
      id: s.id || '',
      name: s.name || '',
      event_types_consumed: typeof s.event_types_consumed === 'string' ? s.event_types_consumed : listToCsv(s.event_types_consumed),
    })),
  };
}

const form = useForm({
  limits: { ...props.limits },
  catalog: buildFormCatalog(props.catalog),
});

const applyForm = useForm({});

const canAddProducer = computed(() => form.catalog.producers.length < form.limits.producers_max);
const canAddSubscriber = computed(() => form.catalog.subscribers.length < form.limits.subscribers_max);

const jsonPreview = computed(() => {
  try {
    return JSON.stringify(form.catalog, null, 2);
  } catch {
    return '{}';
  }
});

const validationErrors = computed(() => {
  const errors = form.errors ?? {};
  return Object.values(errors).filter((message) => typeof message === 'string' && message !== '');
});

function syncFormFromServerProps() {
  const limits = page.props.limits ?? props.limits;
  const catalog = page.props.catalog ?? props.catalog;
  form.defaults({
    limits: { ...limits },
    catalog: buildFormCatalog(catalog),
  });
  form.reset();
}

function addProducer() {
  if (!canAddProducer.value) return;
  form.catalog.producers.push({ id: '', name: '', event_types_emitted: '', channels: '' });
}

function removeProducer(idx) {
  form.catalog.producers.splice(idx, 1);
}

function addSubscriber() {
  if (!canAddSubscriber.value) return;
  form.catalog.subscribers.push({ id: '', name: '', event_types_consumed: '' });
}

function removeSubscriber(idx) {
  form.catalog.subscribers.splice(idx, 1);
}

function save() {
  form.patch(`/control/companies/${props.tenant.id}/modules-catalog`, {
    preserveScroll: true,
    onSuccess: () => syncFormFromServerProps(),
  });
}

function applyToInstance() {
  applyForm.post(applyUrl, { preserveScroll: true });
}
</script>

<style scoped>
.input-dark {
  @apply rounded-lg border border-white/10 bg-[#191c1f] px-3 py-2 text-sm text-[#e1e2e7];
}
.label {
  @apply mb-1 block text-[10px] uppercase text-[#b9cacb];
}
.btn-primary {
  @apply rounded-lg bg-[#00f2ff] text-sm font-bold text-[#00363a] disabled:opacity-60;
}
.btn-ghost {
  @apply inline-flex items-center gap-1 rounded-lg border border-white/15 px-3 py-2 text-xs font-semibold text-[#b9cacb] hover:border-[#00dbe7]/40 hover:text-[#e1fdff] disabled:opacity-50;
}
</style>

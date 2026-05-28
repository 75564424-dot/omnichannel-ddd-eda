<template>
  <ControlLayout title="Provisioning" active-nav="provisioning">
    <div class="mb-6 rounded-xl border border-[#00f2ff]/20 bg-[#00f2ff]/5 px-5 py-4">
      <p class="text-sm font-semibold text-[#e1fdff]">{{ pitch.title }}</p>
      <p class="mt-1 text-xs leading-relaxed text-[#b9cacb]">{{ pitch.description }}</p>
    </div>

    <div class="grid grid-cols-1 gap-8 xl:grid-cols-12">
      <!-- Checklist -->
      <aside class="xl:col-span-4">
        <h2 class="text-lg font-bold text-[#e1fdff]">Pipeline de despliegue</h2>
        <p class="mt-1 text-xs text-[#b9cacb]">Estado de la plataforma antes y después del alta.</p>
        <ul class="mt-4 space-y-2">
          <li v-for="s in steps" :key="s.key" class="flex items-start gap-3 rounded-lg border border-white/8 px-3 py-2.5">
            <span class="material-symbols-outlined text-[20px]" :class="s.done ? 'text-[#00f2ff]' : 'text-[#849495]'">
              {{ s.done ? 'check_circle' : 'radio_button_unchecked' }}
            </span>
            <div>
              <p class="text-xs font-medium">{{ s.label }}</p>
              <p class="text-[10px] text-[#849495]">{{ s.detail }}</p>
            </div>
          </li>
        </ul>
      </aside>

      <!-- Form -->
      <form class="space-y-6 xl:col-span-8" @submit.prevent="submit">
        <!-- A. Empresa -->
        <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-5">
          <header class="mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-[#00dbe7]">corporate_fare</span>
            <div>
              <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Datos de la empresa</h3>
              <p class="text-[10px] text-[#849495]">Información comercial y fiscal del cliente.</p>
            </div>
          </header>
          <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <div class="md:col-span-2">
              <label class="label">Nombre comercial *</label>
              <input v-model="form.company_name" required class="input-dark w-full" placeholder="Ej. Acme Retail" @input="maybeSlugFromName" />
            </div>
            <div>
              <label class="label">Razón social</label>
              <input v-model="form.legal_name" class="input-dark w-full" placeholder="Ej. Acme Retail S.A. de C.V." />
            </div>
            <div>
              <label class="label">RFC / Tax ID</label>
              <input v-model="form.tax_id" class="input-dark w-full" placeholder="RFC o identificador fiscal" />
            </div>
            <div>
              <label class="label">Industria</label>
              <select v-model="form.industry" class="input-dark w-full">
                <option value="">— Seleccionar —</option>
                <option v-for="(label, key) in industries" :key="key" :value="key">{{ label }}</option>
              </select>
            </div>
            <div>
              <label class="label">Slug (tenant) *</label>
              <input v-model="form.slug" required class="input-dark w-full font-mono text-xs" placeholder="acme-retail" @input="slugTouched = true" />
              <p class="mt-1 text-[10px] text-[#849495]">Identificador único en el registro SaaS.</p>
            </div>
            <div>
              <label class="label">País</label>
              <input v-model="form.country" class="input-dark w-full" placeholder="México" />
            </div>
            <div>
              <label class="label">Ciudad</label>
              <input v-model="form.city" class="input-dark w-full" />
            </div>
            <div>
              <label class="label">Teléfono</label>
              <input v-model="form.phone" class="input-dark w-full" type="tel" />
            </div>
            <div>
              <label class="label">Email facturación</label>
              <input v-model="form.billing_email" class="input-dark w-full" type="email" placeholder="facturacion@empresa.com" />
            </div>
            <div>
              <label class="label">Sitio web</label>
              <input v-model="form.website" class="input-dark w-full" placeholder="https://" />
            </div>
            <div>
              <label class="label">Zona horaria</label>
              <input v-model="form.timezone" class="input-dark w-full" placeholder="America/Mexico_City" />
            </div>
            <div class="md:col-span-2">
              <label class="label">Notas internas</label>
              <textarea v-model="form.notes" rows="2" class="input-dark w-full resize-none" placeholder="Acuerdos comerciales, contacto de ventas…" />
            </div>
          </div>
        </section>

        <!-- B. Planes -->
        <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-5">
          <header class="mb-4">
            <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Plan contratado</h3>
            <p class="text-[10px] text-[#849495]">Selecciona el paquete. Los módulos sugeridos se aplican automáticamente (puedes ajustarlos abajo).</p>
          </header>
          <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <button
              v-for="plan in plans"
              :key="plan.id"
              type="button"
              class="plan-card text-left"
              :class="form.plan === plan.id ? 'plan-card--active' : ''"
              @click="selectPlan(plan)"
            >
              <div class="flex items-start justify-between gap-2">
                <div>
                  <p class="text-sm font-bold text-[#e1fdff]">{{ plan.name }}</p>
                  <p class="text-[10px] text-[#00dbe7]">{{ plan.tagline }}</p>
                </div>
                <span v-if="form.plan === plan.id" class="material-symbols-outlined text-[#00f2ff]">check_circle</span>
              </div>
              <p class="mt-2 text-[10px] font-semibold text-[#b9cacb]">{{ plan.price_label }}</p>
              <p class="mt-1 text-[10px] leading-snug text-[#849495]">{{ plan.summary }}</p>
              <ul class="mt-3 space-y-1">
                <li v-for="(h, i) in (plan.highlights || []).slice(0, 4)" :key="i" class="flex gap-1 text-[10px] text-[#b9cacb]">
                  <span class="text-[#00f2ff]">•</span>{{ h }}
                </li>
              </ul>
              <div v-if="plan.limits" class="mt-3 border-t border-white/5 pt-2">
                <p v-for="(val, key) in plan.limits" :key="key" class="text-[9px] text-[#849495]">
                  <span class="uppercase">{{ limitLabel(key) }}:</span> {{ val }}
                </p>
              </div>
            </button>
          </div>
        </section>

        <!-- C. Módulos -->
        <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-5">
          <header class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <div>
              <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Módulos para el cliente</h3>
              <p class="text-[10px] text-[#849495]">
                Paquete asignado al tenant. El cliente los verá habilitados y podrá cargar/sincronizar el catálogo técnico en su portal.
              </p>
            </div>
            <button type="button" class="text-[10px] text-[#00dbe7] hover:underline" @click="applyPlanModules">
              Restaurar módulos del plan
            </button>
          </header>
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <label
              v-for="mod in modules"
              :key="mod.id"
              class="module-card"
              :class="{
                'module-card--on': form.modules.includes(mod.id),
                'module-card--required': mod.required,
              }"
            >
              <input
                v-model="form.modules"
                type="checkbox"
                :value="mod.id"
                :disabled="mod.required"
                class="sr-only"
                @change="modulesTouched = true"
              />
              <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-[22px] text-[#00dbe7]">{{ mod.icon || 'extension' }}</span>
                <div class="min-w-0 flex-1">
                  <div class="flex items-center gap-2">
                    <p class="text-sm font-semibold text-[#e1fdff]">{{ mod.name }}</p>
                    <span v-if="mod.required" class="rounded bg-[#00f2ff]/15 px-1.5 py-0.5 text-[9px] uppercase text-[#00f2ff]">Base</span>
                  </div>
                  <p class="text-[10px] text-[#00dbe7]">{{ mod.tagline }}</p>
                  <p class="mt-1 text-[10px] leading-snug text-[#b9cacb]">{{ mod.description }}</p>
                  <p class="mt-2 text-[9px] italic text-[#849495]">{{ mod.client_hint }}</p>
                </div>
                <span
                  class="material-symbols-outlined shrink-0 text-[20px]"
                  :class="form.modules.includes(mod.id) ? 'text-[#00f2ff]' : 'text-[#849495]'"
                >
                  {{ form.modules.includes(mod.id) ? 'toggle_on' : 'toggle_off' }}
                </span>
              </div>
            </label>
          </div>
          <p v-if="form.errors.modules" class="mt-2 text-xs text-red-400">{{ form.errors.modules }}</p>
        </section>

        <!-- D. Admin -->
        <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-5">
          <header class="mb-4">
            <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Admin principal (portal cliente)</h3>
            <p class="text-[10px] text-[#849495]">Primera cuenta con rol platform_admin para operar la instancia.</p>
          </header>
          <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div>
              <label class="label">Nombre *</label>
              <input v-model="form.admin_name" required class="input-dark w-full" />
            </div>
            <div>
              <label class="label">Email *</label>
              <input v-model="form.admin_email" required type="email" class="input-dark w-full" placeholder="admin@empresa.local" />
            </div>
            <div>
              <label class="label">Contraseña *</label>
              <input v-model="form.admin_password" required type="password" minlength="8" class="input-dark w-full" placeholder="Mín. 8 caracteres" />
            </div>
          </div>
        </section>

        <button type="submit" :disabled="form.processing" class="btn-primary w-full py-3 text-sm">
          {{ form.processing ? 'Provisionando…' : 'Ejecutar provisioning' }}
        </button>
      </form>
    </div>
  </ControlLayout>
</template>

<script setup>
import ControlLayout from '@/Layouts/ControlLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  plans: { type: Array, default: () => [] },
  modules: { type: Array, default: () => [] },
  industries: { type: Object, default: () => ({}) },
  steps: { type: Array, default: () => [] },
  pitch: { type: Object, default: () => ({}) },
});

const slugTouched = ref(false);
const modulesTouched = ref(false);

const defaultPlan = props.plans[0]?.id ?? 'starter';
const defaultModules = props.plans.find((p) => p.id === defaultPlan)?.modules_included ?? ['middleware'];

const form = useForm({
  company_name: '',
  legal_name: '',
  tax_id: '',
  industry: '',
  country: '',
  city: '',
  phone: '',
  billing_email: '',
  website: '',
  timezone: 'America/Mexico_City',
  notes: '',
  slug: '',
  plan: defaultPlan,
  modules: [...defaultModules],
  admin_name: '',
  admin_email: '',
  admin_password: '',
});

function slugify(text) {
  return text
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .slice(0, 80);
}

function maybeSlugFromName() {
  if (!slugTouched.value && form.company_name) {
    form.slug = slugify(form.company_name);
  }
}

function selectPlan(plan) {
  form.plan = plan.id;
  if (!modulesTouched.value) {
    form.modules = [...(plan.modules_included || ['middleware'])];
  }
}

function applyPlanModules() {
  const plan = props.plans.find((p) => p.id === form.plan);
  form.modules = [...(plan?.modules_included || ['middleware'])];
  modulesTouched.value = false;
}

function limitLabel(key) {
  const map = { events_month: 'Eventos', operators: 'Operadores', integrations: 'Integraciones' };
  return map[key] || key;
}

function submit() {
  if (!form.modules.includes('middleware')) {
    form.modules = [...form.modules, 'middleware'];
  }
  form.post('/control/provisioning', { preserveScroll: true });
}
</script>

<style scoped>
.input-dark {
  @apply rounded-lg border border-white/10 bg-[#191c1f] px-3 py-2 text-sm text-[#e1e2e7];
}
.label {
  @apply mb-1 block text-[10px] uppercase tracking-wide text-[#b9cacb];
}
.btn-primary {
  @apply rounded-lg bg-[#00f2ff] font-bold text-[#00363a] disabled:opacity-60;
}
.plan-card {
  @apply rounded-lg border border-white/10 bg-[#191c1f]/80 p-4 transition-all hover:border-white/20;
}
.plan-card--active {
  @apply border-[#00f2ff]/50 bg-[#00f2ff]/10 ring-1 ring-[#00f2ff]/30;
}
.module-card {
  @apply block cursor-pointer rounded-lg border border-white/10 bg-[#191c1f]/60 p-4 transition-all hover:border-white/20;
}
.module-card--on {
  @apply border-[#00f2ff]/40 bg-[#00f2ff]/5;
}
.module-card--required {
  @apply cursor-default opacity-95;
}
</style>

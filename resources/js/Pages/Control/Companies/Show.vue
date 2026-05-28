<template>
  <ControlLayout :title="tenant.name" subtitle="Empresa" active-nav="companies">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <p class="font-mono text-xs text-[#849495]">{{ tenant.slug }} · {{ tenant.id }}</p>
        <p class="mt-1 text-sm text-[#b9cacb]">Plan: <strong class="text-[#e1fdff]">{{ tenant.plan }}</strong></p>
      </div>
      <div class="flex gap-2">
        <button v-if="tenant.status === 'active'" type="button" class="btn-danger" @click="suspend">Suspender</button>
        <button v-else type="button" class="btn-primary" @click="activate">Activar</button>
      </div>
    </div>

    <div v-if="$page.props.flash?.message" class="mb-4 rounded-lg border border-[#00f2ff]/30 bg-[#00f2ff]/10 px-4 py-3 text-sm">
      {{ $page.props.flash.message }}
    </div>

    <section
      class="mb-8 rounded-xl border p-6"
      :class="deployment.is_bound_to_instance
        ? 'border-emerald-500/30 bg-emerald-500/5'
        : 'border-amber-500/30 bg-amber-500/5'"
    >
      <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Instancia dedicada (producción)</h3>
      <p class="mt-2 text-sm text-[#b9cacb]">
        <span class="font-mono text-[#e1fdff]">{{ deployment.status_label }}</span>
        · Instancia desplegada aquí: <span class="font-mono">{{ deployment.instance_slug }}</span>
        · Empresa: <span class="font-mono">{{ deployment.tenant_slug }}</span>
      </p>

      <template v-if="!deployment.is_bound_to_instance">
        <p class="mt-3 text-xs text-[#849495]">
          Cada cliente comercial requiere su propio despliegue (BD + URL + <span class="font-mono">PLATFORM_CLIENT_SLUG</span>).
          El registro SaaS no sustituye el despliegue; los operadores inician sesión solo en la URL de su instancia.
        </p>
        <div class="mt-4 rounded-lg border border-white/10 bg-[#0b0e11]/80 p-4 font-mono text-[11px] leading-relaxed text-[#b9cacb]">
          <p v-for="line in deployment.env_snippet" :key="line">{{ line }}</p>
        </div>
        <ol class="mt-4 list-decimal space-y-1 pl-5 text-xs text-[#b9cacb]">
          <li v-for="(cmd, i) in deployment.bootstrap_commands" :key="i">{{ cmd }}</li>
        </ol>
        <p class="mt-3 text-[10px] text-[#849495]">
          URL sugerida: <span class="font-mono text-[#00dbe7]">{{ deployment.recommended_app_url }}</span>
          · Runbook: {{ deployment.runbook_path }}
        </p>
      </template>

      <p v-else class="mt-2 text-xs text-emerald-300/90">
        Esta empresa está vinculada a la instancia actual. Los operadores pueden usar el portal en {{ deployment.recommended_app_url || $page.props.deployment_context?.app_url }}.
      </p>
    </section>

    <section
      v-if="hasCompanyProfile"
      class="mb-8 rounded-xl border border-white/8 bg-[#121820]/70 p-6"
    >
      <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Perfil comercial</h3>
      <dl class="mt-4 grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
        <div v-for="row in companyProfileRows" :key="row.label">
          <dt class="text-[10px] uppercase text-[#849495]">{{ row.label }}</dt>
          <dd class="text-[#e1fdff]">{{ row.value }}</dd>
        </div>
      </dl>
    </section>

    <div class="mb-8 grid grid-cols-2 gap-4 md:grid-cols-4">
      <div v-for="(val, key) in tenant.consumption" :key="key" class="rounded-xl border border-white/8 bg-[#121820]/70 p-4">
        <p class="text-[10px] uppercase text-[#b9cacb]">{{ consumptionLabel(key) }}</p>
        <p class="mt-1 font-mono text-2xl text-[#00f2ff]">{{ val }}</p>
      </div>
    </div>

    <section class="mb-8 rounded-xl border border-[#00dbe7]/25 bg-[#00dbe7]/5 p-6">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Catálogo técnico (bus)</h3>
          <p class="mt-1 text-sm text-[#b9cacb]">
            {{ tenant.modules_catalog?.producers_count ?? 0 }} productores ·
            {{ tenant.modules_catalog?.subscribers_count ?? 0 }} suscriptores
            <span v-if="tenant.module_limits?.producers_max"> (límite {{ tenant.module_limits.producers_max }}P / {{ tenant.module_limits.subscribers_max }}S)</span>
          </p>
        </div>
        <Link :href="`${base}/modules`" class="btn-primary inline-flex items-center gap-2">
          <span class="material-symbols-outlined text-[18px]">tune</span>
          Configurar módulos
        </Link>
      </div>
    </section>

    <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
      <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-6">
        <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Salud (nodos)</h3>
        <ul class="mt-4 space-y-2">
          <li v-for="(status, node) in health.nodes" :key="node" class="flex justify-between text-sm">
            <span>{{ node }}</span>
            <span class="font-mono uppercase">{{ status }}</span>
          </li>
        </ul>
        <p class="mt-2 text-[10px] text-[#849495]">Actualizado: {{ health.last_updated || '—' }}</p>
      </section>

      <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-6">
        <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Plan y módulos</h3>
        <form class="mt-4 space-y-4" @submit.prevent="savePlan">
          <label class="block text-xs text-[#b9cacb]">Plan</label>
          <select v-model="planForm.plan" class="input-dark w-full">
            <option v-for="p in plans" :key="p" :value="p">{{ p }}</option>
          </select>
          <button type="submit" class="btn-primary">Guardar plan</button>
        </form>
        <form class="mt-6 space-y-2" @submit.prevent="saveModules">
          <label class="block text-xs text-[#b9cacb]">Módulos asignados</label>
          <label v-for="m in modules" :key="m" class="flex items-center gap-2 text-sm">
            <input v-model="modulesForm.modules" type="checkbox" :value="m" class="rounded" />
            {{ m }}
          </label>
          <button type="submit" class="btn-primary mt-2">Guardar módulos</button>
        </form>
      </section>
    </div>

    <section class="rounded-xl border border-white/8 bg-[#121820]/70 p-6">
      <h3 class="text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Operadores de instancia (portal cliente)</h3>
      <p v-if="!deployment.operators_on_this_host" class="mt-2 rounded border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-xs text-amber-200/90">
        Cree operadores en el despliegue dedicado de esta empresa (no en el host SaaS compartido), salvo modo demo con
        <span class="font-mono">PLATFORM_PORTAL_MULTI_TENANT_LOGIN=true</span>.
      </p>
      <p v-else class="mt-2 text-xs text-[#849495]">
        Las contraseñas no se pueden mostrar (están cifradas). Use <strong class="text-[#b9cacb]">Restablecer contraseña</strong> para asignar una nueva.
      </p>
      <p v-if="opForm.errors.operator" class="mt-2 text-xs text-[#ffb4ab]">{{ opForm.errors.operator }}</p>
      <p v-if="tenant.primary_operator" class="mt-1 text-sm text-[#b9cacb]">
        Admin principal: <strong class="text-[#e1fdff]">{{ tenant.primary_operator.email }}</strong>
      </p>

      <form
        v-if="deployment.operators_on_this_host"
        class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-4"
        @submit.prevent="createOperator"
      >
        <input v-model="opForm.name" required placeholder="Nombre" class="input-dark" />
        <input v-model="opForm.email" required type="email" placeholder="Email" class="input-dark" />
        <input v-model="opForm.password" required type="password" placeholder="Password" class="input-dark" />
        <select v-model="opForm.platform_role" class="input-dark">
          <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
        </select>
        <button type="submit" class="btn-primary md:col-span-4">Crear operador</button>
      </form>

      <table class="mt-6 w-full text-sm">
        <thead class="text-left text-[10px] uppercase text-[#b9cacb]">
          <tr><th class="py-2">Nombre</th><th>Email</th><th>Rol</th><th>Contraseña</th></tr>
        </thead>
        <tbody class="divide-y divide-white/5">
          <tr v-if="!tenant.operators?.length">
            <td colspan="4" class="py-4 text-sm text-[#849495]">Sin operadores. Cree el primero con el formulario superior.</td>
          </tr>
          <template v-for="u in tenant.operators" :key="u.id">
            <tr>
              <td class="py-2">{{ u.name }}</td>
              <td>{{ u.email }}</td>
              <td>
                <select :value="u.platform_role" class="input-dark text-xs" @change="updateRole(u.id, $event.target.value)">
                  <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                </select>
              </td>
              <td>
                <button
                  v-if="passwordEditId !== u.id"
                  type="button"
                  class="text-xs font-semibold text-[#00dbe7] hover:underline"
                  @click="openPasswordReset(u.id)"
                >
                  Restablecer contraseña
                </button>
                <button
                  v-else
                  type="button"
                  class="text-xs text-[#849495] hover:underline"
                  @click="cancelPasswordReset"
                >
                  Cancelar
                </button>
              </td>
            </tr>
            <tr v-if="passwordEditId === u.id">
              <td colspan="4" class="pb-4">
                <form class="grid grid-cols-1 gap-3 rounded-lg border border-[#00dbe7]/20 bg-[#191c1f]/80 p-4 md:grid-cols-3" @submit.prevent="savePassword(u.id)">
                  <div class="relative md:col-span-1">
                    <input
                      v-model="passwordForm.password"
                      :type="showPassword ? 'text' : 'password'"
                      required
                      minlength="8"
                      placeholder="Nueva contraseña (mín. 8)"
                      class="input-dark w-full pr-10"
                    />
                    <button
                      type="button"
                      class="absolute right-2 top-2 text-[#849495] hover:text-[#e1fdff]"
                      :aria-label="showPassword ? 'Ocultar' : 'Mostrar'"
                      @click="showPassword = !showPassword"
                    >
                      <span class="material-symbols-outlined text-[18px]">{{ showPassword ? 'visibility_off' : 'visibility' }}</span>
                    </button>
                  </div>
                  <input
                    v-model="passwordForm.password_confirmation"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    minlength="8"
                    placeholder="Confirmar contraseña"
                    class="input-dark"
                  />
                  <div class="flex items-center gap-2">
                    <button type="submit" class="btn-primary flex-1" :disabled="passwordForm.processing">
                      {{ passwordForm.processing ? 'Guardando…' : 'Guardar contraseña' }}
                    </button>
                  </div>
                  <p v-if="passwordForm.errors.password" class="md:col-span-3 text-xs text-[#ffb4ab]">{{ passwordForm.errors.password }}</p>
                  <p v-if="passwordForm.errors.password_confirmation" class="md:col-span-3 text-xs text-[#ffb4ab]">
                    {{ passwordForm.errors.password_confirmation }}
                  </p>
                </form>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </section>
  </ControlLayout>
</template>

<script setup>
import ControlLayout from '@/Layouts/ControlLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
  tenant: { type: Object, required: true },
  deployment: { type: Object, required: true },
  health: { type: Object, default: () => ({}) },
  plans: { type: Array, default: () => [] },
  modules: { type: Array, default: () => [] },
  roles: { type: Array, default: () => [] },
});

const base = `/control/companies/${props.tenant.id}`;

const profileLabels = {
  legal_name: 'Razón social',
  tax_id: 'RFC / Tax ID',
  industry: 'Industria',
  country: 'País',
  city: 'Ciudad',
  phone: 'Teléfono',
  billing_email: 'Email facturación',
  website: 'Sitio web',
  timezone: 'Zona horaria',
  notes: 'Notas',
};

const hasCompanyProfile = computed(() => {
  const p = props.tenant.company_profile || {};
  return Object.values(p).some((v) => v);
});

const companyProfileRows = computed(() => {
  const p = props.tenant.company_profile || {};
  return Object.entries(profileLabels)
    .filter(([key]) => p[key])
    .map(([key, label]) => ({ label, value: p[key] }));
});

const planForm = useForm({ plan: props.tenant.plan });
const modulesForm = useForm({ modules: [...(props.tenant.modules || [])] });
const opForm = useForm({ name: '', email: '', password: '', platform_role: 'platform_admin' });
const passwordEditId = ref(null);
const showPassword = ref(false);
const passwordForm = useForm({ password: '', password_confirmation: '' });

function consumptionLabel(key) {
  const map = {
    events_24h: 'Eventos 24h',
    queue_pending: 'Cola pendiente',
    dead_letters: 'Dead letters',
    event_logs: 'Logs 24h',
  };
  return map[key] || key;
}

function suspend() {
  router.post(`${base}/suspend`, {}, { preserveScroll: true });
}
function activate() {
  router.post(`${base}/activate`, {}, { preserveScroll: true });
}
function savePlan() {
  planForm.patch(`${base}/plan`, { preserveScroll: true });
}
function saveModules() {
  modulesForm.patch(`${base}/modules`, { preserveScroll: true });
}
function createOperator() {
  opForm.post(`${base}/operators`, { preserveScroll: true, onSuccess: () => opForm.reset('password') });
}
function updateRole(userId, role) {
  router.patch(`${base}/operators/${userId}/role`, { platform_role: role }, { preserveScroll: true });
}

function openPasswordReset(userId) {
  passwordEditId.value = userId;
  showPassword.value = false;
  passwordForm.reset();
  passwordForm.clearErrors();
}

function cancelPasswordReset() {
  passwordEditId.value = null;
  passwordForm.reset();
  passwordForm.clearErrors();
}

function savePassword(userId) {
  passwordForm.patch(`${base}/operators/${userId}/password`, {
    preserveScroll: true,
    onSuccess: () => {
      cancelPasswordReset();
    },
  });
}
</script>

<style scoped>
.input-dark {
  @apply rounded-lg border border-white/10 bg-[#191c1f] px-3 py-2 text-sm text-[#e1e2e7];
}
.btn-primary {
  @apply rounded-lg bg-[#00f2ff] px-4 py-2 text-sm font-bold text-[#00363a];
}
.btn-danger {
  @apply rounded-lg border border-[#ffb4ab]/40 px-4 py-2 text-sm text-[#ffb4ab];
}
</style>

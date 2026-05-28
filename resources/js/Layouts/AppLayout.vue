<template>
  <div class="flex min-h-screen bg-surface-container-low">
    <!-- Sidebar -->
    <nav class="fixed left-0 top-0 bottom-0 flex flex-col py-6 px-4 bg-white text-slate-900 h-screen w-72 border-r border-slate-200 z-50">
      <!-- Brand -->
      <div class="flex items-center gap-3 px-2 mb-8">
        <div class="h-8 w-8 rounded bg-primary flex items-center justify-center text-white font-bold text-sm shrink-0">
          {{ companyInitial }}
        </div>
        <div class="min-w-0">
          <h1 class="font-black text-slate-900 uppercase tracking-widest text-sm leading-tight truncate" :title="companyName">
            {{ companyName }}
          </h1>
          <p class="text-slate-500 text-xs mt-0.5">Middleware &amp; observability</p>
        </div>
      </div>

      <!-- Primary Navigation -->
      <div class="flex-1 flex flex-col gap-1">
        <Link
          v-if="canAccessDashboard"
          href="/dashboard"
          :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-md transition-all duration-150',
            isActive('Dashboard') ? 'bg-slate-100 text-slate-900 font-medium' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
          ]"
        >
          <span class="material-symbols-outlined text-[20px]" :style="isActive('Dashboard') ? 'font-variation-settings: FILL 1' : ''">dashboard</span>
          <span class="text-sm font-medium">Dashboard</span>
        </Link>

        <Link
          v-if="canAccessMiddleware"
          href="/middleware"
          :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-md transition-all duration-150',
            isActive('Middleware') ? 'bg-slate-100 text-slate-900 font-medium' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
          ]"
        >
          <span class="material-symbols-outlined text-[20px]">account_tree</span>
          <span class="text-sm font-medium">Middleware</span>
        </Link>
      </div>

      <!-- Footer Navigation -->
      <div class="flex flex-col gap-1 pt-4 border-t border-slate-200">
        <button
          type="button"
          class="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-left text-slate-600 transition-all duration-150 hover:bg-slate-50 hover:text-slate-900"
          @click="openSupportModal"
        >
          <span class="material-symbols-outlined text-[20px]">help</span>
          <span class="text-sm font-medium">Support</span>
        </button>
        <Link
          v-if="$page.props.auth?.user"
          href="/logout"
          method="post"
          as="button"
          class="flex items-center gap-3 px-3 py-2.5 rounded-md text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all duration-150 w-full text-left"
        >
          <span class="material-symbols-outlined text-[20px]">logout</span>
          <span class="text-sm font-medium">Sign Out</span>
        </Link>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="flex-1 ml-72 flex flex-col min-h-screen">
      <!-- TopAppBar -->
      <header class="bg-white border-b border-slate-200 shadow-sm flex justify-between items-center h-16 px-6 sticky top-0 z-40">
        <div class="flex items-center gap-4">
          <span class="text-xl font-bold tracking-tight text-slate-900 truncate max-w-[200px] md:max-w-none" :title="companyName">{{ companyName }}</span>
          <div v-if="title" class="flex items-center gap-3">
            <div class="h-4 w-px bg-slate-300"></div>
            <span class="text-sm font-medium text-slate-500">{{ title }}</span>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <!-- Search -->
          <div class="relative hidden md:block mr-3">
            <span class="material-symbols-outlined absolute left-2.5 top-2 text-slate-400 text-[18px]">search</span>
            <input
              type="text"
              :placeholder="searchPlaceholder"
              class="pl-8 pr-4 py-1.5 bg-slate-100 border-none rounded-full text-sm w-64 focus:ring-1 focus:ring-slate-300 outline-none text-slate-700 placeholder:text-slate-400"
            />
          </div>

          <button
            type="button"
            class="p-2 text-slate-500 hover:bg-slate-50 transition-colors rounded-full flex items-center justify-center relative"
            :class="{ 'bg-slate-100 text-primary': notificationsOpen }"
            aria-label="Notificaciones de soporte"
            :aria-expanded="notificationsOpen"
            @click="toggleNotifications"
          >
            <span class="material-symbols-outlined" :style="notificationsOpen ? 'font-variation-settings: FILL 1' : ''">notifications</span>
            <span
              v-if="supportUnreadCount > 0"
              class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full border border-white bg-error"
              aria-hidden="true"
            ></span>
          </button>

          <button
            type="button"
            class="p-2 text-slate-500 hover:bg-slate-50 transition-colors rounded-full flex items-center justify-center relative"
            :class="{ 'bg-slate-100 text-primary': lifePanelOpen }"
            title="Panel en vivo — System Nodes"
            :aria-expanded="lifePanelOpen"
            aria-haspopup="true"
            aria-label="Estado en vivo de módulos"
            @click="lifePanelOpen = true"
          >
            <span class="material-symbols-outlined" :style="lifePanelOpen ? 'font-variation-settings: FILL 1' : ''">sensors</span>
            <span
              v-if="liveAttentionActive"
              class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full border border-white bg-error"
              aria-hidden="true"
            ></span>
          </button>

          <button type="button" class="p-2 text-slate-500 hover:bg-slate-50 transition-colors rounded-full flex items-center justify-center">
            <span class="material-symbols-outlined">settings</span>
          </button>

          <div
            v-if="$page.props.auth?.user"
            class="ml-2 flex max-w-[220px] items-center gap-2 rounded-full border border-slate-200 bg-slate-50 py-1 pl-1 pr-3"
            :title="`${$page.props.auth.user.name} · ${companyName}`"
          >
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-200 text-slate-600">
              <span class="material-symbols-outlined text-[20px]">person</span>
            </div>
            <div class="min-w-0 text-left leading-tight">
              <p class="truncate text-xs font-semibold text-slate-900">{{ $page.props.auth.user.name }}</p>
              <p class="truncate text-[10px] text-slate-500">{{ companyName }}</p>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <div class="flex-1 overflow-y-auto">
        <slot />
      </div>
    </div>
  </div>

  <Teleport to="body">
    <div v-if="lifePanelOpen" class="fixed inset-0 z-[55] bg-slate-900/35 backdrop-blur-[2px]" @click.self="lifePanelOpen = false"></div>

    <aside
      v-if="lifePanelOpen"
      id="life-system-nodes-panel"
      class="fixed top-0 right-0 z-[60] flex h-screen w-full max-w-md flex-col border-l border-slate-200 bg-white shadow-2xl"
      role="dialog"
      aria-modal="true"
      aria-labelledby="life-panel-title"
    >
      <div class="flex items-start justify-between border-b border-slate-100 bg-slate-50/90 px-5 py-4">
        <div>
          <h2 id="life-panel-title" class="text-xs font-bold uppercase tracking-wider text-primary">Live — {{ companyName }}</h2>
          <p class="mt-1 text-[13px] text-slate-600">Módulos configurados para su instancia. Actívelos cuando desee integrar con el middleware.</p>
        </div>
        <button type="button" class="rounded-full p-2 text-slate-500 hover:bg-white hover:text-slate-900" aria-label="Cerrar panel" @click="lifePanelOpen = false">
          <span class="material-symbols-outlined text-[22px]">close</span>
        </button>
      </div>

      <div class="flex-1 overflow-y-auto px-5 py-4">
        <p v-if="panelToggleError" class="mb-3 rounded-sm border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-800">
          {{ panelToggleError }}
        </p>
        <p v-if="panelLoading" class="text-center text-sm text-slate-500 py-12">Actualizando nodos…</p>
        <ul v-else class="flex flex-col gap-3">
          <li v-for="row in panelNodes" :key="row.key" class="rounded-sm border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-2">
              <div class="flex items-center gap-2 min-w-0">
                <span :class="['mt-1.5 shrink-0 w-2 h-2 rounded-full', panelDotClass(row.status)]"></span>
                <span class="text-sm font-semibold text-slate-900 truncate">{{ row.label }}</span>
              </div>
              <span :class="['shrink-0 text-[10px] px-2 py-0.5 rounded-full font-bold', panelBadgeClass(row.status)]">
                {{ row.status }}
              </span>
            </div>

            <p v-if="row.kind" class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ liveKindLabel(row.kind) }}</p>
            <p class="mt-2 text-[11px] text-slate-500 leading-snug">
              <span class="font-medium text-slate-700">Refrescar</span> vuelve a marcar el nodo en línea y reintenta publicaciones fallidas hacia el bus.
              El interruptor controla si ese módulo puede emitir eventos hacia el middleware (integración externa).
            </p>

            <div class="mt-3 flex flex-wrap items-center justify-between gap-3 border-t border-slate-50 pt-3">
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-sm border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-60"
                :disabled="rowRefreshing[row.key]"
                @click="refreshModule(row.key)"
              >
                <span class="material-symbols-outlined text-[18px]" :class="{ 'animate-spin': rowRefreshing[row.key] }">{{ rowRefreshing[row.key] ? 'progress_activity' : 'refresh' }}</span>
                Refrescar
              </button>

              <button
                type="button"
                role="switch"
                :aria-checked="row.middleware_events_enabled"
                class="inline-flex items-center gap-3 rounded-sm text-left hover:bg-slate-50 px-2 py-1 -mr-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
                @click="toggleIngest(row.key, !row.middleware_events_enabled)"
              >
                <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 whitespace-nowrap">Eventos middleware</span>
                <span
                  class="inline-flex h-7 w-12 shrink-0 flex-row items-center rounded-full px-1 transition-colors duration-150"
                  :class="row.middleware_events_enabled ? 'justify-end bg-primary' : 'justify-start bg-slate-300'"
                >
                  <span aria-hidden="true" class="inline-block h-5 w-5 rounded-full bg-white shadow shrink-0"></span>
                </span>
                <span class="text-[11px] text-slate-400">{{ row.middleware_events_enabled ? 'Activado' : 'Apagado' }}</span>
              </button>
            </div>
          </li>
        </ul>
      </div>

      <footer v-if="nodesLastUpdated" class="border-t border-slate-100 bg-slate-50 px-5 py-2 text-[10px] text-slate-500">
        Última lectura servidor: {{ nodesLastUpdated }}
      </footer>
    </aside>

    <div v-if="notificationsOpen" class="fixed inset-0 z-[55] bg-slate-900/25" @click.self="closeNotifications"></div>
    <aside
      v-if="notificationsOpen"
      class="fixed top-0 right-0 z-[60] flex h-screen w-full max-w-md flex-col border-l border-slate-200 bg-white shadow-2xl"
      role="dialog"
      aria-modal="true"
      aria-labelledby="notifications-title"
    >
      <div class="flex items-start justify-between border-b border-slate-100 bg-slate-50/90 px-5 py-4">
        <div>
          <h2 id="notifications-title" class="text-sm font-bold text-slate-900">Mis reportes de soporte</h2>
          <p class="mt-0.5 text-xs text-slate-500">Estado del ticket y respuestas del equipo</p>
        </div>
        <button type="button" class="rounded-full p-2 text-slate-500 hover:bg-white" aria-label="Cerrar" @click="closeNotifications">
          <span class="material-symbols-outlined text-[22px]">close</span>
        </button>
      </div>

      <!-- Detalle -->
      <div v-if="reportDetailLoading && !reportDetail" class="flex flex-1 items-center justify-center py-16 text-sm text-slate-500">
        Cargando reporte…
      </div>
      <div v-else-if="reportDetail" class="flex flex-1 flex-col overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-3">
          <button type="button" class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline" @click="closeReportDetail">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Volver al listado
          </button>
        </div>
        <div class="flex-1 overflow-y-auto px-5 py-4">
          <div class="flex flex-wrap items-center gap-2">
            <span :class="['rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase', reportStatusClass(reportDetail.status)]">
              {{ reportDetail.status_label }}
            </span>
            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-semibold text-slate-600">
              Severidad: {{ reportDetail.severity_label }}
            </span>
          </div>
          <h3 class="mt-3 text-base font-bold text-slate-900">{{ reportDetail.subject }}</h3>
          <p class="mt-1 text-[11px] text-slate-500">Enviado: {{ reportDetail.created_at }}</p>

          <section class="mt-5">
            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Tu reporte</p>
            <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-slate-800">{{ reportDetail.description }}</p>
          </section>

          <section v-if="reportDetail.has_response" class="mt-5 rounded-lg border border-primary/20 bg-primary/5 p-4">
            <p class="text-[10px] font-bold uppercase text-primary">Respuesta del equipo</p>
            <p class="mt-1 text-[11px] text-slate-500">{{ reportDetail.responded_at }} · {{ reportDetail.responded_by_name }}</p>
            <p class="mt-3 whitespace-pre-wrap text-sm leading-relaxed text-slate-900">{{ reportDetail.admin_response }}</p>
          </section>
          <section v-else class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            Aún no hay respuesta del equipo. Te notificaremos aquí cuando respondan.
          </section>

          <details v-if="reportDetail.diagnostic_log && Object.keys(reportDetail.diagnostic_log).length" class="mt-5">
            <summary class="cursor-pointer text-xs font-semibold text-slate-600">Log técnico enviado con el reporte</summary>
            <pre class="mt-2 max-h-48 overflow-auto rounded-lg bg-slate-900 p-3 font-mono text-[10px] text-slate-300">{{ formatDiagnosticLog(reportDetail.diagnostic_log) }}</pre>
          </details>
        </div>
      </div>

      <!-- Listado -->
      <template v-else>
        <div class="grid grid-cols-4 gap-1 border-b border-slate-100 px-3 py-2">
          <button
            v-for="tab in reportTabs"
            :key="tab.id"
            type="button"
            class="rounded-md px-1 py-1.5 text-[10px] font-semibold uppercase leading-tight"
            :class="reportFilter === tab.id ? 'bg-primary text-white' : 'text-slate-500 hover:bg-slate-100'"
            @click="reportFilter = tab.id"
          >
            {{ tab.label }}
            <span class="block text-[9px] opacity-80">({{ tab.count }})</span>
          </button>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-3">
          <p v-if="notificationsLoading" class="py-12 text-center text-sm text-slate-500">Cargando reportes…</p>
          <p v-else-if="filteredReports.length === 0" class="py-12 text-center text-sm text-slate-500">
            {{ emptyFilterMessage }}
          </p>
          <ul v-else class="space-y-2">
            <li
              v-for="r in filteredReports"
              :key="r.id"
              class="cursor-pointer rounded-lg border p-3 transition-colors hover:border-primary/30"
              :class="r.unread ? 'border-primary/40 bg-primary/5' : 'border-slate-200 bg-white'"
              @click="openReportDetail(r)"
            >
              <div class="flex items-start justify-between gap-2">
                <p class="text-sm font-semibold text-slate-900 line-clamp-1">{{ r.subject }}</p>
                <span v-if="r.unread" class="mt-1 h-2 w-2 shrink-0 rounded-full bg-error" aria-label="Respuesta sin leer"></span>
              </div>
              <div class="mt-2 flex flex-wrap items-center gap-2">
                <span :class="['rounded px-1.5 py-0.5 text-[9px] font-bold uppercase', reportStatusClass(r.status)]">{{ r.status_label }}</span>
                <span v-if="r.has_response" class="text-[10px] font-medium text-primary">Con respuesta</span>
                <span v-else class="text-[10px] text-slate-400">Sin respuesta</span>
              </div>
              <p class="mt-2 line-clamp-2 text-xs text-slate-600">{{ r.has_response ? r.admin_response : r.description }}</p>
              <p class="mt-1 text-[10px] text-slate-400">{{ r.created_at }}</p>
            </li>
          </ul>
        </div>
      </template>
    </aside>

    <div v-if="supportOpen" class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/40 p-4" @click.self="closeSupportModal">
      <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="support-dialog-title"
        class="w-full max-w-lg rounded-xl border border-slate-200 bg-white shadow-2xl"
      >
        <div class="flex items-start justify-between border-b border-slate-100 px-5 py-4">
          <div>
            <h2 id="support-dialog-title" class="text-sm font-bold text-slate-900">Reportar incidente</h2>
            <p class="mt-1 text-xs text-slate-500">
              Describe el problema. Se adjuntará un log automático del estado del bus y errores recientes.
            </p>
          </div>
          <button type="button" class="rounded-full p-1 text-slate-400 hover:bg-slate-100" aria-label="Cerrar" @click="closeSupportModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <form class="space-y-4 px-5 py-4" @submit.prevent="submitSupportReport">
          <div v-if="supportSuccess" class="rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800">
            {{ supportSuccess }}
          </div>
          <div v-if="supportError" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
            {{ supportError }}
          </div>

          <div>
            <label class="mb-1 block text-xs font-semibold text-slate-600">Asunto (opcional)</label>
            <input v-model="supportForm.subject" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Ej. Cola bloqueada en producción" />
          </div>

          <div>
            <label class="mb-1 block text-xs font-semibold text-slate-600">Severidad</label>
            <select v-model="supportForm.severity" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
              <option value="low">Baja</option>
              <option value="normal">Normal</option>
              <option value="high">Alta</option>
              <option value="critical">Crítica</option>
            </select>
          </div>

          <div>
            <label class="mb-1 block text-xs font-semibold text-slate-600">Descripción del problema *</label>
            <textarea
              v-model="supportForm.description"
              required
              rows="5"
              minlength="10"
              class="w-full resize-none rounded-lg border border-slate-200 px-3 py-2 text-sm"
              placeholder="Qué ocurrió, cuándo, qué impacto tiene en su operación…"
            />
          </div>

          <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
            <button type="button" class="rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-50" @click="closeSupportModal">
              Cancelar
            </button>
            <button
              type="submit"
              class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
              :disabled="supportSubmitting"
            >
              {{ supportSubmitting ? 'Enviando…' : 'Enviar reporte' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, reactive, watch, onMounted, onUnmounted, computed } from 'vue';
import { parseSystemNode } from '@/lib/systemModules';

export default {
  name: 'AppLayout',
  components: { Link },
  props: {
    title: {
      type: String,
      default: '',
    },
    searchPlaceholder: {
      type: String,
      default: 'Search...',
    },
  },
  setup() {
    const page = usePage();

    const companyName = computed(() => page.props.instance?.company_name || 'Core Platform');
    const canAccessDashboard = computed(() => !!page.props.auth?.user?.can_access_dashboard);
    const canAccessMiddleware = computed(() => !!page.props.auth?.user?.can_access_middleware);
    const companyInitial = computed(() => {
      const name = companyName.value.trim();
      return name ? name.charAt(0).toUpperCase() : 'C';
    });

    function liveModuleRowsFromProps() {
      const rows = page.props.instance?.live_modules;
      if (Array.isArray(rows) && rows.length > 0) {
        return rows.map((row) => ({
          key: String(row.key),
          label: String(row.label ?? row.key),
          kind: row.kind ? String(row.kind) : '',
          description: row.description ? String(row.description) : '',
        }));
      }
      return [{ key: 'middleware', label: 'Middleware bus', kind: 'middleware', description: '' }];
    }

    function mergePanelRows(payload) {
      return liveModuleRowsFromProps().map((row) => ({
        ...row,
        ...parseSystemNode(payload, row.key),
      }));
    }

    const isActive = (module) => {
      const component = page.component;
      return component && component.startsWith(module + '/');
    };
    const supportUnreadCount = ref(page.props.support_unread_count ?? 0);
    const notificationsOpen = ref(false);
    const notificationsLoading = ref(false);
    const supportReports = ref([]);
    const inboxSummary = ref({ total: 0, pending: 0, answered: 0, unread: 0 });
    const reportFilter = ref('all');
    const reportDetail = ref(null);
    const reportDetailLoading = ref(false);

    watch(
      () => page.props.support_unread_count,
      (count) => {
        supportUnreadCount.value = count ?? 0;
      },
    );

    const lifePanelOpen = ref(false);
    const supportOpen = ref(false);
    const supportSubmitting = ref(false);
    const supportSuccess = ref('');
    const supportError = ref('');
    const supportForm = reactive({
      subject: '',
      description: '',
      severity: 'normal',
    });
    const panelLoading = ref(false);
    const panelToggleError = ref('');
    const nodesLastUpdated = ref('');
    const panelNodes = ref(mergePanelRows({}));
    const rowRefreshing = reactive({});
    let panelNodesLoadSeq = 0;

    function applyNodesPayload(payload) {
      if (!payload || typeof payload !== 'object') return;
      nodesLastUpdated.value = payload.last_updated ? String(payload.last_updated) : '';
      panelNodes.value = mergePanelRows(payload);
    }

    function liveKindLabel(kind) {
      switch (kind) {
        case 'producer': return 'Productor';
        case 'subscriber': return 'Suscriptor';
        case 'middleware': return 'Bus';
        default: return kind;
      }
    }

    async function loadPanelNodes() {
      const seq = ++panelNodesLoadSeq;
      panelLoading.value = true;
      panelToggleError.value = '';
      try {
        const { data } = await window.axios.get('/dashboard/nodes/status');
        if (seq !== panelNodesLoadSeq) {
          return;
        }
        applyNodesPayload(data);
      } catch (err) {
        console.error(err);
        if (seq === panelNodesLoadSeq) {
          panelToggleError.value = 'No se pudo leer el estado de los módulos.';
        }
      } finally {
        if (seq === panelNodesLoadSeq) {
          panelLoading.value = false;
        }
      }
    }

    watch(lifePanelOpen, (open) => {
      if (open) {
        loadPanelNodes();
      }
    });

    function nodePath(moduleKey) {
      return `/dashboard/nodes/${encodeURIComponent(moduleKey)}`;
    }

    const liveAttentionActive = computed(() =>
      panelNodes.value.some((row) => row.middleware_events_enabled === false),
    );

    async function refreshModule(moduleKey) {
      rowRefreshing[moduleKey] = true;
      try {
        const { data } = await window.axios.post(`${nodePath(moduleKey)}/refresh`);
        applyNodesPayload(data);
      } catch (err) {
        console.error(err);
      } finally {
        rowRefreshing[moduleKey] = false;
      }
    }

    async function toggleIngest(moduleKey, enabled) {
      panelNodesLoadSeq += 1;
      panelToggleError.value = '';
      const previous = panelNodes.value.map((row) => ({ ...row }));
      panelNodes.value = panelNodes.value.map((row) =>
        row.key === moduleKey ? { ...row, middleware_events_enabled: enabled } : row,
      );
      try {
        const { data } = await window.axios.patch(`${nodePath(moduleKey)}/middleware-events`, {
          middleware_events_enabled: enabled,
        });
        applyNodesPayload(data);
      } catch (err) {
        console.error(err);
        panelNodes.value = previous;
        const status = err.response?.status;
        panelToggleError.value = err.response?.data?.message
          || (status === 419
            ? 'Sesión expirada. Recargue la página e intente de nuevo.'
            : status === 404
              ? 'Este módulo no está registrado en el servidor.'
              : 'No se pudo guardar el cambio. Intente de nuevo.');
      }
    }

    function panelDotClass(status) {
      switch (String(status).toUpperCase()) {
        case 'ONLINE': return 'bg-green-500';
        case 'SYNCING': return 'bg-amber-500';
        case 'HI-LOAD': return 'bg-primary animate-pulse';
        case 'OFFLINE': return 'bg-red-500';
        default: return 'bg-green-500';
      }
    }

    function panelBadgeClass(status) {
      switch (String(status).toUpperCase()) {
        case 'ONLINE': return 'bg-green-50 text-green-700';
        case 'SYNCING': return 'bg-amber-50 text-amber-700';
        case 'HI-LOAD': return 'bg-slate-900 text-white';
        case 'OFFLINE': return 'bg-red-50 text-red-700';
        default: return 'bg-green-50 text-green-700';
      }
    }

    const reportTabs = computed(() => [
      { id: 'all', label: 'Todos', count: inboxSummary.value.total },
      { id: 'pending', label: 'Sin resp.', count: inboxSummary.value.pending },
      { id: 'answered', label: 'Con resp.', count: inboxSummary.value.answered },
      { id: 'unread', label: 'No leídos', count: inboxSummary.value.unread },
    ]);

    const filteredReports = computed(() => {
      const list = supportReports.value;
      switch (reportFilter.value) {
        case 'pending':
          return list.filter((r) => !r.has_response);
        case 'answered':
          return list.filter((r) => r.has_response);
        case 'unread':
          return list.filter((r) => r.unread);
        default:
          return list;
      }
    });

    const emptyFilterMessage = computed(() => {
      const map = {
        all: 'No has enviado reportes todavía.',
        pending: 'No hay reportes pendientes de respuesta.',
        answered: 'Ningún reporte tiene respuesta aún.',
        unread: 'No tienes respuestas nuevas por leer.',
      };
      return map[reportFilter.value] || map.all;
    });

    function reportStatusClass(status) {
      switch (status) {
        case 'resolved':
          return 'bg-green-100 text-green-800';
        case 'acknowledged':
          return 'bg-amber-100 text-amber-800';
        default:
          return 'bg-slate-200 text-slate-700';
      }
    }

    function formatDiagnosticLog(log) {
      try {
        return JSON.stringify(log, null, 2);
      } catch {
        return String(log);
      }
    }

    function syncReportInList(detail) {
      const idx = supportReports.value.findIndex((r) => r.id === detail.id);
      if (idx >= 0) {
        supportReports.value[idx] = { ...supportReports.value[idx], ...detail };
      }
    }

    async function loadNotifications() {
      notificationsLoading.value = true;
      try {
        const { data } = await window.axios.get('/support/notifications');
        supportReports.value = data.reports || [];
        inboxSummary.value = data.summary || { total: 0, pending: 0, answered: 0, unread: 0 };
        supportUnreadCount.value = data.unread_count ?? 0;
      } catch (err) {
        console.error(err);
        supportReports.value = [];
      } finally {
        notificationsLoading.value = false;
      }
    }

    async function toggleNotifications() {
      notificationsOpen.value = !notificationsOpen.value;
      if (notificationsOpen.value) {
        reportDetail.value = null;
        reportFilter.value = 'all';
        await loadNotifications();
      }
    }

    function closeNotifications() {
      notificationsOpen.value = false;
      reportDetail.value = null;
    }

    function closeReportDetail() {
      reportDetail.value = null;
    }

    async function openReportDetail(item) {
      reportDetailLoading.value = true;
      try {
        const { data } = await window.axios.get(`/support/reports/${item.id}`);
        reportDetail.value = data.report;
        supportUnreadCount.value = data.unread_count ?? 0;
        syncReportInList(data.report);
        if (data.report?.has_response) {
          inboxSummary.value = {
            ...inboxSummary.value,
            unread: data.unread_count ?? 0,
          };
        }
      } catch (err) {
        console.error(err);
      } finally {
        reportDetailLoading.value = false;
      }
    }

    function openSupportModal() {
      supportOpen.value = true;
      supportSuccess.value = '';
      supportError.value = '';
    }

    function closeSupportModal() {
      supportOpen.value = false;
    }

    async function submitSupportReport() {
      if (supportForm.description.trim().length < 10) {
        supportError.value = 'La descripción debe tener al menos 10 caracteres.';
        return;
      }
      supportSubmitting.value = true;
      supportError.value = '';
      supportSuccess.value = '';
      try {
        const { data } = await window.axios.post('/support/reports', {
          subject: supportForm.subject || undefined,
          description: supportForm.description.trim(),
          severity: supportForm.severity,
          page_url: window.location.href,
        });
        supportSuccess.value = data.message || 'Reporte enviado correctamente.';
        supportForm.subject = '';
        supportForm.description = '';
        supportForm.severity = 'normal';
      } catch (err) {
        const status = err.response?.status;
        const msg = err.response?.data?.message
          || err.response?.data?.errors?.description?.[0]
          || (status === 419
            ? 'Sesión expirada. Recargue la página e intente de nuevo.'
            : status === 401
              ? 'No autenticado. Cierre sesión y vuelva a entrar con su cuenta de cliente.'
              : status === 403
                ? 'Su rol no tiene permiso para enviar reportes.'
                : 'No se pudo enviar el reporte. Intente de nuevo.');
        supportError.value = msg;
      } finally {
        supportSubmitting.value = false;
      }
    }

    function escapeClose(e) {
      if (e.key === 'Escape') {
        lifePanelOpen.value = false;
        supportOpen.value = false;
        closeNotifications();
      }
    }

    onMounted(() => {
      window.addEventListener('keydown', escapeClose);
      loadPanelNodes();
    });
    onUnmounted(() => window.removeEventListener('keydown', escapeClose));

    watch(
      () => page.props.instance?.live_modules,
      () => {
        if (!lifePanelOpen.value) {
          panelNodes.value = mergePanelRows({});
        }
      },
    );

    return {
      companyName,
      companyInitial,
      canAccessDashboard,
      canAccessMiddleware,
      liveKindLabel,
      liveAttentionActive,
      isActive,
      lifePanelOpen,
      panelLoading,
      panelToggleError,
      panelNodes,
      nodesLastUpdated,
      refreshModule,
      toggleIngest,
      panelDotClass,
      panelBadgeClass,
      rowRefreshing,
      supportOpen,
      supportForm,
      supportSubmitting,
      supportSuccess,
      supportError,
      openSupportModal,
      closeSupportModal,
      submitSupportReport,
      supportUnreadCount,
      notificationsOpen,
      notificationsLoading,
      supportReports,
      inboxSummary,
      reportFilter,
      reportTabs,
      filteredReports,
      emptyFilterMessage,
      reportDetail,
      reportDetailLoading,
      toggleNotifications,
      closeNotifications,
      closeReportDetail,
      openReportDetail,
      reportStatusClass,
      formatDiagnosticLog,
    };
  },
};
</script>

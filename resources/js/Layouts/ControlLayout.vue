<template>
  <div class="control-portal flex min-h-screen bg-[#0b0e11] text-[#e1e2e7]">
    <div class="pointer-events-none fixed inset-0 opacity-[0.35]" style="background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 0); background-size: 32px 32px;"></div>

    <aside class="fixed inset-y-0 left-0 z-50 flex w-[260px] flex-col border-r border-white/5 bg-[#0b0e11]/95 py-6 backdrop-blur-lg">
      <div class="mb-6 px-6">
        <h1 class="text-xl font-bold tracking-tighter text-[#e1fdff]">System Control</h1>
        <p class="mt-1 text-[10px] uppercase tracking-widest text-[#b9cacb]">SaaS — Cloud Management</p>
      </div>

      <nav class="flex-1 space-y-4 overflow-y-auto px-2">
        <div>
          <Link href="/control/overview" :class="navClass({ href: '/control/overview', key: 'overview' })">
            <span class="material-symbols-outlined text-[22px]">dashboard</span>
            <span class="text-xs font-medium uppercase tracking-wide">Global Overview</span>
          </Link>
        </div>

        <div v-for="section in navSections" :key="section.title">
          <p class="mb-1 px-4 text-[9px] font-bold uppercase tracking-[0.2em] text-[#849495]">{{ section.title }}</p>
          <div class="space-y-0.5">
            <Link
              v-for="item in section.items"
              :key="item.href"
              :href="item.href"
              :class="navClass(item)"
            >
              <span class="material-symbols-outlined text-[22px]">{{ item.icon }}</span>
              <span class="text-xs font-medium uppercase tracking-wide">{{ item.label }}</span>
            </Link>
          </div>
        </div>
      </nav>

      <div class="mt-auto border-t border-white/5 px-6 py-4">
        <p class="truncate text-xs font-semibold text-[#00dbe7]">{{ $page.props.auth?.user?.name }}</p>
        <p class="text-[10px] uppercase text-[#b9cacb]">SaaS Admin</p>
        <Link
          v-if="$page.props.auth?.user"
          href="/logout"
          method="post"
          as="button"
          class="mt-3 flex w-full items-center gap-2 text-xs text-[#b9cacb] hover:text-[#e1fdff]"
        >
          <span class="material-symbols-outlined text-[18px]">logout</span>
          Sign out
        </Link>
      </div>
    </aside>

    <div class="relative ml-[260px] flex min-h-screen flex-1 flex-col">
      <header class="sticky top-0 z-40 flex h-16 items-center justify-between border-b border-white/5 bg-[#111417]/80 px-8 backdrop-blur-md">
        <div class="flex items-center gap-4">
          <span class="text-lg font-bold tracking-tight text-[#e1fdff]">{{ title }}</span>
          <div v-if="subtitle" class="hidden items-center gap-2 md:flex">
            <span class="text-[#849495]">/</span>
            <span class="text-xs uppercase tracking-widest text-[#b9cacb]">{{ subtitle }}</span>
          </div>
        </div>
        <div class="flex items-center gap-2 rounded-full border border-white/5 bg-[#1d2023] px-3 py-1">
          <span class="h-2 w-2 animate-pulse rounded-full bg-[#00f2ff] shadow-[0_0_8px_#00f2ff]"></span>
          <span class="text-[10px] uppercase tracking-wider text-[#b9cacb]">Datos en vivo</span>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto p-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
  title: { type: String, default: 'System Control' },
  subtitle: { type: String, default: '' },
  activeNav: { type: String, default: '' },
});

const page = usePage();

const navSections = [
  {
    title: 'A · Empresas',
    items: [
      { href: '/control/companies', label: 'Gestión empresas', icon: 'corporate_fare', key: 'companies' },
      { href: '/control/simulations', label: 'Historial simulaciones', icon: 'history', key: 'simulations' },
    ],
  },
  {
    title: 'B · Middleware',
    items: [{ href: '/control/middleware', label: 'Middleware global', icon: 'hub', key: 'middleware' }],
  },
  {
    title: 'C · Infraestructura',
    items: [{ href: '/control/infrastructure', label: 'Infraestructura', icon: 'dns', key: 'infrastructure' }],
  },
  {
    title: 'D · Incidentes',
    items: [{ href: '/control/incidents', label: 'Incidentes y alertas', icon: 'warning', key: 'incidents' }],
  },
  {
    title: 'E · Provisioning',
    items: [{ href: '/control/provisioning', label: 'Provisioning', icon: 'add_box', key: 'provisioning' }],
  },
];

function navClass(item) {
  const active = props.activeNav === item.key || page.url.startsWith(item.href);
  return [
    'flex items-center gap-4 px-4 py-3 transition-all rounded-sm',
    active
      ? 'border-l-4 border-[#00f2ff] bg-[#00f2ff]/10 text-[#00f2ff]'
      : 'text-[#b9cacb] hover:bg-white/5 hover:text-[#e1fdff]',
  ];
}
</script>

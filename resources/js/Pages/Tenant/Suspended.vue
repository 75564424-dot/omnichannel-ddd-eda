<template>
  <main class="flex min-h-screen items-center justify-center bg-[#10161b] px-6 py-12 text-[#e6f7f8]">
    <section class="w-full max-w-xl text-center">
      <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg border border-[#ffb4ab]/35 bg-[#ffb4ab]/10 text-[#ffb4ab]">
        <span class="material-symbols-outlined text-[30px]">lock</span>
      </div>

      <p class="mt-8 text-xs font-bold uppercase tracking-widest text-[#00dbe7]">Acceso temporalmente bloqueado</p>
      <h1 class="mt-3 text-3xl font-bold text-[#e1fdff]">Servicio suspendido</h1>
      <p class="mt-4 text-sm leading-6 text-[#b9cacb]">
        {{ message }}
      </p>

      <div class="mt-8 border-t border-white/10 pt-5 text-xs text-[#849495]">
        Omnichannel Platform mantiene los datos y la configuracion de la instancia protegidos durante la suspension.
      </div>
    </section>
  </main>
</template>

<script setup>
defineProps({
  message: {
    type: String,
    default: 'El servicio asociado a esta empresa se encuentra temporalmente suspendido. Contacte al administrador para obtener mas informacion.',
  },
});
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-surface-container-low px-4">
    <div class="w-full max-w-md bg-white border border-slate-200 shadow-sm rounded-sm p-8">
      <div class="flex items-center gap-3 mb-8">
        <div class="h-10 w-10 rounded bg-primary flex items-center justify-center text-white font-bold">{{ companyInitial }}</div>
        <div>
          <h1 class="text-lg font-bold text-slate-900">{{ companyName }}</h1>
          <p class="text-xs text-slate-500">Operator sign in</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-5">
        <div>
          <label for="email" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            autocomplete="username"
            required
            class="w-full border border-slate-200 rounded-sm px-3 py-2 text-sm focus:ring-1 focus:ring-primary/40 outline-none"
          />
          <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
        </div>

        <div>
          <label for="password" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            autocomplete="current-password"
            required
            class="w-full border border-slate-200 rounded-sm px-3 py-2 text-sm focus:ring-1 focus:ring-primary/40 outline-none"
          />
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
          <input v-model="form.remember" type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary/30" />
          Remember me
        </label>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-primary text-white font-semibold text-sm py-2.5 rounded-sm hover:opacity-90 disabled:opacity-60 transition-opacity"
        >
          {{ form.processing ? 'Signing in…' : 'Sign in' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

const page = usePage();
const companyName = computed(() => page.props.instance?.company_name || 'Core Platform');
const companyInitial = computed(() => {
  const name = companyName.value.trim();
  return name ? name.charAt(0).toUpperCase() : 'C';
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

function submit() {
  form.post('/login');
}
</script>

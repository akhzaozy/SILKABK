<template>
  <Link
    :href="href"
    class="flex items-center gap-3 p-4 bg-white rounded-2xl border border-gray-200 hover:border-blue-300 hover:shadow-sm transition-all"
  >
    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
      <slot name="icon">
        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
      </slot>
    </div>
    <div class="flex-1">
      <h3 class="font-semibold text-gray-900">{{ title }}</h3>
      <p v-if="description" class="text-sm text-gray-500">{{ description }}</p>
    </div>
    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  href: String,
  title: String,
  description: String
})
</script>

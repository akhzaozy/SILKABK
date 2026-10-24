<template>
  <div class="bg-white rounded-2xl border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-sm font-medium text-gray-500">{{ title }}</h3>
      <slot name="icon">
        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
        </div>
      </slot>
    </div>
    <p class="text-3xl font-bold text-gray-900">{{ value }}</p>
    <p v-if="subtitle" class="text-sm text-gray-500 mt-1">{{ subtitle }}</p>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  value: [String, Number],
  subtitle: String
})
</script>

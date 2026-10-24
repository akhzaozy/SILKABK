<template>
  <div class="bg-white rounded-2xl border border-gray-200 p-5">
    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ label }}</p>
    <p :class="`text-xl font-bold mt-1 text-${color}-600`">{{ value }}</p>
    <p class="text-xs text-gray-400 mt-0.5">{{ count }}</p>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  count: { type: String, default: '' },
  color: { type: String, default: 'blue' },
})
</script>
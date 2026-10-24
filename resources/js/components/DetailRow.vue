<template>
  <div class="px-6 py-4 flex items-start gap-4">
    <p class="text-sm text-gray-500 w-44 flex-shrink-0 pt-0.5">{{ label }}</p>
    <div class="flex-1 text-sm text-gray-800">
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
})
</script>
<template>
  <p class="px-3 pt-5 pb-1 text-[10px] font-semibold uppercase tracking-widest text-gray-400">
    {{ label }}
  </p>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
})
</script>
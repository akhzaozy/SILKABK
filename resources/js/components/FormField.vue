<template>
  <div class="mb-4">
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <slot />
    <p v-if="error" class="text-red-500 text-sm mt-1">{{ error }}</p>
    <p v-if="help" class="text-gray-500 text-sm mt-1">{{ help }}</p>
  </div>
</template>

<script setup>
defineProps({
  label: String,
  id: String,
  error: String,
  help: String,
  required: Boolean
})
</script>

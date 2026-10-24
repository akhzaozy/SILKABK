<template>
  <Link
    :href="href"
    class="flex items-center gap-3 px-3 rounded-lg text-sm transition-colors"
    style="min-height: 44px; padding-top: 0.625rem; padding-bottom: 0.625rem"
    :class="isActive
      ? 'bg-blue-50 text-blue-700 font-medium'
      : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
  >
    <span class="w-4 h-4 flex-shrink-0 text-current opacity-70">
      <!-- Icon slot sederhana - bisa diganti dengan heroicons / lucide -->
      <slot name="icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </slot>
    </span>
    {{ label }}
  </Link>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const props = defineProps({
  href:  { type: String, required: true },
  label: { type: String, required: true },
  icon:  { type: String, default: '' },
})

const page = usePage()

// Aktif jika URL saat ini dimulai dengan href
const isActive = computed(() =>
  page.url.startsWith(props.href) && props.href !== '#'
)
</script>
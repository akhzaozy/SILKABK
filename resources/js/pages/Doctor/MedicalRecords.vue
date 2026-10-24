<template>
  <AppLayout title="Rekam Medis Pasien">
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Rekam Medis Pasien</h1>
        <Link :href="route('doctor.medical-records.create')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
          Input Rekam Medis Baru
        </Link>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-600">Kelola rekam medis - Dalam pengembangan</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

defineProps({
  records: Object,
})
</script>

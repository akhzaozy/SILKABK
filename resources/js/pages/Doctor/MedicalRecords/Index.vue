<template>
  <AppLayout title="Rekam Medis Pasien">
    <div class="space-y-5">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Rekam Medis Pasien</h2>
          <p class="text-sm text-gray-500 mt-0.5">Kelola rekam medis elektronik pasien</p>
        </div>
        <Link :href="route('doctor.medical-records.create')"
          class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
          + Input Rekam Medis
        </Link>
      </div>

      <!-- Search -->
      <div class="bg-white rounded-2xl border border-gray-200 p-4">
        <div class="flex gap-3">
          <input v-model="search" @keyup.enter="applySearch" type="text"
            class="flex-1 text-sm border border-gray-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Cari nama pasien atau nomor rekam medis..." />
          <button @click="applySearch"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors">
            Cari
          </button>
          <button v-if="search" @click="search = ''; applySearch()"
            class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 rounded-xl hover:bg-gray-100 transition-colors">
            Reset
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="records.data.length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
        <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
          <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <h3 class="text-base font-medium text-gray-800 mb-1">Belum Ada Rekam Medis</h3>
        <p class="text-sm text-gray-500 mb-4">Mulai input rekam medis pasien</p>
        <Link :href="route('doctor.medical-records.create')"
          class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
          Input Rekam Medis Baru
        </Link>
      </div>

      <!-- Table -->
      <div v-else class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pasien</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">ICD-10</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Diagnosis</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">KPSP</th>
              <th class="px-5 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="r in records.data" :key="r.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3">
                <p class="font-medium text-gray-800">{{ r.patient?.name }}</p>
                <p class="text-xs text-gray-400">{{ r.patient?.medical_record_number }}</p>
              </td>
              <td class="px-5 py-3 text-gray-700">{{ formatDate(r.visit_date) }}</td>
              <td class="px-5 py-3">
                <span v-if="r.icd_code" class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">{{ r.icd_code }}</span>
                <span v-else class="text-gray-300">—</span>
              </td>
              <td class="px-5 py-3 text-gray-700 max-w-xs">
                <p class="line-clamp-2">{{ r.diagnosis }}</p>
              </td>
              <td class="px-5 py-3">
                <span v-if="r.kpsp_result" class="inline-flex items-center gap-1 text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200 px-2 py-1 rounded-full">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Ada
                </span>
                <span v-else class="text-gray-300 text-xs">—</span>
              </td>
              <td class="px-5 py-3">
                <div class="flex items-center gap-2 justify-end">
                  <Link :href="route('doctor.medical-records.show', r.id)"
                    class="text-xs text-blue-600 hover:text-blue-800 font-medium px-2 py-1 rounded hover:bg-blue-50">
                    Lihat
                  </Link>
                  <Link :href="route('doctor.medical-records.edit', r.id)"
                    class="text-xs text-gray-600 hover:text-gray-800 font-medium px-2 py-1 rounded hover:bg-gray-100">
                    Edit
                  </Link>
                </div>
              </td>
            </tr>
          </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="records.last_page > 1" class="flex items-center justify-between">
        <p class="text-xs text-gray-500">{{ records.from }}–{{ records.to }} dari {{ records.total }}</p>
        <div class="flex gap-1">
          <Link v-for="link in records.links" :key="link.label" :href="link.url ?? '#'"
            :class="[
              'px-3 py-1.5 text-xs rounded-lg transition-colors',
              link.active ? 'bg-blue-600 text-white font-semibold'
                : link.url ? 'text-gray-600 hover:bg-gray-100'
                : 'text-gray-300 cursor-not-allowed',
            ]"
            v-html="link.label"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  records: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const search = ref(props.filters.search ?? '')

function applySearch() {
  router.get(route('doctor.medical-records'), search.value ? { search: search.value } : {}, {
    preserveState: true, replace: true,
  })
}

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}
</script>

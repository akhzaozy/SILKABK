<template>
  <AppLayout title="Rekam Medis Anak">
    <div class="space-y-5">

      <!-- Header -->
      <div>
        <h2 class="text-lg font-semibold text-gray-800">Rekam Medis Anak</h2>
        <p class="text-sm text-gray-500 mt-0.5">Lihat riwayat rekam medis anak Anda</p>
      </div>

      <!-- Filter -->
      <div class="bg-white rounded-2xl border border-gray-200 p-4">
        <div class="flex flex-wrap gap-3">
          <select v-model="selectedPatient" @change="applyFilter"
            class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Anak</option>
            <option v-for="p in patients" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
          <button v-if="selectedPatient" @click="selectedPatient = ''; applyFilter()"
            class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded-xl hover:bg-gray-100">
            Reset
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="records.data.length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
        <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
          <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <h3 class="text-base font-medium text-gray-800 mb-1">Belum Ada Rekam Medis</h3>
        <p class="text-sm text-gray-500">Rekam medis akan muncul setelah dokter melakukan pemeriksaan</p>
      </div>

      <!-- Records List -->
      <div v-else class="space-y-3">
        <div v-for="r in records.data" :key="r.id" class="bg-white rounded-2xl border border-gray-200 hover:border-blue-200 hover:shadow-sm transition-all">
          <div class="p-5">
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <span class="text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 px-2 py-1 rounded-full">
                    {{ r.icd_code ? `ICD: ${r.icd_code}` : 'Rekam Medis' }}
                  </span>
                </div>
                <p class="text-base font-semibold text-gray-800">{{ r.patient?.name }}</p>
                <p class="text-sm text-gray-500">
                  {{ formatDate(r.visit_date) }}
                  <span v-if="r.doctor"> · Dr. {{ r.doctor?.name }}</span>
                  <span v-if="r.psychologist"> · Psi. {{ r.psychologist?.nama }}</span>
                </p>

                <div class="mt-3 text-sm text-gray-700">
                  <p class="text-xs font-medium text-gray-500 uppercase mb-0.5">Diagnosis</p>
                  <p class="line-clamp-2">{{ r.diagnosis }}</p>
                </div>

                <!-- KPSP indicator -->
                <div v-if="r.kpsp_result" class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200 px-2 py-1 rounded-full">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Screening KPSP tersedia
                </div>
              </div>

              <Link :href="route('parent.medical-records.show', r.id)"
                class="flex-shrink-0 text-xs font-medium text-blue-600 hover:text-blue-800 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-colors">
                Detail →
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="records.last_page > 1" class="flex items-center justify-between">
        <p class="text-xs text-gray-500">{{ records.from }}–{{ records.to }} dari {{ records.total }} rekam medis</p>
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
  records:  { type: Object, required: true },
  patients: { type: Array,  default: () => [] },
  filters:  { type: Object, default: () => ({}) },
})

const selectedPatient = ref(props.filters?.patient_id ?? '')

function applyFilter() {
  router.get(route('parent.medical-records'), selectedPatient.value ? { patient_id: selectedPatient.value } : {}, {
    preserveState: true, replace: true,
  })
}

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}
</script>

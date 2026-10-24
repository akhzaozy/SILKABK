<template>
  <AppLayout title="Riwayat Diagnosa Anak">
    <div class="space-y-5">

      <div>
        <h2 class="text-lg font-semibold text-gray-800">Riwayat Diagnosa Anak</h2>
        <p class="text-sm text-gray-500 mt-0.5">Hasil diagnosa penyakit dari dokter</p>
      </div>

      <!-- Filter Pasien -->
      <div v-if="patients.length > 1" class="bg-white rounded-2xl border border-gray-200 p-4">
        <div class="flex flex-wrap gap-2">
          <button @click="filterPatient('')"
            :class="['px-4 py-1.5 text-sm rounded-xl border-2 transition-all font-medium',
              !selectedPatient ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:border-gray-300']">
            Semua Anak
          </button>
          <button v-for="p in patients" :key="p.id"
            @click="filterPatient(p.id)"
            :class="['px-4 py-1.5 text-sm rounded-xl border-2 transition-all font-medium',
              selectedPatient == p.id ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:border-gray-300']">
            {{ p.name }}
          </button>
        </div>
      </div>

      <!-- Kosong -->
      <div v-if="diagnoses.data.length === 0" class="bg-gray-50 rounded-2xl border border-gray-200 p-10 text-center">
        <p class="text-gray-400 text-sm">Belum ada riwayat diagnosa.</p>
      </div>

      <!-- List Diagnosa -->
      <div v-else class="space-y-3">
        <div v-for="d in diagnoses.data" :key="d.id"
          class="bg-white rounded-2xl border border-gray-200 p-5 hover:shadow-sm transition-shadow">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap mb-2">
                <span class="text-xs text-gray-400">{{ formatDate(d.diagnosis_date) }}</span>
                <span v-if="d.icd_code" class="text-xs font-mono bg-gray-100 text-gray-500 px-2 py-0.5 rounded">
                  {{ d.icd_code }}
                </span>
              </div>
              <p class="font-semibold text-gray-800">{{ d.diagnosis_name }}</p>
              <p class="text-sm text-gray-500 mt-0.5">
                Pasien: <span class="font-medium text-gray-700">{{ d.patient?.name }}</span>
                &nbsp;·&nbsp; Dr. {{ d.doctor?.name }}
              </p>
              <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ d.symptoms }}</p>
            </div>
            <Link :href="route('parent.diagnoses.show', d.id)"
              class="flex-shrink-0 px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors">
              Detail
            </Link>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="diagnoses.last_page > 1" class="flex items-center justify-between">
        <p class="text-xs text-gray-500">{{ diagnoses.from }}–{{ diagnoses.to }} dari {{ diagnoses.total }}</p>
        <div class="flex gap-1">
          <Link v-for="link in diagnoses.links" :key="link.label"
            :href="link.url ?? '#'"
            :class="['px-3 py-1 text-xs rounded-lg', link.active ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100', !link.url && 'opacity-40 pointer-events-none']"
            v-html="link.label" />
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
  diagnoses:  { type: Object, required: true },
  patients:   { type: Array, default: () => [] },
  filters:    { type: Object, default: () => ({}) },
})

const selectedPatient = ref(props.filters.patient_id ?? '')

function filterPatient(id) {
  selectedPatient.value = id
  router.get(route('parent.diagnoses'), { patient_id: id || undefined }, { preserveState: true, replace: true })
}

function formatDate(val) {
  if (!val) return '—'
  const d = new Date(String(val).substring(0, 10))
  return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }).format(d)
}
</script>

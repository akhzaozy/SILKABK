<template>
  <AppLayout title="Diagnosa Penyakit">
    <div class="space-y-5">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Diagnosa Penyakit</h2>
          <p class="text-sm text-gray-500 mt-0.5">Riwayat diagnosa pasien yang ditangani</p>
        </div>
        <Link :href="route('doctor.diagnoses.create')"
          class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
          + Diagnosa Baru
        </Link>
      </div>

      <!-- Filter -->
      <div class="bg-white rounded-2xl border border-gray-200 p-4 flex flex-wrap gap-3">
        <input v-model="search" @keyup.enter="applyFilter" type="text"
          class="flex-1 min-w-48 text-sm border border-gray-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Cari nama / nomor rekam medis..." />
        <select v-model="selectedPatient" @change="applyFilter"
          class="text-sm border border-gray-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Semua Pasien</option>
          <option v-for="p in patients" :key="p.id" :value="p.id">{{ p.name }}</option>
        </select>
        <button @click="applyFilter"
          class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors">
          Cari
        </button>
        <button v-if="search || selectedPatient" @click="resetFilter"
          class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 rounded-xl hover:bg-gray-100 transition-colors">
          Reset
        </button>
      </div>

      <!-- Tabel -->
      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pasien</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Diagnosa Penyakit</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">ICD-10</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="diagnoses.data.length === 0">
              <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-400">Belum ada diagnosa.</td>
            </tr>
            <tr v-for="d in diagnoses.data" :key="d.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ formatDate(d.diagnosis_date) }}</td>
              <td class="px-4 py-3">
                <p class="font-medium text-gray-800">{{ d.patient?.name }}</p>
                <p class="text-xs text-gray-400">{{ d.patient?.medical_record_number }}</p>
              </td>
              <td class="px-4 py-3 font-medium text-gray-800">{{ d.diagnosis_name }}</td>
              <td class="px-4 py-3">
                <span v-if="d.icd_code" class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-0.5 rounded">
                  {{ d.icd_code }}
                </span>
                <span v-else class="text-gray-300">—</span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <Link :href="route('doctor.diagnoses.show', d.id)"
                    class="text-xs text-blue-600 hover:text-blue-800 font-medium px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-colors">
                    Detail
                  </Link>
                  <Link :href="route('doctor.diagnoses.edit', d.id)"
                    class="text-xs text-amber-600 hover:text-amber-800 font-medium px-3 py-1.5 rounded-lg hover:bg-amber-50 transition-colors">
                    Edit
                  </Link>
                  <button @click="confirmDelete(d)"
                    class="text-xs text-red-500 hover:text-red-700 font-medium px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="diagnoses.last_page > 1" class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
          <p class="text-xs text-gray-500">{{ diagnoses.from }}–{{ diagnoses.to }} dari {{ diagnoses.total }}</p>
          <div class="flex gap-1">
            <Link v-for="link in diagnoses.links" :key="link.label"
              :href="link.url ?? '#'"
              :class="['px-3 py-1 text-xs rounded-lg', link.active ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100', !link.url && 'opacity-40 pointer-events-none']"
              v-html="link.label" />
          </div>
        </div>
      </div>

      <!-- Modal Konfirmasi Hapus -->
      <div v-if="toDelete" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-xl">
          <h3 class="font-semibold text-gray-800 mb-2">Hapus Diagnosa?</h3>
          <p class="text-sm text-gray-600 mb-5">
            Diagnosa <span class="font-medium">{{ toDelete.diagnosis_name }}</span> untuk
            <span class="font-medium">{{ toDelete.patient?.name }}</span> akan dihapus permanen.
          </p>
          <div class="flex justify-end gap-3">
            <button @click="toDelete = null"
              class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50">
              Batal
            </button>
            <button @click="doDelete"
              class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl">
              Hapus
            </button>
          </div>
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
  diagnoses:      { type: Object, required: true },
  patients:       { type: Array, default: () => [] },
  filters:        { type: Object, default: () => ({}) },
})

const search          = ref(props.filters.search ?? '')
const selectedPatient = ref(props.filters.patient_id ?? '')
const toDelete        = ref(null)

function applyFilter() {
  router.get(route('doctor.diagnoses.index'), {
    search:     search.value || undefined,
    patient_id: selectedPatient.value || undefined,
  }, { preserveState: true, replace: true })
}

function resetFilter() {
  search.value = ''
  selectedPatient.value = ''
  applyFilter()
}

function confirmDelete(d) { toDelete.value = d }

function doDelete() {
  router.delete(route('doctor.diagnoses.destroy', toDelete.value.id), {
    onSuccess: () => { toDelete.value = null },
  })
}

function formatDate(val) {
  if (!val) return '—'
  const d = new Date(String(val).substring(0, 10))
  return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }).format(d)
}
</script>

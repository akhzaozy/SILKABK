<template>
  <AppLayout title="Detail Diagnosa">
    <div class="max-w-2xl space-y-5">

      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Detail Diagnosa</h2>
          <p class="text-sm text-gray-500 mt-0.5">{{ formatDate(diagnosis.diagnosis_date) }}</p>
        </div>
        <Link :href="route('parent.diagnoses')"
          class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
          Kembali
        </Link>
      </div>

      <!-- Identitas -->
      <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-700 text-sm uppercase tracking-wide mb-4">Identitas Pasien</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-xs text-gray-400 font-medium uppercase mb-0.5">Nama Anak</p>
            <p class="text-gray-800 font-semibold">{{ diagnosis.patient?.name }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-400 font-medium uppercase mb-0.5">No. Rekam Medis</p>
            <p class="text-gray-700 font-mono">{{ diagnosis.patient?.medical_record_number }}</p>
          </div>
          <div class="col-span-2">
            <p class="text-xs text-gray-400 font-medium uppercase mb-0.5">Dokter Pemeriksa</p>
            <p class="text-gray-700">Dr. {{ diagnosis.doctor?.name }}</p>
          </div>
        </div>
      </div>

      <!-- Hasil Diagnosa -->
      <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
        <h3 class="font-semibold text-gray-700 text-sm uppercase tracking-wide">Hasil Diagnosa</h3>

        <div>
          <p class="text-xs text-gray-400 font-medium uppercase mb-1">Gejala yang Dialami</p>
          <p class="text-sm text-gray-800 whitespace-pre-line leading-relaxed bg-gray-50 rounded-xl p-3">
            {{ diagnosis.symptoms }}
          </p>
        </div>

        <div class="flex items-center gap-4">
          <div class="flex-1">
            <p class="text-xs text-gray-400 font-medium uppercase mb-1">Diagnosa Penyakit</p>
            <p class="text-base font-bold text-blue-700">{{ diagnosis.diagnosis_name }}</p>
          </div>
          <div v-if="diagnosis.icd_code">
            <p class="text-xs text-gray-400 font-medium uppercase mb-1">Kode ICD-10</p>
            <span class="font-mono bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-sm">
              {{ diagnosis.icd_code }}
            </span>
          </div>
        </div>

        <div v-if="diagnosis.treatment">
          <p class="text-xs text-gray-400 font-medium uppercase mb-1">Penanganan / Resep</p>
          <p class="text-sm text-gray-800 whitespace-pre-line leading-relaxed bg-blue-50 rounded-xl p-3 border border-blue-100">
            {{ diagnosis.treatment }}
          </p>
        </div>

        <div v-if="diagnosis.notes">
          <p class="text-xs text-gray-400 font-medium uppercase mb-1">Catatan Dokter</p>
          <p class="text-sm text-gray-600 whitespace-pre-line leading-relaxed">{{ diagnosis.notes }}</p>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  diagnosis: { type: Object, required: true },
})

function formatDate(val) {
  if (!val) return '—'
  const d = new Date(String(val).substring(0, 10))
  return new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' }).format(d)
}
</script>

<template>
  <AppLayout title="Detail Diagnosa">
    <div class="max-w-2xl space-y-5">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Detail Diagnosa</h2>
          <p class="text-sm text-gray-500 mt-0.5">{{ formatDate(diagnosis.diagnosis_date) }}</p>
        </div>
        <div class="flex gap-2">
          <Link :href="route('doctor.diagnoses.edit', diagnosis.id)"
            class="px-4 py-2 text-sm font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl transition-colors">
            Edit
          </Link>
          <Link :href="route('doctor.diagnoses.index')"
            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
            Kembali
          </Link>
        </div>
      </div>

      <!-- Identitas Pasien -->
      <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-700 text-sm uppercase tracking-wide mb-4">Identitas Pasien</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-xs text-gray-400 font-medium uppercase mb-0.5">Nama</p>
            <p class="text-gray-800 font-semibold">{{ diagnosis.patient?.name }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-400 font-medium uppercase mb-0.5">No. Rekam Medis</p>
            <p class="text-gray-700 font-mono">{{ diagnosis.patient?.medical_record_number }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-400 font-medium uppercase mb-0.5">Orang Tua / Wali</p>
            <p class="text-gray-700">{{ diagnosis.patient?.guardian?.name ?? '—' }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-400 font-medium uppercase mb-0.5">Dokter Pemeriksa</p>
            <p class="text-gray-700">Dr. {{ diagnosis.doctor?.name }}</p>
          </div>
        </div>
      </div>

      <!-- Gejala & Diagnosa -->
      <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
        <h3 class="font-semibold text-gray-700 text-sm uppercase tracking-wide">Gejala & Diagnosa</h3>

        <div>
          <p class="text-xs text-gray-400 font-medium uppercase mb-1">Gejala yang Dialami</p>
          <p class="text-sm text-gray-800 whitespace-pre-line leading-relaxed">{{ diagnosis.symptoms }}</p>
        </div>

        <div class="grid grid-cols-3 gap-4">
          <div class="col-span-2">
            <p class="text-xs text-gray-400 font-medium uppercase mb-1">Diagnosa Penyakit</p>
            <p class="text-base font-semibold text-blue-700">{{ diagnosis.diagnosis_name }}</p>
          </div>
          <div v-if="diagnosis.icd_code">
            <p class="text-xs text-gray-400 font-medium uppercase mb-1">Kode ICD-10</p>
            <span class="text-sm font-mono bg-gray-100 text-gray-700 px-2 py-1 rounded-lg">
              {{ diagnosis.icd_code }}
            </span>
          </div>
        </div>
      </div>

      <!-- Penanganan & Catatan -->
      <div v-if="diagnosis.treatment || diagnosis.notes" class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
        <h3 class="font-semibold text-gray-700 text-sm uppercase tracking-wide">Penanganan & Catatan</h3>

        <div v-if="diagnosis.treatment">
          <p class="text-xs text-gray-400 font-medium uppercase mb-1">Penanganan / Resep</p>
          <p class="text-sm text-gray-800 whitespace-pre-line leading-relaxed">{{ diagnosis.treatment }}</p>
        </div>

        <div v-if="diagnosis.notes">
          <p class="text-xs text-gray-400 font-medium uppercase mb-1">Catatan Tambahan</p>
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

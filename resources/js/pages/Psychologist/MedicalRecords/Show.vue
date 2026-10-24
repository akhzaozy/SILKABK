<template>
  <AppLayout title="Detail Asesmen Psikologi">
    <div class="max-w-2xl space-y-5">

      <!-- Header -->
      <div class="flex items-center gap-3">
        <Link :href="route('psychologist.medical-records')" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
          <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div class="flex-1">
          <h2 class="text-lg font-semibold text-gray-800">Detail Asesmen Psikologi</h2>
          <p class="text-sm text-gray-500">{{ formatDate(record.visit_date) }}</p>
        </div>
        <Link :href="route('psychologist.medical-records.edit', record.id)"
          class="px-3 py-1.5 text-sm font-medium text-blue-600 border border-blue-300 rounded-xl hover:bg-blue-50 transition-colors">
          Edit
        </Link>
      </div>

      <!-- Pasien & Psikolog -->
      <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 pb-3 mb-4 border-b border-gray-100">Informasi Asesmen</h3>
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Nama Pasien</dt>
            <dd class="mt-0.5 text-sm font-medium text-gray-800">{{ record.patient?.name }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">No. Rekam Medis</dt>
            <dd class="mt-0.5 text-sm font-mono text-gray-800">{{ record.patient?.medical_record_number }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Tanggal Kunjungan</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ formatDate(record.visit_date) }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Psikolog</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ record.psychologist?.nama }}</dd>
          </div>
        </dl>
      </div>

      <!-- Hasil Asesmen -->
      <div class="bg-white rounded-2xl border border-gray-200 p-5 space-y-4">
        <h3 class="font-semibold text-gray-800 pb-3 border-b border-gray-100">Hasil Asesmen</h3>
        <div>
          <p class="text-xs font-medium text-gray-500 uppercase mb-0.5">Keluhan / Rujukan</p>
          <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ record.chief_complaint }}</p>
        </div>
        <div>
          <p class="text-xs font-medium text-gray-500 uppercase mb-0.5">Hasil Asesmen / Kesimpulan</p>
          <p class="text-sm text-gray-800 font-medium whitespace-pre-wrap">{{ record.diagnosis }}</p>
        </div>
        <div v-if="record.treatment_plan">
          <p class="text-xs font-medium text-gray-500 uppercase mb-0.5">Rekomendasi / Rencana Intervensi</p>
          <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ record.treatment_plan }}</p>
        </div>
        <div v-if="record.notes">
          <p class="text-xs font-medium text-gray-500 uppercase mb-0.5">Catatan Tambahan</p>
          <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ record.notes }}</p>
        </div>
      </div>

      <!-- KPSP -->
      <div v-if="record.kpsp_result" class="bg-purple-50 border border-purple-200 rounded-2xl p-5">
        <h3 class="font-semibold text-purple-900 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Hasil Skrining KPSP
        </h3>
        <dl class="grid grid-cols-2 gap-4">
          <div v-if="record.kpsp_result.usia_bulan">
            <dt class="text-xs font-medium text-purple-600 uppercase">Usia Skrining</dt>
            <dd class="mt-0.5 text-sm text-purple-900 font-medium">{{ record.kpsp_result.usia_bulan }} bulan</dd>
          </div>
          <div v-if="record.kpsp_result.skor !== undefined">
            <dt class="text-xs font-medium text-purple-600 uppercase">Skor</dt>
            <dd class="mt-0.5 text-sm text-purple-900 font-medium">{{ record.kpsp_result.skor }} / 10</dd>
          </div>
          <div v-if="record.kpsp_result.tanggal_screening">
            <dt class="text-xs font-medium text-purple-600 uppercase">Tanggal Skrining</dt>
            <dd class="mt-0.5 text-sm text-purple-900">{{ formatDate(record.kpsp_result.tanggal_screening) }}</dd>
          </div>
          <div v-if="record.kpsp_result.interpretasi" class="col-span-2">
            <dt class="text-xs font-medium text-purple-600 uppercase">Interpretasi</dt>
            <dd class="mt-0.5 text-sm font-semibold"
              :class="record.kpsp_result.interpretasi === 'Sesuai' ? 'text-green-700'
                : record.kpsp_result.interpretasi === 'Meragukan' ? 'text-yellow-700'
                : 'text-red-700'">
              {{ record.kpsp_result.interpretasi }}
            </dd>
          </div>
          <div v-if="record.kpsp_result.catatan" class="col-span-2">
            <dt class="text-xs font-medium text-purple-600 uppercase">Catatan KPSP</dt>
            <dd class="mt-0.5 text-sm text-purple-900">{{ record.kpsp_result.catatan }}</dd>
          </div>
        </dl>
      </div>

      <div v-else class="bg-gray-50 border border-gray-200 rounded-2xl p-4 flex items-center justify-between">
        <p class="text-sm text-gray-600">Data skrining KPSP belum diisi</p>
        <Link :href="route('psychologist.medical-records.edit', record.id)"
          class="text-sm font-medium text-purple-600 hover:text-purple-800 px-3 py-1.5 rounded-lg hover:bg-purple-50 transition-colors">
          Tambah KPSP
        </Link>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

defineProps({
  record: { type: Object, required: true },
})

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}
</script>

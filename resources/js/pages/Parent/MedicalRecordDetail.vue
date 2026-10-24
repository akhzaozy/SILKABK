<template>
  <AppLayout title="Detail Rekam Medis">
    <div class="max-w-2xl space-y-5">

      <!-- Header -->
      <div class="flex items-center gap-3">
        <Link :href="route('parent.medical-records')" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
          <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Detail Rekam Medis</h2>
          <p class="text-sm text-gray-500">{{ formatDate(record.visit_date) }}</p>
        </div>
      </div>

      <!-- Pasien -->
      <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 pb-3 mb-4 border-b border-gray-100">Informasi Pasien & Pemeriksaan</h3>
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Nama Anak</dt>
            <dd class="mt-0.5 text-sm font-medium text-gray-800">{{ record.patient?.name }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Tanggal Pemeriksaan</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ formatDate(record.visit_date) }}</dd>
          </div>
          <div v-if="record.doctor">
            <dt class="text-xs font-medium text-gray-500 uppercase">Dokter Pemeriksa</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ record.doctor?.name }}</dd>
          </div>
          <div v-if="record.psychologist">
            <dt class="text-xs font-medium text-gray-500 uppercase">Psikolog</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ record.psychologist?.nama }}</dd>
          </div>
          <div v-if="record.icd_code">
            <dt class="text-xs font-medium text-gray-500 uppercase">Kode ICD-10</dt>
            <dd class="mt-0.5 text-sm font-mono text-gray-800">{{ record.icd_code }}</dd>
          </div>
        </dl>
      </div>

      <!-- Rekam Medis -->
      <div class="bg-white rounded-2xl border border-gray-200 p-5 space-y-4">
        <h3 class="font-semibold text-gray-800 pb-3 border-b border-gray-100">Hasil Pemeriksaan</h3>
        <div>
          <p class="text-xs font-medium text-gray-500 uppercase mb-0.5">Keluhan Utama</p>
          <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ record.chief_complaint }}</p>
        </div>
        <div v-if="record.physical_examination">
          <p class="text-xs font-medium text-gray-500 uppercase mb-0.5">Pemeriksaan Fisik</p>
          <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ record.physical_examination }}</p>
        </div>
        <div>
          <p class="text-xs font-medium text-gray-500 uppercase mb-0.5">Diagnosis</p>
          <p class="text-sm text-gray-800 font-medium whitespace-pre-wrap">{{ record.diagnosis }}</p>
        </div>
        <div v-if="record.treatment_plan">
          <p class="text-xs font-medium text-gray-500 uppercase mb-0.5">Rencana Tatalaksana</p>
          <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ record.treatment_plan }}</p>
        </div>
        <div v-if="record.notes">
          <p class="text-xs font-medium text-gray-500 uppercase mb-0.5">Catatan Tambahan</p>
          <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ record.notes }}</p>
        </div>
      </div>

      <!-- KPSP Section -->
      <div v-if="record.kpsp_result" class="bg-purple-50 border border-purple-200 rounded-2xl p-5">
        <h3 class="font-semibold text-purple-900 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Hasil Screening KPSP
        </h3>
        <dl class="grid grid-cols-2 gap-4">
          <div v-if="record.kpsp_result.usia_bulan">
            <dt class="text-xs font-medium text-purple-600 uppercase">Usia Screening</dt>
            <dd class="mt-0.5 text-sm text-purple-900 font-medium">{{ record.kpsp_result.usia_bulan }} bulan</dd>
          </div>
          <div v-if="record.kpsp_result.skor !== undefined">
            <dt class="text-xs font-medium text-purple-600 uppercase">Skor</dt>
            <dd class="mt-0.5 text-sm text-purple-900 font-medium">{{ record.kpsp_result.skor }} / 10</dd>
          </div>
          <div v-if="record.kpsp_result.tanggal_screening">
            <dt class="text-xs font-medium text-purple-600 uppercase">Tanggal Screening</dt>
            <dd class="mt-0.5 text-sm text-purple-900">{{ formatDate(record.kpsp_result.tanggal_screening) }}</dd>
          </div>
          <div v-if="record.kpsp_result.interpretasi" class="col-span-2">
            <dt class="text-xs font-medium text-purple-600 uppercase">Interpretasi</dt>
            <dd class="mt-0.5 text-sm font-semibold"
              :class="record.kpsp_result.interpretasi === 'Sesuai' ? 'text-green-700' : 'text-orange-700'">
              {{ record.kpsp_result.interpretasi }}
            </dd>
          </div>
        </dl>
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

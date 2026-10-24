<template>
  <AppLayout title="Laporan">
    <div class="space-y-6">

      <!-- Header & Filter -->
      <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Laporan</h2>
          <p class="text-sm text-gray-500 mt-0.5">Ringkasan data operasional klinik</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
          <input v-model="f.from" type="date" @change="applyFilter"
            class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <span class="text-gray-400 text-sm">s/d</span>
          <input v-model="f.to" type="date" @change="applyFilter"
            class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <a :href="exportReportUrl"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export Excel
          </a>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Pasien Aktif</p>
          <p class="text-3xl font-bold text-gray-800 mt-1">{{ summary.totalPatients }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Pendaftaran (periode)</p>
          <p class="text-3xl font-bold text-gray-800 mt-1">{{ summary.totalRegistrations }}</p>
          <p v-if="summary.pendingRegistrations" class="text-xs text-amber-600 mt-1">{{ summary.pendingRegistrations }} pending</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Pendapatan (periode)</p>
          <p class="text-2xl font-bold text-green-700 mt-1">{{ formatRupiah(summary.totalRevenue) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Sesi Selesai (periode)</p>
          <p class="text-3xl font-bold text-gray-800 mt-1">{{ summary.completedSchedules }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Slot Tersedia (mendatang)</p>
          <p class="text-3xl font-bold text-blue-600 mt-1">{{ summary.availableSlots }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Pendaftaran per Status -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
          <h3 class="font-semibold text-gray-800 mb-4">Pendaftaran per Status <span class="text-xs font-normal text-gray-400">(periode)</span></h3>
          <div class="space-y-3">
            <div v-for="item in statusRows" :key="item.key" class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span :class="item.dot" class="w-2.5 h-2.5 rounded-full flex-shrink-0"/>
                <span class="text-sm text-gray-700">{{ item.label }}</span>
              </div>
              <span class="text-sm font-semibold text-gray-800">
                {{ registrationsByStatus[item.key]?.total ?? 0 }}
              </span>
            </div>
            <div v-if="!Object.keys(registrationsByStatus).length" class="text-sm text-gray-400 text-center py-4">
              Belum ada data pada periode ini
            </div>
          </div>
        </div>

        <!-- Jadwal per Tipe Profesional -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
          <h3 class="font-semibold text-gray-800 mb-4">Jadwal per Profesional <span class="text-xs font-normal text-gray-400">(periode)</span></h3>
          <div class="space-y-4">
            <div v-for="(rows, type) in schedulesByType" :key="type">
              <p class="text-xs font-semibold text-gray-500 uppercase mb-2 capitalize">{{ type }}</p>
              <div class="space-y-1.5 pl-3">
                <div v-for="row in rows" :key="row.status" class="flex items-center justify-between text-sm">
                  <span class="text-gray-600">{{ statusLabel(row.status) }}</span>
                  <span class="font-semibold text-gray-800">{{ row.total }}</span>
                </div>
              </div>
            </div>
            <div v-if="!Object.keys(schedulesByType).length" class="text-sm text-gray-400 text-center py-4">
              Belum ada data pada periode ini
            </div>
          </div>
        </div>

      </div>

      <!-- Pendapatan 6 Bulan Terakhir -->
      <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Pendapatan 6 Bulan Terakhir</h3>
        <div class="overflow-x-auto">
          <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100">
                <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Bulan</th>
                <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Transaksi</th>
                <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Total Pendapatan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-if="revenueByMonth.length === 0">
                <td colspan="3" class="py-8 text-center text-gray-400">Belum ada data pembayaran</td>
              </tr>
              <tr v-for="row in revenueByMonth" :key="row.month" class="hover:bg-gray-50">
                <td class="py-3 px-3 text-gray-700 font-medium">{{ formatMonth(row.month) }}</td>
                <td class="py-3 px-3 text-right text-gray-600">{{ row.count }}</td>
                <td class="py-3 px-3 text-right font-semibold text-green-700">{{ formatRupiah(row.total) }}</td>
              </tr>
            </tbody>
            </table>
        </div>
        </div>
      </div>

      <!-- Pendaftaran per Bulan -->
      <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Pendaftaran 6 Bulan Terakhir</h3>
        <div class="overflow-x-auto">
          <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100">
                <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Bulan</th>
                <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Jumlah Pendaftaran</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-if="registrationsByMonth.length === 0">
                <td colspan="2" class="py-8 text-center text-gray-400">Belum ada data pendaftaran</td>
              </tr>
              <tr v-for="row in registrationsByMonth" :key="row.month" class="hover:bg-gray-50">
                <td class="py-3 px-3 text-gray-700 font-medium">{{ formatMonth(row.month) }}</td>
                <td class="py-3 px-3 text-right font-semibold text-blue-700">{{ row.total }}</td>
              </tr>
            </tbody>
            </table>
        </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  summary:               { type: Object, default: () => ({}) },
  registrationsByStatus: { type: Object, default: () => ({}) },
  revenueByMonth:        { type: Array,  default: () => [] },
  schedulesByType:       { type: Object, default: () => ({}) },
  registrationsByMonth:  { type: Array,  default: () => [] },
  filters:               { type: Object, default: () => ({}) },
})

const f = reactive({
  from: props.filters.from ?? '',
  to:   props.filters.to   ?? '',
})

function applyFilter() {
  if (f.from && f.to) {
    router.get(route('reports.index'), { from: f.from, to: f.to }, { preserveState: true, replace: true })
  }
}

const exportReportUrl = computed(() => {
  const params = new URLSearchParams()
  if (f.from) params.set('from', f.from)
  if (f.to)   params.set('to',   f.to)
  return route('export.report') + (params.toString() ? '?' + params.toString() : '')
})

const statusRows = [
  { key: 'pending',  label: 'Pending',   dot: 'bg-amber-400' },
  { key: 'approved', label: 'Disetujui', dot: 'bg-green-500' },
  { key: 'rejected', label: 'Ditolak',   dot: 'bg-red-400' },
]

const scheduleStatusMap = {
  available: 'Slot Tersedia',
  scheduled: 'Dijadwalkan',
  done:      'Selesai',
  cancelled: 'Dibatalkan',
}
function statusLabel(s) { return scheduleStatusMap[s] ?? s }

function formatRupiah(val) {
  return 'Rp ' + Number(val ?? 0).toLocaleString('id-ID')
}

function formatMonth(ym) {
  if (!ym) return '—'
  const [year, month] = ym.split('-')
  const d = new Date(Number(year), Number(month) - 1, 1)
  return d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })
}
</script>

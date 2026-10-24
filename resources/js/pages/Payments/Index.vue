<template>
  <AppLayout title="Laporan Pembayaran">
    <div class="space-y-5">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Laporan Pembayaran</h2>
          <p class="text-sm text-gray-500 mt-0.5">Riwayat seluruh transaksi pembayaran layanan terapi</p>
        </div>
        <div class="flex items-center gap-2">
          <a :href="route('export.payments')"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-green-700 border border-green-300 bg-green-50 hover:bg-green-100 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export Excel
          </a>
          <Link
            :href="route('payments.create')"
            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Catat Pembayaran
          </Link>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <SummaryCard
          label="Total Lunas"
          :value="formatRupiah(summary.total_paid)"
          :count="summary.count_paid + ' transaksi'"
          color="green"
        />
        <SummaryCard
          label="Belum Lunas"
          :value="formatRupiah(summary.total_unpaid)"
          :count="summary.count_unpaid + ' transaksi'"
          color="red"
        />
        <SummaryCard
          label="Total Transaksi"
          :value="payments.total"
          count="semua status"
          color="blue"
        />
        <SummaryCard
          label="Total Nilai"
          :value="formatRupiah(summary.total_paid + summary.total_unpaid)"
          count="keseluruhan"
          color="purple"
        />
      </div>

      <!-- Filter -->
      <div class="bg-white rounded-2xl border border-gray-200 p-4">
        <div class="flex flex-wrap gap-3">
          <!-- Search -->
          <div class="relative flex-1 min-w-48">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </span>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Kode atau nama pasien..."
              class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
              @input="debouncedApply"
            />
          </div>

          <!-- Status -->
          <select v-model="filters.status" @change="applyFilters"
            class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="paid">Lunas</option>
            <option value="unpaid">Belum Lunas</option>
          </select>

          <!-- Metode -->
          <select v-model="filters.method" @change="applyFilters"
            class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Metode</option>
            <option value="Cash">Cash</option>
            <option value="Transfer">Transfer</option>
          </select>

          <!-- Tanggal -->
          <input v-model="filters.date_from" type="date" @change="applyFilters"
            class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"/>
          <input v-model="filters.date_to" type="date" @change="applyFilters"
            class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"/>

          <button v-if="hasFilter" @click="resetFilters"
            class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded-xl hover:bg-gray-100 transition-colors">
            Reset
          </button>
        </div>
      </div>

      <!-- Tabel -->
      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100 bg-gray-50">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kode</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pasien</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jumlah</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Metode</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-if="payments.data.length === 0">
                <td colspan="7" class="px-5 py-12 text-center text-gray-400 text-sm">
                  Tidak ada data pembayaran ditemukan
                </td>
              </tr>
              <tr v-for="p in payments.data" :key="p.id" class="hover:bg-gray-50/50 transition-colors">
                <td class="px-5 py-4">
                  <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-lg">
                    {{ p.payment_code }}
                  </span>
                </td>
                <td class="px-5 py-4">
                  <p class="font-medium text-gray-800">{{ p.patient?.name }}</p>
                  <p class="text-xs text-gray-400">{{ p.patient?.medical_record_number }}</p>
                </td>
                <td class="px-5 py-4 text-gray-600">{{ formatDate(p.payment_date) }}</td>
                <td class="px-5 py-4 font-semibold text-gray-800">{{ formatRupiah(p.amount) }}</td>
                <td class="px-5 py-4">
                  <span :class="p.payment_method === 'Transfer'
                    ? 'bg-blue-50 text-blue-700 border-blue-200'
                    : 'bg-gray-50 text-gray-700 border-gray-200'"
                    class="text-xs font-medium px-2 py-1 rounded-full border">
                    {{ p.payment_method }}
                  </span>
                </td>
                <td class="px-5 py-4">
                  <span :class="p.status === 'paid'
                    ? 'bg-green-50 text-green-700 border-green-200'
                    : 'bg-yellow-50 text-yellow-700 border-yellow-200'"
                    class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-1 rounded-full border">
                    <span :class="p.status === 'paid' ? 'bg-green-500' : 'bg-yellow-400'"
                      class="w-1.5 h-1.5 rounded-full"/>
                    {{ p.status === 'paid' ? 'Lunas' : 'Belum Lunas' }}
                  </span>
                </td>
                <td class="px-5 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="route('payments.show', p.id)"
                      class="text-xs font-medium text-gray-600 hover:text-gray-900 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                      Detail
                    </Link>
                    <Link :href="route('payments.edit', p.id)"
                      class="text-xs font-medium text-blue-600 hover:text-blue-800 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-colors">
                      Edit
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="payments.last_page > 1"
          class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
          <p class="text-xs text-gray-500">
            {{ payments.from }}–{{ payments.to }} dari {{ payments.total }} transaksi
          </p>
          <div class="flex gap-1">
            <Link v-for="link in payments.links" :key="link.label"
              :href="link.url ?? '#'"
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

    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SummaryCard from '@/components/SummaryCard.vue'

const props = defineProps({
  payments: { type: Object, required: true },
  summary:  { type: Object, required: true },
  filters:  { type: Object, default: () => ({}) },
})

const filters = reactive({
  search:    props.filters.search    ?? '',
  status:    props.filters.status    ?? '',
  method:    props.filters.method    ?? '',
  date_from: props.filters.date_from ?? '',
  date_to:   props.filters.date_to   ?? '',
})

const hasFilter = computed(() =>
  Object.values(filters).some(Boolean)
)

let debounceTimer = null
function debouncedApply() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(applyFilters, 400)
}

function applyFilters() {
  const query = {}
  Object.entries(filters).forEach(([k, v]) => { if (v) query[k] = v })
  router.get(route('payments.index'), query, { preserveState: true, replace: true })
}

function resetFilters() {
  Object.keys(filters).forEach(k => filters[k] = '')
  applyFilters()
}

function formatRupiah(val) {
  return 'Rp ' + Number(val ?? 0).toLocaleString('id-ID')
}

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>
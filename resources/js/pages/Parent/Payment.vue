<template>
  <AppLayout title="Riwayat Pembayaran">
    <div class="space-y-5">

      <div>
        <h2 class="text-lg font-semibold text-gray-800">Riwayat Pembayaran</h2>
        <p class="text-sm text-gray-500 mt-0.5">Daftar transaksi pembayaran layanan terapi anak Anda</p>
      </div>

      <!-- Filter status -->
      <div class="flex gap-2">
        <button v-for="opt in statusOptions" :key="opt.value"
          @click="setStatus(opt.value)"
          :class="[
            'px-4 py-2 rounded-xl text-sm font-medium transition-colors border',
            filters.status === opt.value
              ? 'bg-blue-600 text-white border-blue-600'
              : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300',
          ]">
          {{ opt.label }}
        </button>
      </div>

      <!-- Empty state -->
      <div v-if="payments.data.length === 0"
        class="bg-white rounded-2xl border border-gray-200 px-6 py-14 text-center">
        <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
          </svg>
        </div>
        <p class="text-gray-500 text-sm">Belum ada riwayat pembayaran</p>
      </div>

      <!-- List pembayaran -->
      <div v-else class="space-y-3">
        <Link
          v-for="p in payments.data"
          :key="p.id"
          :href="route('parent.payments.show', p.id)"
          class="block bg-white rounded-2xl border border-gray-200 hover:border-blue-200 hover:shadow-sm transition-all p-5"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
              <!-- Kode + terapi -->
              <div class="flex items-center gap-2 mb-1">
                <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-lg">
                  {{ p.payment_code }}
                </span>
                <span v-if="p.schedule" class="text-xs text-gray-400">
                  {{ p.schedule.therapy_type?.name }}
                </span>
              </div>
              <!-- Pasien + tanggal -->
              <p class="font-medium text-gray-800">{{ p.patient?.name }}</p>
              <p class="text-sm text-gray-400 mt-0.5">{{ formatDate(p.payment_date) }} · {{ p.payment_method }}</p>
            </div>

            <div class="text-right flex-shrink-0">
              <p class="font-bold text-gray-800">{{ formatRupiah(p.amount) }}</p>
              <span :class="p.status === 'paid'
                ? 'bg-green-50 text-green-700 border-green-200'
                : 'bg-yellow-50 text-yellow-700 border-yellow-200'"
                class="inline-flex items-center gap-1 mt-1 text-xs font-medium px-2 py-0.5 rounded-full border">
                <span :class="p.status === 'paid' ? 'bg-green-500' : 'bg-yellow-400'"
                  class="w-1.5 h-1.5 rounded-full"/>
                {{ p.status === 'paid' ? 'Lunas' : 'Belum Lunas' }}
              </span>
            </div>
          </div>
        </Link>
      </div>

      <!-- Pagination -->
      <div v-if="payments.last_page > 1" class="flex justify-center gap-1">
        <Link v-for="link in payments.links" :key="link.label"
          :href="link.url ?? '#'"
          :class="[
            'px-3 py-1.5 text-xs rounded-lg transition-colors',
            link.active ? 'bg-blue-600 text-white font-semibold'
              : link.url ? 'text-gray-600 hover:bg-gray-100 bg-white border border-gray-200'
              : 'text-gray-300 cursor-not-allowed',
          ]"
          v-html="link.label"
        />
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  payments: { type: Object, required: true },
  filters:  { type: Object, default: () => ({}) },
})

const filters = reactive({
  status: props.filters.status ?? '',
})

const statusOptions = [
  { label: 'Semua',       value: '' },
  { label: 'Lunas',       value: 'paid' },
  { label: 'Belum Lunas', value: 'unpaid' },
]

function setStatus(val) {
  filters.status = val
  router.get(route('parent.payments'), { status: val || undefined }, {
    preserveState: true, replace: true,
  })
}

function formatRupiah(val) {
  return 'Rp ' + Number(val ?? 0).toLocaleString('id-ID')
}

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>
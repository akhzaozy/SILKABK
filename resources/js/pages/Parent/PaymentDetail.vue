<template>
  <AppLayout title="Bukti Pembayaran">
    <div class="max-w-lg mx-auto">

      <div class="flex items-center gap-3 mb-6">
        <Link :href="route('parent.payments')"
          class="p-2 rounded-xl hover:bg-gray-100 text-gray-500 hover:text-gray-700 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <h2 class="text-lg font-semibold text-gray-800">Bukti Pembayaran</h2>
      </div>

      <!-- Bukti / Receipt Card -->
      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">

        <!-- Header receipt -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-6 text-center">
          <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <p class="text-white/80 text-sm">UPTD BLKABK Balikpapan</p>
          <p class="text-white font-semibold text-lg mt-1">Bukti Pembayaran Layanan Terapi</p>
        </div>

        <!-- Status besar -->
        <div :class="payment.status === 'paid'
          ? 'bg-green-50 border-green-100'
          : 'bg-yellow-50 border-yellow-100'"
          class="border-b px-6 py-4 text-center">
          <span :class="payment.status === 'paid'
            ? 'text-green-700 bg-green-100'
            : 'text-yellow-700 bg-yellow-100'"
            class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-1.5 rounded-full">
            <span :class="payment.status === 'paid' ? 'bg-green-500' : 'bg-yellow-400'"
              class="w-2 h-2 rounded-full"/>
            {{ payment.status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
          </span>
          <p :class="payment.status === 'paid' ? 'text-green-800' : 'text-yellow-800'"
            class="text-3xl font-bold mt-2">
            {{ formatRupiah(payment.amount) }}
          </p>
        </div>

        <!-- Detail rows -->
        <div class="divide-y divide-gray-50">
          <ReceiptRow label="Kode Transaksi">
            <span class="font-mono text-sm">{{ payment.payment_code }}</span>
          </ReceiptRow>
          <ReceiptRow label="Nama Pasien">
            {{ payment.patient?.name }}
          </ReceiptRow>
          <ReceiptRow label="No. Rekam Medis">
            {{ payment.patient?.medical_record_number }}
          </ReceiptRow>
          <ReceiptRow label="Tanggal Bayar">
            {{ formatDate(payment.payment_date) }}
          </ReceiptRow>
          <ReceiptRow label="Metode Bayar">
            {{ payment.payment_method }}
          </ReceiptRow>
          <ReceiptRow v-if="payment.schedule" label="Sesi Terapi">
            {{ payment.schedule.therapy_type?.name }}
            <span class="text-gray-400 text-xs block">{{ formatDate(payment.schedule.schedule_date) }}</span>
          </ReceiptRow>
          <ReceiptRow v-if="payment.notes" label="Catatan">
            {{ payment.notes }}
          </ReceiptRow>
          <ReceiptRow label="Dicatat oleh">
            {{ payment.created_by?.name }}
          </ReceiptRow>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 text-center">
          <p class="text-xs text-gray-400">
            Dokumen ini merupakan bukti pembayaran resmi yang tercatat dalam sistem
            UPTD BLKABK Balikpapan.
          </p>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

defineProps({
  payment: { type: Object, required: true },
})

function formatRupiah(val) {
  return 'Rp ' + Number(val ?? 0).toLocaleString('id-ID')
}

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}

// ReceiptRow — inline component kecil
const ReceiptRow = {
  props: ['label'],
  template: `
    <div class="px-6 py-3 flex items-start justify-between gap-4">
      <p class="text-sm text-gray-400 flex-shrink-0">{{ label }}</p>
      <div class="text-sm text-gray-800 font-medium text-right">
        <slot />
      </div>
    </div>
  `,
}
</script>
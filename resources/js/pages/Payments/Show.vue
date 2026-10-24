<template>
  <AppLayout title="Detail Pembayaran">
    <div class="max-w-2xl">

      <!-- Back + Header -->
      <div class="flex items-center gap-3 mb-6">
        <Link :href="route('payments.index')"
          class="p-2 rounded-xl hover:bg-gray-100 text-gray-500 hover:text-gray-700 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div class="flex-1">
          <h2 class="text-lg font-semibold text-gray-800">Detail Pembayaran</h2>
          <p class="text-sm text-gray-500 font-mono">{{ payment.payment_code }}</p>
        </div>
        <Link :href="route('payments.edit', payment.id)"
          class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-800 px-4 py-2 rounded-xl hover:bg-blue-50 transition-colors">
          Edit
        </Link>
      </div>

      <!-- Status Badge besar -->
      <div :class="payment.status === 'paid'
        ? 'bg-green-50 border-green-200'
        : 'bg-yellow-50 border-yellow-200'"
        class="border rounded-2xl p-5 mb-5 flex items-center gap-4">
        <div :class="payment.status === 'paid'
          ? 'bg-green-100 text-green-700'
          : 'bg-yellow-100 text-yellow-700'"
          class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              :d="payment.status === 'paid'
                ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                : 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'"/>
          </svg>
        </div>
        <div>
          <p :class="payment.status === 'paid' ? 'text-green-800' : 'text-yellow-800'"
            class="font-semibold text-base">
            {{ payment.status === 'paid' ? 'Pembayaran Lunas' : 'Belum Lunas' }}
          </p>
          <p :class="payment.status === 'paid' ? 'text-green-600' : 'text-yellow-600'"
            class="text-2xl font-bold mt-0.5">
            {{ formatRupiah(payment.amount) }}
          </p>
        </div>
      </div>

      <!-- Detail Card -->
      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
          <h3 class="text-sm font-semibold text-gray-700">Informasi Transaksi</h3>
        </div>
        <div class="divide-y divide-gray-50">
          <DetailRow label="Kode Pembayaran">
            <span class="font-mono text-sm bg-gray-100 text-gray-700 px-2 py-0.5 rounded-lg">
              {{ payment.payment_code }}
            </span>
          </DetailRow>
          <DetailRow label="Pasien">
            <div>
              <p class="font-medium text-gray-800">{{ payment.patient?.name }}</p>
              <p class="text-xs text-gray-400">{{ payment.patient?.medical_record_number }}</p>
            </div>
          </DetailRow>
          <DetailRow label="Tanggal Pembayaran">
            {{ formatDate(payment.payment_date) }}
          </DetailRow>
          <DetailRow label="Metode Pembayaran">
            <span :class="payment.payment_method === 'Transfer'
              ? 'bg-blue-50 text-blue-700 border-blue-200'
              : 'bg-gray-50 text-gray-700 border-gray-200'"
              class="text-xs font-medium px-2 py-1 rounded-full border">
              {{ payment.payment_method }}
            </span>
          </DetailRow>
          <DetailRow v-if="payment.schedule" label="Sesi Terapi Terkait">
            <div>
              <p class="text-gray-800">{{ payment.schedule?.therapy_type?.name }}</p>
              <p class="text-xs text-gray-400">{{ formatDate(payment.schedule?.schedule_date) }}</p>
            </div>
          </DetailRow>
          <DetailRow v-if="payment.notes" label="Catatan">
            {{ payment.notes }}
          </DetailRow>
          <DetailRow label="Dicatat oleh">
            {{ payment.created_by?.name }} — {{ formatDate(payment.created_at) }}
          </DetailRow>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import DetailRow from '@/components/DetailRow.vue'

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
</script>
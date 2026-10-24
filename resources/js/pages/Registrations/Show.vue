<template>
  <AppLayout title="Detail Pendaftaran">
    <div class="max-w-2xl space-y-5">

      <!-- Header -->
      <div class="flex items-center gap-3">
        <Link :href="route('registrations.index')" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
          <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Detail Pendaftaran</h2>
          <p class="text-sm text-gray-500">{{ formatDate(registration.registration_date) }}</p>
        </div>
      </div>

      <!-- Status + Actions -->
      <div class="flex items-center gap-3">
        <span :class="statusBadge(registration.status)" class="inline-flex items-center gap-1.5 text-sm font-medium px-3 py-1.5 rounded-full border">
          <span :class="statusDot(registration.status)" class="w-2 h-2 rounded-full"/>
          {{ statusLabel(registration.status) }}
        </span>
        <span v-if="registration.patient_type === 'new'" class="text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200 px-2 py-1 rounded-full">
          Pasien Baru
        </span>
        <template v-if="registration.status === 'pending'">
          <button @click="approve"
            class="px-4 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
            Setujui & Konfirmasi Jadwal
          </button>
          <button @click="rejectDialog = true"
            class="px-4 py-1.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
            Tolak
          </button>
        </template>
      </div>

      <!-- Data Pasien -->
      <div class="bg-white rounded-2xl border border-gray-200 p-5 space-y-4">
        <h3 class="font-semibold text-gray-800 pb-2 border-b border-gray-100">Data Pasien</h3>
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Nama</dt>
            <dd class="mt-0.5 text-sm text-gray-800 font-medium">{{ registration.patient?.name }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">No. Rekam Medis</dt>
            <dd class="mt-0.5 text-sm font-mono text-gray-800">{{ registration.patient?.medical_record_number }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Nama Wali</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ registration.patient?.guardian?.name }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Telepon Wali</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ registration.patient?.guardian?.phone || '—' }}</dd>
          </div>
        </dl>
      </div>

      <!-- Slot Jadwal yang Dipilih -->
      <div v-if="registration.schedule" class="bg-white rounded-2xl border border-gray-200 p-5 space-y-4">
        <h3 class="font-semibold text-gray-800 pb-2 border-b border-gray-100">Slot Jadwal yang Dipilih</h3>
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Tanggal</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ formatDate(registration.schedule.schedule_date) }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Window Waktu</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ registration.schedule.start_time }} – {{ registration.schedule.end_time }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Tenaga Profesional</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ getProfessional(registration.schedule) }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Jenis Layanan</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ registration.therapy_type?.name }}</dd>
          </div>
        </dl>
      </div>

      <!-- Detail Pendaftaran -->
      <div class="bg-white rounded-2xl border border-gray-200 p-5 space-y-4">
        <h3 class="font-semibold text-gray-800 pb-2 border-b border-gray-100">Detail Pendaftaran</h3>
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Durasi Dipilih</dt>
            <dd class="mt-0.5 text-sm text-gray-800 font-medium">{{ registration.duration_hours }} jam</dd>
          </div>
          <div v-if="registration.therapy_type?.price_per_hour">
            <dt class="text-xs font-medium text-gray-500 uppercase">Estimasi Biaya</dt>
            <dd class="mt-0.5 text-sm text-gray-800 font-semibold text-blue-700">
              {{ formatRupiah((registration.therapy_type.price_per_hour ?? 0) * (registration.duration_hours ?? 1)) }}
            </dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Tanggal Daftar</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ formatDate(registration.registration_date) }}</dd>
          </div>
          <div v-if="registration.notes" class="col-span-2">
            <dt class="text-xs font-medium text-gray-500 uppercase">Keluhan / Catatan</dt>
            <dd class="mt-0.5 text-sm text-gray-800 whitespace-pre-wrap">{{ registration.notes }}</dd>
          </div>
        </dl>
      </div>

      <!-- Info approval -->
      <div v-if="registration.status === 'pending'" class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-sm text-amber-800">
        Menyetujui pendaftaran akan otomatis mengonfirmasi slot jadwal dan mengalokasikannya ke pasien ini.
        End time akan disesuaikan berdasarkan durasi <strong>{{ registration.duration_hours }} jam</strong>.
      </div>
    </div>

    <!-- Dialog Tolak -->
    <div v-if="rejectDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
        <h3 class="font-semibold text-gray-800">Tolak Pendaftaran</h3>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Alasan Penolakan <span class="text-gray-400">(opsional)</span></label>
          <textarea v-model="rejectReason" rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-400"
            placeholder="Jelaskan alasan penolakan..." />
        </div>
        <div class="flex justify-end gap-3">
          <button @click="rejectDialog = false"
            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50">
            Batal
          </button>
          <button @click="reject"
            class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl">
            Konfirmasi Tolak
          </button>
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
  registration: { type: Object, required: true },
})

const rejectDialog = ref(false)
const rejectReason = ref('')

function approve() {
  if (confirm('Setujui pendaftaran ini dan konfirmasi jadwal?')) {
    router.patch(route('registrations.approve', props.registration.id), {}, { preserveScroll: true })
  }
}

function reject() {
  router.patch(route('registrations.reject', props.registration.id), { notes: rejectReason.value }, {
    preserveScroll: true,
    onSuccess: () => { rejectDialog.value = false },
  })
}

function getProfessional(schedule) {
  if (!schedule) return '—'
  if (schedule.therapist)    return schedule.therapist.name
  if (schedule.doctor)       return 'Dr. ' + schedule.doctor.name
  if (schedule.psychologist) return 'Psi. ' + schedule.psychologist.nama
  return '—'
}

function formatRupiah(val) {
  return 'Rp ' + Number(val ?? 0).toLocaleString('id-ID')
}

const statusMap = {
  pending:  { label: 'Menunggu',  badge: 'bg-yellow-50 text-yellow-700 border-yellow-200', dot: 'bg-yellow-400' },
  approved: { label: 'Disetujui', badge: 'bg-green-50 text-green-700 border-green-200',   dot: 'bg-green-500' },
  rejected: { label: 'Ditolak',   badge: 'bg-red-50 text-red-700 border-red-200',         dot: 'bg-red-400' },
}
function statusLabel(s) { return statusMap[s]?.label ?? s }
function statusBadge(s) { return statusMap[s]?.badge ?? 'bg-gray-50 text-gray-700 border-gray-200' }
function statusDot(s)   { return statusMap[s]?.dot   ?? 'bg-gray-400' }

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}
</script>

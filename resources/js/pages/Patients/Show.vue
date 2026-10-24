<template>
  <AppLayout title="Detail Pasien">
    <div class="max-w-4xl space-y-5">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Link :href="route('patients.index')" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </Link>
          <div>
            <h2 class="text-lg font-semibold text-gray-800">{{ patient.name }}</h2>
            <p class="text-sm text-gray-500">No. RM: {{ patient.medical_record_number }}</p>
          </div>
        </div>
        <Link :href="route('patients.edit', patient.id)"
          class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
          Edit Data
        </Link>
      </div>

      <!-- Status Badge -->
      <div class="flex items-center gap-2">
        <span :class="patient.is_active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'"
          class="inline-flex items-center gap-1.5 text-sm font-medium px-3 py-1 rounded-full border">
          <span :class="patient.is_active ? 'bg-green-500' : 'bg-red-400'" class="w-2 h-2 rounded-full"/>
          {{ patient.is_active ? 'Pasien Aktif' : 'Pasien Tidak Aktif' }}
        </span>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- Info Pasien -->
        <div class="lg:col-span-2 space-y-5">
          <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Data Anak</h3>
            <dl class="grid grid-cols-2 gap-4">
              <div>
                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Nama Lengkap</dt>
                <dd class="mt-1 text-sm text-gray-800 font-medium">{{ patient.name }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">No. Rekam Medis</dt>
                <dd class="mt-1 text-sm font-mono text-gray-800">{{ patient.medical_record_number }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Jenis Kelamin</dt>
                <dd class="mt-1 text-sm text-gray-800">{{ patient.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tanggal Lahir</dt>
                <dd class="mt-1 text-sm text-gray-800">{{ formatDate(patient.birth_date) }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Usia</dt>
                <dd class="mt-1 text-sm text-gray-800">{{ calcAge(patient.birth_date) }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tempat Lahir</dt>
                <dd class="mt-1 text-sm text-gray-800">{{ patient.birth_place || '—' }}</dd>
              </div>
              <div class="col-span-2">
                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Alamat</dt>
                <dd class="mt-1 text-sm text-gray-800">{{ patient.address || '—' }}</dd>
              </div>
              <div v-if="patient.diagnosis" class="col-span-2">
                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Diagnosis Awal</dt>
                <dd class="mt-1 text-sm text-gray-800">{{ patient.diagnosis }}</dd>
              </div>
              <div v-if="patient.notes" class="col-span-2">
                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Catatan</dt>
                <dd class="mt-1 text-sm text-gray-800">{{ patient.notes }}</dd>
              </div>
            </dl>
          </div>

          <!-- Riwayat Jadwal -->
          <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Riwayat Jadwal Terapi</h3>
            <div v-if="!patient.schedules?.length" class="text-sm text-gray-400 py-4 text-center">Belum ada jadwal terapi</div>
            <div v-else class="space-y-3">
              <div v-for="s in patient.schedules.slice(0, 5)" :key="s.id"
                class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <div>
                  <p class="text-sm font-medium text-gray-800">{{ s.therapy_type?.name }}</p>
                  <p class="text-xs text-gray-400">{{ formatDate(s.schedule_date) }} · {{ s.start_time }}–{{ s.end_time }}</p>
                </div>
                <span :class="statusBadge(s.status)" class="text-xs font-medium px-2 py-1 rounded-full border">
                  {{ statusLabel(s.status) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar: Wali + Pendaftaran -->
        <div class="space-y-5">
          <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Data Wali</h3>
            <dl class="space-y-3">
              <div>
                <dt class="text-xs font-medium text-gray-500 uppercase">Nama</dt>
                <dd class="mt-0.5 text-sm text-gray-800">{{ patient.guardian?.name || '—' }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500 uppercase">Hubungan</dt>
                <dd class="mt-0.5 text-sm text-gray-800">{{ patient.guardian?.relationship || '—' }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500 uppercase">Telepon</dt>
                <dd class="mt-0.5 text-sm text-gray-800">{{ patient.guardian?.phone || '—' }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500 uppercase">Alamat</dt>
                <dd class="mt-0.5 text-sm text-gray-800">{{ patient.guardian?.address || '—' }}</dd>
              </div>
            </dl>
          </div>

          <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Ringkasan</h3>
            <div class="space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Total Jadwal</span>
                <span class="font-medium text-gray-800">{{ patient.schedules?.length ?? 0 }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Total Pembayaran</span>
                <span class="font-medium text-gray-800">{{ patient.payments?.length ?? 0 }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Pendaftaran</span>
                <span class="font-medium text-gray-800">{{ patient.registrations?.length ?? 0 }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

defineProps({
  patient: { type: Object, required: true },
})

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}

function calcAge(birthDate) {
  if (!birthDate) return ''
  const today = new Date()
  const birth = new Date(birthDate)
  const months = (today.getFullYear() - birth.getFullYear()) * 12 + (today.getMonth() - birth.getMonth())
  if (months < 24) return `${months} bulan`
  return `${Math.floor(months / 12)} tahun ${months % 12} bulan`
}

const statusMap = {
  scheduled: { label: 'Dijadwalkan', cls: 'bg-blue-50 text-blue-700 border-blue-200' },
  done:      { label: 'Selesai',     cls: 'bg-green-50 text-green-700 border-green-200' },
  cancelled: { label: 'Dibatalkan',  cls: 'bg-red-50 text-red-700 border-red-200' },
}
function statusLabel(s) { return statusMap[s]?.label ?? s }
function statusBadge(s) { return statusMap[s]?.cls ?? 'bg-gray-50 text-gray-700 border-gray-200' }
</script>

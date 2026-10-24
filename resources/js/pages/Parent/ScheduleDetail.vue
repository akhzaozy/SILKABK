<template>
  <AppLayout title="Detail Jadwal">
    <div class="max-w-2xl space-y-5">

      <!-- Header -->
      <div class="flex items-center gap-3">
        <Link :href="route('parent.schedules')" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
          <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Detail Jadwal Terapi</h2>
          <p class="text-sm text-gray-500">{{ formatDate(schedule.schedule_date) }}</p>
        </div>
      </div>

      <!-- Status -->
      <span :class="statusBadge(schedule.status)" class="inline-flex items-center gap-1.5 text-sm font-medium px-3 py-1.5 rounded-full border">
        <span :class="statusDot(schedule.status)" class="w-2 h-2 rounded-full"/>
        {{ statusLabel(schedule.status) }}
      </span>

      <!-- Detail Jadwal -->
      <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 pb-3 mb-4 border-b border-gray-100">Informasi Jadwal</h3>
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Nama Anak</dt>
            <dd class="mt-0.5 text-sm font-medium text-gray-800">{{ schedule.patient?.name }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Jenis Terapi</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ schedule.therapy_type?.name }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Tanggal</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ formatDate(schedule.schedule_date) }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Waktu</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ schedule.start_time }} – {{ schedule.end_time }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Tenaga Profesional</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ getProfessional(schedule) }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Tarif</dt>
            <dd class="mt-0.5 text-sm text-gray-800">
              <span v-if="schedule.therapy_type?.price_per_hour">{{ formatRupiah(schedule.therapy_type.price_per_hour) }}/jam</span>
              <span v-else>—</span>
            </dd>
          </div>
          <div v-if="schedule.notes" class="col-span-2">
            <dt class="text-xs font-medium text-gray-500 uppercase">Catatan</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ schedule.notes }}</dd>
          </div>
        </dl>
      </div>

      <!-- Hasil Terapi -->
      <div v-if="schedule.therapy_result" class="bg-white rounded-2xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 pb-3 mb-4 border-b border-gray-100">Catatan Perkembangan Sesi</h3>
        <dl class="space-y-4">
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Aktivitas Terapi</dt>
            <dd class="mt-0.5 text-sm text-gray-800 whitespace-pre-wrap">{{ schedule.therapy_result.activities }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Respons & Perkembangan Anak</dt>
            <dd class="mt-0.5 text-sm text-gray-800 whitespace-pre-wrap">{{ schedule.therapy_result.patient_response }}</dd>
          </div>
          <div v-if="schedule.therapy_result.goals_achieved">
            <dt class="text-xs font-medium text-gray-500 uppercase">Target yang Tercapai</dt>
            <dd class="mt-0.5 text-sm text-gray-800 whitespace-pre-wrap">{{ schedule.therapy_result.goals_achieved }}</dd>
          </div>
          <div v-if="schedule.therapy_result.next_plan">
            <dt class="text-xs font-medium text-gray-500 uppercase">Rencana Sesi Berikutnya</dt>
            <dd class="mt-0.5 text-sm text-gray-800 whitespace-pre-wrap">{{ schedule.therapy_result.next_plan }}</dd>
          </div>
        </dl>
      </div>
      <div v-else-if="schedule.status === 'scheduled'" class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
        <p class="text-sm text-amber-700">Catatan hasil terapi belum tersedia. Akan diisi oleh terapis setelah sesi selesai.</p>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

defineProps({
  schedule: { type: Object, required: true },
})

function getProfessional(s) {
  if (!s) return '—'
  if (s.therapist)    return s.therapist.name
  if (s.doctor)       return 'Dr. ' + s.doctor.name
  if (s.psychologist) return 'Psi. ' + s.psychologist.nama
  return '—'
}

function formatRupiah(val) {
  return 'Rp ' + Number(val ?? 0).toLocaleString('id-ID')
}

const statusMap = {
  scheduled: { label: 'Dijadwalkan', badge: 'bg-blue-50 text-blue-700 border-blue-200',   dot: 'bg-blue-500' },
  done:      { label: 'Selesai',     badge: 'bg-green-50 text-green-700 border-green-200', dot: 'bg-green-500' },
  cancelled: { label: 'Dibatalkan',  badge: 'bg-red-50 text-red-700 border-red-200',       dot: 'bg-red-400' },
}
function statusLabel(s) { return statusMap[s]?.label ?? s }
function statusBadge(s) { return statusMap[s]?.badge ?? 'bg-gray-50 text-gray-700 border-gray-200' }
function statusDot(s)   { return statusMap[s]?.dot   ?? 'bg-gray-400' }

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })
}
</script>

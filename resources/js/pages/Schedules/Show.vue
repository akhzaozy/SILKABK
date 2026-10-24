<template>
  <AppLayout title="Detail Jadwal">
    <div class="max-w-2xl space-y-5">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Link :href="route('schedules.index')" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </Link>
          <div>
            <h2 class="text-lg font-semibold text-gray-800">Detail Jadwal / Slot</h2>
            <p class="text-sm text-gray-500">{{ formatDate(schedule.schedule_date) }}</p>
          </div>
        </div>
        <div class="flex gap-2">
          <Link v-if="schedule.status !== 'done' && schedule.status !== 'cancelled'"
            :href="route('schedules.edit', schedule.id)"
            class="px-3 py-1.5 text-sm font-medium text-blue-600 border border-blue-200 rounded-xl hover:bg-blue-50 transition-colors">
            Edit
          </Link>
          <button v-if="schedule.status !== 'done' && schedule.status !== 'cancelled'"
            @click="cancel"
            class="px-3 py-1.5 text-sm font-medium text-red-600 border border-red-200 rounded-xl hover:bg-red-50 transition-colors">
            Batalkan
          </button>
        </div>
      </div>

      <!-- Status -->
      <span :class="statusBadge(schedule.status)" class="inline-flex items-center gap-1.5 text-sm font-medium px-3 py-1.5 rounded-full border">
        <span :class="statusDot(schedule.status)" class="w-2 h-2 rounded-full"/>
        {{ statusLabel(schedule.status) }}
      </span>

      <!-- Detail -->
      <div class="bg-white rounded-2xl border border-gray-200 p-5 space-y-4">
        <h3 class="font-semibold text-gray-800 pb-2 border-b border-gray-100">Informasi Jadwal</h3>
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Tanggal</dt>
            <dd class="mt-0.5 text-sm text-gray-800 font-medium">{{ formatDate(schedule.schedule_date) }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Window Waktu</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ schedule.start_time }} – {{ schedule.end_time }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Pasien</dt>
            <dd class="mt-0.5 text-sm text-gray-800 font-medium">
              <span v-if="schedule.patient">{{ schedule.patient.name }}</span>
              <span v-else class="text-blue-600 font-medium bg-blue-50 px-2 py-0.5 rounded-lg text-xs">Slot Kosong</span>
            </dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">No. Rekam Medis</dt>
            <dd class="mt-0.5 text-sm font-mono text-gray-800">{{ schedule.patient?.medical_record_number || '—' }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Tenaga Profesional</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ getProfessional(schedule) }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Tipe</dt>
            <dd class="mt-0.5 text-sm text-gray-800 capitalize">{{ schedule.professional_type }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Jenis Layanan</dt>
            <dd class="mt-0.5 text-sm text-gray-800">{{ schedule.therapy_type?.name || '—' }}</dd>
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
      <div v-if="schedule.therapy_result" class="bg-white rounded-2xl border border-gray-200 p-5 space-y-4">
        <h3 class="font-semibold text-gray-800 pb-2 border-b border-gray-100">Catatan Hasil Terapi</h3>
        <dl class="space-y-4">
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Aktivitas Terapi</dt>
            <dd class="mt-0.5 text-sm text-gray-800 whitespace-pre-wrap">{{ schedule.therapy_result.activities }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 uppercase">Respons Pasien</dt>
            <dd class="mt-0.5 text-sm text-gray-800 whitespace-pre-wrap">{{ schedule.therapy_result.patient_response }}</dd>
          </div>
          <div v-if="schedule.therapy_result.goals_achieved">
            <dt class="text-xs font-medium text-gray-500 uppercase">Target Tercapai</dt>
            <dd class="mt-0.5 text-sm text-gray-800 whitespace-pre-wrap">{{ schedule.therapy_result.goals_achieved }}</dd>
          </div>
          <div v-if="schedule.therapy_result.next_plan">
            <dt class="text-xs font-medium text-gray-500 uppercase">Rencana Sesi Berikutnya</dt>
            <dd class="mt-0.5 text-sm text-gray-800 whitespace-pre-wrap">{{ schedule.therapy_result.next_plan }}</dd>
          </div>
        </dl>
      </div>
      <div v-else-if="schedule.status === 'scheduled'" class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
        <p class="text-sm text-amber-700">Catatan hasil terapi belum diisi oleh terapis.</p>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  schedule: { type: Object, required: true },
})

function cancel() {
  if (confirm('Batalkan jadwal/slot ini?')) {
    router.patch(route('schedules.cancel', props.schedule.id))
  }
}

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
  available: { label: 'Slot Tersedia', badge: 'bg-blue-50 text-blue-700 border-blue-200',    dot: 'bg-blue-400' },
  scheduled: { label: 'Dijadwalkan',   badge: 'bg-green-50 text-green-700 border-green-200',  dot: 'bg-green-500' },
  done:      { label: 'Selesai',       badge: 'bg-gray-50 text-gray-600 border-gray-200',     dot: 'bg-gray-400' },
  cancelled: { label: 'Dibatalkan',    badge: 'bg-red-50 text-red-700 border-red-200',        dot: 'bg-red-400' },
}
function statusLabel(s) { return statusMap[s]?.label ?? s }
function statusBadge(s) { return statusMap[s]?.badge ?? 'bg-gray-50 text-gray-700 border-gray-200' }
function statusDot(s)   { return statusMap[s]?.dot   ?? 'bg-gray-400' }

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })
}
</script>

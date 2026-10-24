<template>
  <AppLayout title="Status Pendaftaran">
    <div class="space-y-5">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Status Pendaftaran Layanan</h2>
          <p class="text-sm text-gray-500 mt-0.5">Pantau status permohonan layanan terapi anak Anda</p>
        </div>
        <Link :href="route('parent.register')"
          class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
          + Daftar Layanan
        </Link>
      </div>

      <!-- Filter -->
      <div class="bg-white rounded-2xl border border-gray-200 p-4">
        <select v-model="f.status" @change="applyFilters"
          class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Semua Status</option>
          <option value="pending">Menunggu</option>
          <option value="approved">Disetujui</option>
          <option value="rejected">Ditolak</option>
        </select>
      </div>

      <!-- Empty State -->
      <div v-if="registrations.data.length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
        <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
          <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </div>
        <h3 class="text-base font-medium text-gray-800 mb-1">Belum Ada Pendaftaran</h3>
        <p class="text-sm text-gray-500 mb-4">Daftarkan anak Anda untuk mendapatkan layanan terapi</p>
        <Link :href="route('parent.register')"
          class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
          Daftar Sekarang
        </Link>
      </div>

      <!-- List -->
      <div v-else class="space-y-3">
        <div v-for="r in registrations.data" :key="r.id" class="bg-white rounded-2xl border border-gray-200 p-5">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <span :class="statusBadge(r.status)" class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-1 rounded-full border">
                  <span :class="statusDot(r.status)" class="w-1.5 h-1.5 rounded-full"/>
                  {{ statusLabel(r.status) }}
                </span>
              </div>
              <p class="text-base font-semibold text-gray-800">{{ r.patient?.name }}</p>
              <p class="text-sm text-gray-500">{{ r.therapy_type?.name }}</p>
              <template v-if="r.schedule">
                <div class="mt-2 text-xs text-gray-500 space-y-0.5">
                  <p>{{ formatDate(r.schedule.schedule_date) }} · {{ r.schedule.start_time }} – {{ r.schedule.end_time }}</p>
                  <p>{{ getProfessional(r.schedule) }} · {{ r.duration_hours }} jam</p>
                </div>
              </template>
              <p class="text-xs text-gray-400 mt-1">Didaftarkan: {{ formatDate(r.registration_date) }}</p>
              <p v-if="r.notes && r.status === 'rejected'" class="mt-2 text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2">
                Alasan penolakan: {{ r.notes }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="registrations.last_page > 1" class="flex items-center justify-between">
        <p class="text-xs text-gray-500">{{ registrations.from }}–{{ registrations.to }} dari {{ registrations.total }}</p>
        <div class="flex gap-1">
          <Link v-for="link in registrations.links" :key="link.label" :href="link.url ?? '#'"
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
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  registrations: { type: Object, required: true },
  filters:       { type: Object, default: () => ({}) },
})

const f = reactive({ status: props.filters.status ?? '' })

function applyFilters() {
  router.get(route('parent.registrations'), f.status ? { status: f.status } : {}, { preserveState: true, replace: true })
}

function getProfessional(s) {
  if (!s) return '—'
  if (s.therapist)    return s.therapist.name
  if (s.doctor)       return 'Dr. ' + s.doctor.name
  if (s.psychologist) return 'Psi. ' + s.psychologist.nama
  return '—'
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

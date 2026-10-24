<template>
  <AppLayout title="Jadwal Terapi Anak">
    <div class="space-y-5">

      <!-- Header -->
      <div>
        <h2 class="text-lg font-semibold text-gray-800">Jadwal Terapi Anak</h2>
        <p class="text-sm text-gray-500 mt-0.5">Seluruh jadwal sesi terapi anak Anda</p>
      </div>

      <!-- Tidak ada pasien terdaftar -->
      <div v-if="patients.length === 0" class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6 text-center">
        <svg class="w-10 h-10 mx-auto mb-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <p class="text-sm font-medium text-yellow-800">Belum ada anak yang terdaftar</p>
        <p class="text-xs text-yellow-600 mt-1">Tambahkan data anak terlebih dahulu melalui menu "Tambah Anak"</p>
      </div>

      <template v-else>
        <!-- Filter -->
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
          <div class="flex flex-wrap gap-3">
            <select v-model="f.period" @change="applyFilters"
              class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Semua Periode</option>
              <option value="upcoming">Mendatang</option>
              <option value="past">Sudah Lewat</option>
            </select>
            <select v-model="f.status" @change="applyFilters"
              class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Semua Status</option>
              <option value="scheduled">Dijadwalkan</option>
              <option value="done">Selesai</option>
              <option value="cancelled">Dibatalkan</option>
            </select>
            <button v-if="f.status || f.period" @click="resetFilters"
              class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded-xl hover:bg-gray-100">
              Reset
            </button>
          </div>
        </div>

        <!-- Empty State — pasien ada tapi tidak ada jadwal -->
        <div v-if="schedules.data.length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
          <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <h3 class="text-base font-medium text-gray-800 mb-1">Belum Ada Jadwal Terapi</h3>
          <p class="text-sm text-gray-500">Jadwal terapi anak Anda akan muncul di sini setelah dijadwalkan oleh admin</p>
        </div>

        <!-- Schedule Cards -->
        <div v-else class="space-y-3">
          <div v-for="s in schedules.data" :key="s.id"
            class="bg-white rounded-2xl border border-gray-200 hover:border-blue-200 hover:shadow-sm transition-all">
            <div class="p-5">
              <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-2">
                    <span :class="statusBadge(s.status)"
                      class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-1 rounded-full border">
                      <span :class="statusDot(s.status)" class="w-1.5 h-1.5 rounded-full"/>
                      {{ statusLabel(s.status) }}
                    </span>
                    <span v-if="isUpcoming(s.schedule_date)"
                      class="text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 px-2 py-1 rounded-full">
                      Mendatang
                    </span>
                  </div>
                  <p class="text-base font-semibold text-gray-800">{{ s.patient?.name }}</p>
                  <p class="text-sm text-gray-500">{{ s.therapy_type?.name }}</p>

                  <div class="grid grid-cols-2 gap-x-6 gap-y-2 mt-3 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                      </svg>
                      {{ formatDate(s.schedule_date) }}
                    </div>
                    <div class="flex items-center gap-2">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                      {{ s.start_time }} – {{ s.end_time }}
                    </div>
                    <div class="flex items-center gap-2">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                      {{ getProfessional(s) }}
                    </div>
                  </div>
                </div>

                <Link :href="route('parent.schedules.show', s.id)"
                  class="flex-shrink-0 text-xs font-medium text-blue-600 hover:text-blue-800 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-colors">
                  Detail →
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="schedules.last_page > 1" class="flex items-center justify-between">
          <p class="text-xs text-gray-500">{{ schedules.from }}–{{ schedules.to }} dari {{ schedules.total }} jadwal</p>
          <div class="flex gap-1">
            <Link v-for="link in schedules.links" :key="link.label" :href="link.url ?? '#'"
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
      </template>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  schedules: { type: Object, required: true },
  patients:  { type: Array,  default: () => [] },
  filters:   { type: Object, default: () => ({}) },
})

const f = reactive({
  status: props.filters.status ?? '',
  period: props.filters.period ?? '',
})

function applyFilters() {
  const params = {}
  if (f.status) params.status = f.status
  if (f.period) params.period = f.period
  router.get(route('parent.schedules'), params, { preserveState: true, replace: true })
}

function resetFilters() {
  f.status = ''
  f.period = ''
  applyFilters()
}

function getProfessional(s) {
  if (!s) return '—'
  if (s.therapist)    return s.therapist.name
  if (s.doctor)       return 'Dr. ' + s.doctor.name
  if (s.psychologist) return 'Psi. ' + s.psychologist.nama
  return '—'
}

function isUpcoming(dateStr) {
  if (!dateStr) return false
  return String(dateStr).substring(0, 10) >= new Date().toISOString().substring(0, 10)
}

const statusMap = {
  scheduled: { label: 'Dijadwalkan', badge: 'bg-blue-50 text-blue-700 border-blue-200',   dot: 'bg-blue-500'  },
  done:      { label: 'Selesai',     badge: 'bg-green-50 text-green-700 border-green-200', dot: 'bg-green-500' },
  cancelled: { label: 'Dibatalkan',  badge: 'bg-red-50 text-red-700 border-red-200',       dot: 'bg-red-400'   },
  available: { label: 'Tersedia',    badge: 'bg-gray-50 text-gray-700 border-gray-200',    dot: 'bg-gray-400'  },
}
function statusLabel(s) { return statusMap[s]?.label ?? s }
function statusBadge(s) { return statusMap[s]?.badge ?? 'bg-gray-50 text-gray-700 border-gray-200' }
function statusDot(s)   { return statusMap[s]?.dot   ?? 'bg-gray-400' }

function formatDate(val) {
  if (!val) return '—'
  const [y, m, d] = String(val).substring(0, 10).split('-').map(Number)
  return new Date(y, m - 1, d).toLocaleDateString('id-ID', {
    weekday: 'long', day: '2-digit', month: 'long', year: 'numeric',
  })
}
</script>

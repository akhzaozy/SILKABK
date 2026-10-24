<template>
  <AppLayout title="Jadwal Praktik Saya">
    <div class="space-y-5">

      <div>
        <h2 class="text-lg font-semibold text-gray-800">Jadwal Praktik Saya</h2>
        <p class="text-sm text-gray-500 mt-0.5">Semua slot jadwal Anda — tersedia maupun sudah terisi</p>
      </div>

      <!-- Filter -->
      <div class="bg-white rounded-2xl border border-gray-200 p-4">
        <div class="flex flex-wrap gap-3">
          <input v-model="f.date" type="date" @change="applyFilters"
            class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <select v-model="f.status" @change="applyFilters"
            class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="available">Slot Tersedia</option>
            <option value="scheduled">Dijadwalkan</option>
            <option value="done">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
          </select>
          <button v-if="f.date || f.status" @click="resetFilters"
            class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded-xl hover:bg-gray-100">
            Reset Filter
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="schedules.data.length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
        <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
          <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <h3 class="text-base font-medium text-gray-800 mb-1">Tidak Ada Jadwal</h3>
        <p class="text-sm text-gray-500">Belum ada jadwal yang ditugaskan kepada Anda</p>
      </div>

      <!-- List -->
      <div v-else class="space-y-3">
        <div v-for="s in schedules.data" :key="s.id"
          class="bg-white rounded-2xl border hover:shadow-sm transition-all p-5"
          :class="s.status === 'available' ? 'border-blue-100' : 'border-gray-200'">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <span :class="statusBadge(s.status)" class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-1 rounded-full border">
                  <span :class="statusDot(s.status)" class="w-1.5 h-1.5 rounded-full"/>
                  {{ statusLabel(s.status) }}
                </span>
                <span v-if="s.therapy_result" class="inline-flex items-center gap-1.5 text-xs font-medium bg-green-50 text-green-700 border border-green-200 px-2 py-1 rounded-full">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Catatan terisi
                </span>
              </div>

              <template v-if="s.status === 'available'">
                <p class="text-base font-semibold text-blue-700">Slot Kosong</p>
                <p class="text-sm text-gray-500">{{ s.therapy_type?.name }}</p>
              </template>
              <template v-else>
                <p class="text-base font-semibold text-gray-800">{{ s.patient?.name }}</p>
                <p class="text-sm text-gray-500">{{ s.therapy_type?.name }}</p>
              </template>

              <p class="text-sm text-gray-500 mt-0.5">
                {{ formatDate(s.schedule_date) }} · {{ s.start_time }} – {{ s.end_time }}
              </p>
            </div>

            <div class="flex flex-col gap-2 flex-shrink-0">
              <Link v-if="s.status !== 'available'" :href="route('therapist.schedules.show', s.id)"
                class="text-xs font-medium text-blue-600 hover:text-blue-800 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-colors text-center">
                Detail
              </Link>
              <Link v-if="s.status === 'scheduled' && !s.therapy_result"
                :href="route('therapist.results.create', s.id)"
                class="text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-lg transition-colors text-center">
                Input Hasil
              </Link>
              <Link v-if="s.therapy_result"
                :href="route('therapist.results.edit', s.therapy_result.id)"
                class="text-xs font-medium text-gray-600 hover:text-gray-800 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors text-center">
                Edit Catatan
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="schedules.last_page > 1" class="flex items-center justify-between">
        <p class="text-xs text-gray-500">{{ schedules.from }}–{{ schedules.to }} dari {{ schedules.total }}</p>
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
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  schedules: { type: Object, required: true },
  filters:   { type: Object, default: () => ({}) },
})

const f = reactive({
  date:   props.filters.date   ?? '',
  status: props.filters.status ?? '',
})

function applyFilters() {
  const params = {}
  if (f.date)   params.date   = f.date
  if (f.status) params.status = f.status
  router.get(route('therapist.schedules'), params, { preserveState: true, replace: true })
}

function resetFilters() {
  f.date = ''
  f.status = ''
  router.get(route('therapist.schedules'), {}, { preserveState: true, replace: true })
}

const statusMap = {
  available: { label: 'Slot Tersedia', badge: 'bg-blue-50 text-blue-700 border-blue-200',   dot: 'bg-blue-400' },
  scheduled: { label: 'Dijadwalkan',   badge: 'bg-green-50 text-green-700 border-green-200', dot: 'bg-green-500' },
  done:      { label: 'Selesai',       badge: 'bg-gray-50 text-gray-600 border-gray-200',    dot: 'bg-gray-400' },
  cancelled: { label: 'Dibatalkan',    badge: 'bg-red-50 text-red-700 border-red-200',       dot: 'bg-red-400' },
}
function statusLabel(s) { return statusMap[s]?.label ?? s }
function statusBadge(s) { return statusMap[s]?.badge ?? 'bg-gray-50 text-gray-700 border-gray-200' }
function statusDot(s)   { return statusMap[s]?.dot   ?? 'bg-gray-400' }

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', { weekday: 'short', day: '2-digit', month: 'long', year: 'numeric' })
}
</script>

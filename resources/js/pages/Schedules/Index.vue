<template>
  <AppLayout title="Jadwal & Slot Tersedia">
    <div class="space-y-5">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Jadwal & Slot Tersedia</h2>
          <p class="text-sm text-gray-500 mt-0.5">Kelola jadwal sesi dan slot yang bisa dibooking orang tua</p>
        </div>
        <div class="flex items-center gap-2">
          <a :href="route('export.schedules')"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-green-700 border border-green-300 bg-green-50 hover:bg-green-100 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export Excel
          </a>
          <Link :href="route('schedules.create')"
            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
            + Buat Jadwal / Slot
          </Link>
        </div>
      </div>

      <!-- Filter -->
      <div class="bg-white rounded-2xl border border-gray-200 p-4">
        <div class="flex flex-wrap gap-3">
          <input v-model="f.date" type="date" @change="applyFilters"
            class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <select v-model="f.professional_type" @change="applyFilters"
            class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Profesional</option>
            <option value="terapis">Terapis</option>
            <option value="dokter">Dokter</option>
            <option value="psikolog">Psikolog</option>
          </select>
          <select v-model="f.status" @change="applyFilters"
            class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="available">Slot Tersedia</option>
            <option value="scheduled">Dijadwalkan</option>
            <option value="done">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
          </select>
          <button v-if="hasFilter" @click="resetFilters"
            class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded-xl hover:bg-gray-100">
            Reset
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100 bg-gray-50">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal & Waktu</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pasien</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tenaga Profesional</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jenis Layanan</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th class="text-right px-5 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-if="schedules.data.length === 0">
                <td colspan="6" class="px-5 py-12 text-center text-gray-400 text-sm">Tidak ada jadwal ditemukan</td>
              </tr>
              <tr v-for="s in schedules.data" :key="s.id" class="hover:bg-gray-50/50 transition-colors">
                <td class="px-5 py-4">
                  <p class="font-medium text-gray-800">{{ formatDate(s.schedule_date) }}</p>
                  <p class="text-xs text-gray-400">{{ s.start_time }} – {{ s.end_time }}</p>
                </td>
                <td class="px-5 py-4">
                  <template v-if="s.patient">
                    <p class="font-medium text-gray-800">{{ s.patient.name }}</p>
                    <p class="text-xs text-gray-400">{{ s.patient.medical_record_number }}</p>
                  </template>
                  <span v-else class="text-xs text-blue-600 font-medium bg-blue-50 px-2 py-1 rounded-lg">Slot Kosong</span>
                </td>
                <td class="px-5 py-4">
                  <p class="text-gray-800">{{ getProfessional(s) }}</p>
                  <p class="text-xs text-gray-400 capitalize">{{ s.professional_type }}</p>
                </td>
                <td class="px-5 py-4 text-gray-700">
                  <p>{{ s.therapy_type?.name }}</p>
                  <p v-if="s.therapy_type?.price_per_hour" class="text-xs text-gray-400">
                    {{ formatRupiah(s.therapy_type.price_per_hour) }}/jam
                  </p>
                </td>
                <td class="px-5 py-4">
                  <span :class="statusBadge(s.status)" class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-1 rounded-full border">
                    <span :class="statusDot(s.status)" class="w-1.5 h-1.5 rounded-full"/>
                    {{ statusLabel(s.status) }}
                  </span>
                </td>
                <td class="px-5 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="route('schedules.show', s.id)"
                      class="text-xs font-medium text-gray-600 hover:text-gray-900 px-3 py-1.5 rounded-lg hover:bg-gray-100">
                      Detail
                    </Link>
                    <Link v-if="s.status !== 'done' && s.status !== 'cancelled'" :href="route('schedules.edit', s.id)"
                      class="text-xs font-medium text-blue-600 hover:text-blue-800 px-3 py-1.5 rounded-lg hover:bg-blue-50">
                      Edit
                    </Link>
                    <button v-if="s.status !== 'done' && s.status !== 'cancelled'"
                      @click="cancel(s)"
                      class="text-xs font-medium text-red-600 hover:text-red-800 px-3 py-1.5 rounded-lg hover:bg-red-50">
                      Batalkan
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="schedules.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
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
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  schedules:  { type: Object, required: true },
  therapists: { type: Array,  default: () => [] },
  filters:    { type: Object, default: () => ({}) },
})

const f = reactive({
  date:              props.filters.date              ?? '',
  professional_type: props.filters.professional_type ?? '',
  status:            props.filters.status            ?? '',
})

const hasFilter = computed(() => Object.values(f).some(Boolean))

function applyFilters() {
  const query = {}
  Object.entries(f).forEach(([k, v]) => { if (v) query[k] = v })
  router.get(route('schedules.index'), query, { preserveState: true, replace: true })
}

function resetFilters() {
  Object.keys(f).forEach(k => f[k] = '')
  applyFilters()
}

function cancel(schedule) {
  if (confirm('Batalkan jadwal/slot ini?')) {
    router.patch(route('schedules.cancel', schedule.id), {}, { preserveScroll: true })
  }
}

function getProfessional(s) {
  if (s.therapist)    return s.therapist.name
  if (s.doctor)       return 'Dr. ' + s.doctor.name
  if (s.psychologist) return 'Psi. ' + s.psychologist.nama
  return '—'
}

function formatRupiah(val) {
  return 'Rp ' + Number(val ?? 0).toLocaleString('id-ID')
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
  return new Date(val).toLocaleDateString('id-ID', { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' })
}
</script>

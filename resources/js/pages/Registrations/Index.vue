<template>
  <AppLayout title="Pendaftaran Layanan">
    <div class="space-y-5">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Pendaftaran Layanan</h2>
          <p class="text-sm text-gray-500 mt-0.5">Kelola permohonan pendaftaran layanan terapi</p>
        </div>
        <a :href="route('export.registrations')"
          class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-green-700 border border-green-300 bg-green-50 hover:bg-green-100 rounded-xl transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          Export Excel
        </a>
      </div>

      <!-- Filter -->
      <div class="bg-white rounded-2xl border border-gray-200 p-4">
        <div class="flex flex-wrap gap-3">
          <div class="relative flex-1 min-w-48">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input v-model="f.search" type="text"
              placeholder="Cari nama atau nomor RM pasien..."
              class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
              @input="debouncedApply"
            />
          </div>
          <select v-model="f.status" @change="applyFilters"
            class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="pending">Menunggu</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
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
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pasien</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Slot Jadwal</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Durasi</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tgl. Daftar</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-if="registrations.data.length === 0">
                <td colspan="6" class="px-5 py-12 text-center text-gray-400 text-sm">Tidak ada pendaftaran ditemukan</td>
              </tr>
              <tr v-for="r in registrations.data" :key="r.id" class="hover:bg-gray-50/50 transition-colors">
                <td class="px-5 py-4">
                  <div class="flex items-center gap-1.5 mb-0.5">
                    <p class="font-medium text-gray-800">{{ r.patient?.name }}</p>
                    <span v-if="r.patient_type === 'new'" class="text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200 px-1.5 py-0.5 rounded-full">Baru</span>
                  </div>
                  <p class="text-xs text-gray-400">{{ r.patient?.medical_record_number }}</p>
                  <p class="text-xs text-gray-400">Wali: {{ r.patient?.guardian?.name }}</p>
                </td>
                <td class="px-5 py-4">
                  <template v-if="r.schedule">
                    <p class="text-sm text-gray-800">{{ formatDate(r.schedule.schedule_date) }}</p>
                    <p class="text-xs text-gray-400">{{ r.schedule.start_time }} – {{ r.schedule.end_time }}</p>
                    <p class="text-xs text-gray-500">{{ getProfessional(r.schedule) }}</p>
                  </template>
                  <span v-else class="text-xs text-gray-400">—</span>
                </td>
                <td class="px-5 py-4 text-sm text-gray-700">{{ r.duration_hours }} jam</td>
                <td class="px-5 py-4 text-gray-600 text-xs">{{ formatDate(r.registration_date) }}</td>
                <td class="px-5 py-4">
                  <span :class="statusBadge(r.status)" class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-1 rounded-full border">
                    <span :class="statusDot(r.status)" class="w-1.5 h-1.5 rounded-full"/>
                    {{ statusLabel(r.status) }}
                  </span>
                </td>
                <td class="px-5 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="route('registrations.show', r.id)"
                      class="text-xs font-medium text-gray-600 hover:text-gray-900 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                      Detail
                    </Link>
                    <template v-if="r.status === 'pending'">
                      <button @click="approve(r)"
                        class="text-xs font-medium text-green-600 hover:text-green-800 px-3 py-1.5 rounded-lg hover:bg-green-50 transition-colors">
                        Setujui
                      </button>
                      <button @click="reject(r)"
                        class="text-xs font-medium text-red-600 hover:text-red-800 px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors">
                        Tolak
                      </button>
                    </template>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="registrations.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
          <p class="text-xs text-gray-500">{{ registrations.from }}–{{ registrations.to }} dari {{ registrations.total }} pendaftaran</p>
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

    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  registrations: { type: Object, required: true },
  filters:       { type: Object, default: () => ({}) },
})

const f = reactive({
  search: props.filters.search ?? '',
  status: props.filters.status ?? '',
})

const hasFilter = computed(() => Object.values(f).some(Boolean))

let timer = null
function debouncedApply() {
  clearTimeout(timer)
  timer = setTimeout(applyFilters, 400)
}

function applyFilters() {
  const query = {}
  Object.entries(f).forEach(([k, v]) => { if (v) query[k] = v })
  router.get(route('registrations.index'), query, { preserveState: true, replace: true })
}

function resetFilters() {
  Object.keys(f).forEach(k => f[k] = '')
  applyFilters()
}

function getProfessional(s) {
  if (!s) return '—'
  if (s.therapist)    return s.therapist.name
  if (s.doctor)       return 'Dr. ' + s.doctor.name
  if (s.psychologist) return 'Psi. ' + s.psychologist.nama
  return '—'
}

function approve(reg) {
  router.patch(route('registrations.approve', reg.id), {}, { preserveScroll: true })
}

function reject(reg) {
  if (confirm('Tolak pendaftaran ini?')) {
    router.patch(route('registrations.reject', reg.id), {}, { preserveScroll: true })
  }
}

const statusMap = {
  pending:  { label: 'Menunggu', badge: 'bg-yellow-50 text-yellow-700 border-yellow-200', dot: 'bg-yellow-400' },
  approved: { label: 'Disetujui', badge: 'bg-green-50 text-green-700 border-green-200', dot: 'bg-green-500' },
  rejected: { label: 'Ditolak',   badge: 'bg-red-50 text-red-700 border-red-200',       dot: 'bg-red-400' },
}
function statusLabel(s) { return statusMap[s]?.label ?? s }
function statusBadge(s) { return statusMap[s]?.badge ?? 'bg-gray-50 text-gray-700 border-gray-200' }
function statusDot(s)   { return statusMap[s]?.dot   ?? 'bg-gray-400' }

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>

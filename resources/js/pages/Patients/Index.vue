<template>
  <AppLayout title="Data Pasien">
    <div class="space-y-5">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Data Pasien</h2>
          <p class="text-sm text-gray-500 mt-0.5">Daftar seluruh pasien terdaftar</p>
        </div>
        <div class="flex items-center gap-2">
          <a :href="route('export.patients')"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-green-700 border border-green-300 bg-green-50 hover:bg-green-100 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export Excel
          </a>
          <Link :href="route('patients.create')"
            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Pasien
          </Link>
        </div>
      </div>

      <!-- Filter -->
      <div class="bg-white rounded-2xl border border-gray-200 p-4">
        <div class="flex flex-wrap gap-3">
          <div class="relative flex-1 min-w-48">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input v-model="f.search" type="text"
              placeholder="Cari nama atau nomor RM..."
              class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
              @input="debouncedApply"
            />
          </div>
          <select v-model="f.status" @change="applyFilters"
            class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Tidak Aktif</option>
          </select>
          <button v-if="hasFilter" @click="resetFilters"
            class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded-xl hover:bg-gray-100 transition-colors">
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
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">No. RM</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama Pasien</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tgl. Lahir / Usia</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Wali</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-if="patients.data.length === 0">
                <td colspan="6" class="px-5 py-12 text-center text-gray-400 text-sm">
                  Belum ada data pasien
                </td>
              </tr>
              <tr v-for="p in patients.data" :key="p.id" class="hover:bg-gray-50/50 transition-colors">
                <td class="px-5 py-4">
                  <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-lg">{{ p.medical_record_number }}</span>
                </td>
                <td class="px-5 py-4">
                  <p class="font-medium text-gray-800">{{ p.name }}</p>
                  <p class="text-xs text-gray-400">{{ p.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                </td>
                <td class="px-5 py-4 text-gray-600">
                  <p>{{ formatDate(p.birth_date) }}</p>
                  <p class="text-xs text-gray-400">{{ calcAge(p.birth_date) }}</p>
                </td>
                <td class="px-5 py-4 text-gray-600">{{ p.guardian?.name ?? '—' }}</td>
                <td class="px-5 py-4">
                  <span :class="p.is_active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'"
                    class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-1 rounded-full border">
                    <span :class="p.is_active ? 'bg-green-500' : 'bg-red-400'" class="w-1.5 h-1.5 rounded-full"/>
                    {{ p.is_active ? 'Aktif' : 'Tidak Aktif' }}
                  </span>
                </td>
                <td class="px-5 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="route('patients.show', p.id)"
                      class="text-xs font-medium text-gray-600 hover:text-gray-900 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                      Detail
                    </Link>
                    <Link :href="route('patients.edit', p.id)"
                      class="text-xs font-medium text-blue-600 hover:text-blue-800 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-colors">
                      Edit
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="patients.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
          <p class="text-xs text-gray-500">{{ patients.from }}–{{ patients.to }} dari {{ patients.total }} pasien</p>
          <div class="flex gap-1">
            <Link v-for="link in patients.links" :key="link.label" :href="link.url ?? '#'"
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
  patients: { type: Object, required: true },
  filters:  { type: Object, default: () => ({}) },
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
  router.get(route('patients.index'), query, { preserveState: true, replace: true })
}

function resetFilters() {
  Object.keys(f).forEach(k => f[k] = '')
  applyFilters()
}

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function calcAge(birthDate) {
  if (!birthDate) return ''
  const today = new Date()
  const birth = new Date(birthDate)
  const months = (today.getFullYear() - birth.getFullYear()) * 12 + (today.getMonth() - birth.getMonth())
  if (months < 24) return `${months} bulan`
  return `${Math.floor(months / 12)} tahun ${months % 12} bulan`
}
</script>

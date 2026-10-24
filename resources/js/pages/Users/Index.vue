<template>
  <AppLayout title="Kelola Pengguna">
    <div class="space-y-5">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Kelola Pengguna</h2>
          <p class="text-sm text-gray-500 mt-0.5">Manajemen akun seluruh pengguna sistem</p>
        </div>
        <Link
          :href="route('users.create')"
          class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Tambah Pengguna
        </Link>
      </div>

      <!-- Filter -->
      <div class="bg-white rounded-2xl border border-gray-200 p-4">
        <div class="flex flex-wrap gap-3">
          <div class="relative flex-1 min-w-48">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
              v-model="f.search"
              type="text"
              placeholder="Cari nama atau email..."
              class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
              @input="debouncedApply"
            />
          </div>

          <select v-model="f.role" @change="applyFilters"
            class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Role</option>
            <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
          </select>

          <select v-model="f.status" @change="applyFilters"
            class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
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
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pengguna</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Role</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Terdaftar</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-if="users.data.length === 0">
                <td colspan="5" class="px-5 py-12 text-center text-gray-400 text-sm">
                  Tidak ada pengguna ditemukan
                </td>
              </tr>
              <tr v-for="u in users.data" :key="u.id" class="hover:bg-gray-50/50 transition-colors">
                <td class="px-5 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                      <span class="text-blue-700 font-semibold text-xs uppercase">{{ u.name?.charAt(0) }}</span>
                    </div>
                    <div>
                      <p class="font-medium text-gray-800">{{ u.name }}</p>
                      <p class="text-xs text-gray-400">{{ u.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-5 py-4">
                  <span :class="roleBadge(u.role)" class="text-xs font-medium px-2 py-1 rounded-full border">
                    {{ roleLabel(u.role) }}
                  </span>
                </td>
                <td class="px-5 py-4">
                  <span :class="u.is_active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'"
                    class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-1 rounded-full border">
                    <span :class="u.is_active ? 'bg-green-500' : 'bg-red-400'" class="w-1.5 h-1.5 rounded-full"/>
                    {{ u.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="px-5 py-4 text-gray-500 text-xs">{{ formatDate(u.created_at) }}</td>
                <td class="px-5 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="route('users.edit', u.id)"
                      class="text-xs font-medium text-blue-600 hover:text-blue-800 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-colors">
                      Edit
                    </Link>
                    <button
                      @click="toggleActive(u)"
                      :class="u.is_active ? 'text-red-600 hover:bg-red-50' : 'text-green-600 hover:bg-green-50'"
                      class="text-xs font-medium px-3 py-1.5 rounded-lg transition-colors"
                    >
                      {{ u.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="users.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
          <p class="text-xs text-gray-500">{{ users.from }}–{{ users.to }} dari {{ users.total }} pengguna</p>
          <div class="flex gap-1">
            <Link v-for="link in users.links" :key="link.label" :href="link.url ?? '#'"
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
  users:   { type: Object, required: true },
  roles:   { type: Array,  default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const f = reactive({
  search: props.filters.search ?? '',
  role:   props.filters.role   ?? '',
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
  router.get(route('users.index'), query, { preserveState: true, replace: true })
}

function resetFilters() {
  Object.keys(f).forEach(k => f[k] = '')
  applyFilters()
}

function toggleActive(user) {
  router.patch(route('users.toggleActive', user.id), {}, {
    preserveScroll: true,
  })
}

const roleMap = {
  super_admin: { label: 'Super Admin', cls: 'bg-purple-50 text-purple-700 border-purple-200' },
  admin:       { label: 'Admin',       cls: 'bg-blue-50 text-blue-700 border-blue-200' },
  orang_tua:   { label: 'Orang Tua',   cls: 'bg-green-50 text-green-700 border-green-200' },
  terapis:     { label: 'Terapis',     cls: 'bg-orange-50 text-orange-700 border-orange-200' },
  dokter:      { label: 'Dokter',      cls: 'bg-red-50 text-red-700 border-red-200' },
  psikolog:    { label: 'Psikolog',    cls: 'bg-pink-50 text-pink-700 border-pink-200' },
}
function roleLabel(role) { return roleMap[role]?.label ?? role }
function roleBadge(role) { return roleMap[role]?.cls ?? 'bg-gray-50 text-gray-700 border-gray-200' }

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>

<template>
  <AppLayout title="Kelola Biaya Layanan">
    <div class="space-y-5">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Kelola Biaya Layanan</h2>
          <p class="text-sm text-gray-500 mt-0.5">Daftar jenis layanan terapi dan biaya per sesi</p>
        </div>
        <Link :href="route('therapy-types.create')"
          class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
          + Tambah Layanan
        </Link>
      </div>

      <!-- Tabel -->
      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Nama Layanan</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Deskripsi</th>
              <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Biaya / Sesi</th>
              <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Jadwal</th>
              <th class="px-5 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="types.length === 0">
              <td colspan="5" class="px-5 py-12 text-center text-gray-400">Belum ada layanan</td>
            </tr>
            <tr v-for="t in types" :key="t.id" class="hover:bg-gray-50">
              <td class="px-5 py-4">
                <p class="font-medium text-gray-800">{{ t.name }}</p>
              </td>
              <td class="px-5 py-4 text-gray-500 max-w-xs">
                <p class="line-clamp-2 text-xs">{{ t.description || '—' }}</p>
              </td>
              <td class="px-5 py-4 text-right">
                <span class="font-semibold text-green-700">{{ formatRupiah(t.price_per_hour) }}</span>
              </td>
              <td class="px-5 py-4 text-center">
                <span class="text-xs font-semibold px-2 py-1 rounded-full"
                  :class="t.schedules_count > 0 ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-500'">
                  {{ t.schedules_count }}
                </span>
              </td>
              <td class="px-5 py-4">
                <div class="flex items-center justify-end gap-2">
                  <Link :href="route('therapy-types.edit', t.id)"
                    class="text-xs font-medium text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50">
                    Edit
                  </Link>
                  <button
                    v-if="t.schedules_count === 0 && t.registrations_count === 0"
                    @click="destroy(t)"
                    class="text-xs font-medium text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
          </table>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

defineProps({
  types: { type: Array, default: () => [] },
})

function destroy(type) {
  if (confirm(`Hapus layanan "${type.name}"?`)) {
    router.delete(route('therapy-types.destroy', type.id), { preserveScroll: true })
  }
}

function formatRupiah(val) {
  return 'Rp ' + Number(val ?? 0).toLocaleString('id-ID')
}
</script>

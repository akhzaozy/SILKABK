<template>
  <AppLayout title="Tambah Layanan">
    <div class="max-w-xl space-y-5">

      <div class="flex items-center gap-3">
        <Link :href="route('therapy-types.index')" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
          <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Tambah Layanan Baru</h2>
          <p class="text-sm text-gray-500">Jenis layanan terapi dan biayanya</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Layanan <span class="text-red-500">*</span></label>
          <input v-model="form.name" type="text"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.name ? 'border-red-400' : 'border-gray-300'"
            placeholder="cth: Terapi Wicara" />
          <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Deskripsi <span class="text-gray-400 font-normal">(opsional)</span>
          </label>
          <textarea v-model="form.description" rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Penjelasan singkat mengenai layanan ini..." />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Biaya per Sesi (Rp) <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">Rp</span>
            <input v-model="form.price_per_hour" type="number" min="0" step="1000"
              class="w-full pl-10 pr-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="form.errors.price_per_hour ? 'border-red-400' : 'border-gray-300'"
              placeholder="150000" />
          </div>
          <p v-if="form.errors.price_per_hour" class="mt-1 text-xs text-red-500">{{ form.errors.price_per_hour }}</p>
          <p v-if="form.price_per_hour" class="mt-1 text-xs text-gray-400">
            {{ formatRupiah(form.price_per_hour) }} per sesi (1 jam)
          </p>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
          <Link :href="route('therapy-types.index')"
            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
            Batal
          </Link>
          <button type="submit" :disabled="form.processing"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors disabled:opacity-50">
            {{ form.processing ? 'Menyimpan...' : 'Simpan Layanan' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const form = useForm({
  name:           '',
  description:    '',
  price_per_hour: '',
})

function submit() {
  form.post(route('therapy-types.store'))
}

function formatRupiah(val) {
  return 'Rp ' + Number(val ?? 0).toLocaleString('id-ID')
}
</script>

<template>
  <AppLayout title="Tambah Data Anak">
    <div class="max-w-2xl space-y-5">

      <!-- Header -->
      <div class="flex items-center gap-3">
        <Link :href="route('parent.registrations')" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
          <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Tambah Data Anak</h2>
          <p class="text-sm text-gray-500">Daftarkan anak Anda ke sistem untuk bisa menggunakan layanan</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap Anak <span class="text-red-500">*</span></label>
          <input v-model="form.name" type="text"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.name ? 'border-red-400' : 'border-gray-300'"
            placeholder="Nama lengkap anak" />
          <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            NIK Anak <span class="text-gray-400 font-normal">(opsional)</span>
          </label>
          <input v-model="form.nik" type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.nik ? 'border-red-400' : 'border-gray-300'"
            placeholder="16 digit NIK" maxlength="20" />
          <p v-if="form.errors.nik" class="mt-1 text-xs text-red-500">{{ form.errors.nik }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
            <select v-model="form.gender"
              class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="form.errors.gender ? 'border-red-400' : 'border-gray-300'">
              <option value="">-- Pilih --</option>
              <option value="L">Laki-laki</option>
              <option value="P">Perempuan</option>
            </select>
            <p v-if="form.errors.gender" class="mt-1 text-xs text-red-500">{{ form.errors.gender }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Lahir <span class="text-red-500">*</span></label>
            <input v-model="form.birth_date" type="date"
              class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="form.errors.birth_date ? 'border-red-400' : 'border-gray-300'" />
            <p v-if="form.errors.birth_date" class="mt-1 text-xs text-red-500">{{ form.errors.birth_date }}</p>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Tempat Lahir <span class="text-red-500">*</span></label>
          <input v-model="form.birth_place" type="text"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.birth_place ? 'border-red-400' : 'border-gray-300'"
            placeholder="Kota tempat lahir" />
          <p v-if="form.errors.birth_place" class="mt-1 text-xs text-red-500">{{ form.errors.birth_place }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Alamat Lengkap <span class="text-red-500">*</span></label>
          <textarea v-model="form.address" rows="3"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.address ? 'border-red-400' : 'border-gray-300'"
            placeholder="Alamat lengkap domisili" />
          <p v-if="form.errors.address" class="mt-1 text-xs text-red-500">{{ form.errors.address }}</p>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-700">
          Nomor rekam medis akan dibuat otomatis oleh sistem setelah data tersimpan.
        </div>

        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
          <Link :href="route('parent.registrations')"
            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
            Batal
          </Link>
          <button type="submit" :disabled="form.processing"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors disabled:opacity-50">
            {{ form.processing ? 'Menyimpan...' : 'Simpan Data Anak' }}
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
  name:        '',
  nik:         '',
  gender:      '',
  birth_date:  '',
  birth_place: '',
  address:     '',
})

function submit() {
  form.post(route('parent.children.store'))
}
</script>

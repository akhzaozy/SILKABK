<template>
  <AppLayout title="Tambah Pengguna">
    <div class="max-w-2xl space-y-5">

      <!-- Header -->
      <div class="flex items-center gap-3">
        <Link :href="route('users.index')" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
          <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Tambah Pengguna Baru</h2>
          <p class="text-sm text-gray-500">Buat akun untuk pengguna sistem</p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
          <input v-model="form.name" type="text"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.name ? 'border-red-400' : 'border-gray-300'"
            placeholder="Nama lengkap pengguna" />
          <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
          <input v-model="form.email" type="email"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.email ? 'border-red-400' : 'border-gray-300'"
            placeholder="email@uptdblkabk.id" />
          <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Role <span class="text-red-500">*</span></label>
          <select v-model="form.role"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.role ? 'border-red-400' : 'border-gray-300'">
            <option value="">-- Pilih Role --</option>
            <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
          </select>
          <p v-if="form.errors.role" class="mt-1 text-xs text-red-500">{{ form.errors.role }}</p>
        </div>

        <!-- Jenis Terapi — hanya muncul jika role = terapis -->
        <div v-if="form.role === 'terapis'"
          class="bg-blue-50 border border-blue-200 rounded-xl p-4 space-y-3">
          <p class="text-sm font-semibold text-blue-800">Profil Terapis</p>
          <div>
            <label class="block text-sm font-medium text-blue-800 mb-1.5">
              Jenis Terapi <span class="text-red-500">*</span>
            </label>
            <select v-model="form.therapy_type_id"
              class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
              :class="form.errors.therapy_type_id ? 'border-red-400' : 'border-blue-300'">
              <option value="">-- Pilih Jenis Terapi --</option>
              <option v-for="t in therapyTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
            <p v-if="form.errors.therapy_type_id" class="mt-1 text-xs text-red-500">{{ form.errors.therapy_type_id }}</p>
            <p class="mt-1 text-xs text-blue-600">Menentukan spesialisasi terapi yang ditangani terapis ini.</p>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Password <span class="text-red-500">*</span></label>
          <input v-model="form.password" type="password"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.password ? 'border-red-400' : 'border-gray-300'"
            placeholder="Minimal 8 karakter" />
          <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password <span class="text-red-500">*</span></label>
          <input v-model="form.password_confirmation" type="password"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 border-gray-300"
            placeholder="Ulangi password" />
        </div>

        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
          <Link :href="route('users.index')"
            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
            Batal
          </Link>
          <button type="submit" :disabled="form.processing"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors disabled:opacity-50">
            {{ form.processing ? 'Menyimpan...' : 'Simpan Pengguna' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { watch } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  roles:        { type: Array, default: () => [] },
  therapyTypes: { type: Array, default: () => [] },
})

const form = useForm({
  name:                  '',
  email:                 '',
  role:                  '',
  therapy_type_id:       '',
  password:              '',
  password_confirmation: '',
})

// Reset therapy_type_id jika role diganti bukan terapis
watch(() => form.role, (role) => {
  if (role !== 'terapis') form.therapy_type_id = ''
})

function submit() {
  form.post(route('users.store'))
}
</script>

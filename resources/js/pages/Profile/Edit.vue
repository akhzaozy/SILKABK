<template>
  <AppLayout title="Edit Profil">
    <div class="max-w-xl space-y-5">

      <!-- Header -->
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0">
          <span class="text-white text-xl font-bold">{{ user.name?.charAt(0)?.toUpperCase() }}</span>
        </div>
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Edit Profil</h2>
          <p class="text-sm text-gray-500 capitalize">{{ roleLabel }}</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-5">

        <!-- Informasi Akun -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
          <h3 class="font-semibold text-gray-800 pb-3 border-b border-gray-100">Informasi Akun</h3>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
            <input v-model="form.name" type="text"
              class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="form.errors.name ? 'border-red-400' : 'border-gray-300'" />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
            <input v-model="form.email" type="email"
              class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="form.errors.email ? 'border-red-400' : 'border-gray-300'" />
            <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
          </div>
        </div>

        <!-- Profil Terapis -->
        <div v-if="user.role === 'terapis'" class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
          <h3 class="font-semibold text-gray-800 pb-3 border-b border-gray-100">Profil Terapis</h3>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Spesialisasi / Jenis Terapi</label>
            <input v-model="form.specialization" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="cth: Terapi Wicara" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">No. Telepon</label>
            <input v-model="form.phone" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="cth: 081234567890" />
          </div>
        </div>

        <!-- Profil Dokter -->
        <div v-if="user.role === 'dokter'" class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
          <h3 class="font-semibold text-gray-800 pb-3 border-b border-gray-100">Profil Dokter</h3>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Spesialisasi</label>
            <input v-model="form.specialization" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="cth: Dokter Spesialis Anak" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">No. SIP / Lisensi</label>
            <input v-model="form.license_number" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Nomor Surat Izin Praktik" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">No. Telepon</label>
            <input v-model="form.phone" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="cth: 081234567890" />
          </div>
        </div>

        <!-- Profil Psikolog -->
        <div v-if="user.role === 'psikolog'" class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
          <h3 class="font-semibold text-gray-800 pb-3 border-b border-gray-100">Profil Psikolog</h3>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Spesialisasi</label>
            <input v-model="form.spesialisasi" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="cth: Psikolog Klinis Anak" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nomor SIP Psikolog</label>
            <input v-model="form.nomor_sip" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Nomor Surat Izin Praktik" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">No. Telepon</label>
            <input v-model="form.telepon" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="cth: 081234567890" />
          </div>
        </div>

        <!-- Profil Orang Tua -->
        <div v-if="user.role === 'orang_tua'" class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
          <h3 class="font-semibold text-gray-800 pb-3 border-b border-gray-100">Profil Orang Tua / Wali</h3>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Hubungan dengan Anak</label>
            <select v-model="form.relationship"
              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">-- Pilih --</option>
              <option value="Ayah">Ayah</option>
              <option value="Ibu">Ibu</option>
              <option value="Wali">Wali</option>
              <option value="Kakek">Kakek</option>
              <option value="Nenek">Nenek</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">No. Telepon</label>
            <input v-model="form.phone" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="cth: 081234567890" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Alamat</label>
            <textarea v-model="form.address" rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Alamat lengkap domisili" />
          </div>
        </div>

        <!-- Ganti Password -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
          <h3 class="font-semibold text-gray-800 pb-3 border-b border-gray-100">
            Ganti Password
            <span class="text-sm font-normal text-gray-400 ml-2">(kosongkan jika tidak ingin mengubah)</span>
          </h3>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Password Baru</label>
            <input v-model="form.password" type="password"
              class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="form.errors.password ? 'border-red-400' : 'border-gray-300'"
              placeholder="Minimal 8 karakter" />
            <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password Baru</label>
            <input v-model="form.password_confirmation" type="password"
              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Ulangi password baru" />
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
          <Link :href="route('dashboard')"
            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
            Batal
          </Link>
          <button type="submit" :disabled="form.processing"
            class="px-5 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors disabled:opacity-50">
            {{ form.processing ? 'Menyimpan...' : 'Simpan Profil' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  user: { type: Object, required: true },
})

const roleLabels = {
  super_admin: 'Super Admin',
  admin:       'Admin',
  orang_tua:   'Orang Tua',
  terapis:     'Terapis',
  dokter:      'Dokter',
  psikolog:    'Psikolog',
}
const roleLabel = computed(() => roleLabels[props.user.role] ?? props.user.role)

const therapist    = props.user.therapist
const doctor       = props.user.doctor
const psychologist = props.user.psychologist
const parent       = props.user.parent_guardian

const form = useForm({
  // Akun
  name:     props.user.name,
  email:    props.user.email,
  password: '',
  password_confirmation: '',

  // Terapis
  specialization: therapist?.specialization ?? doctor?.specialization ?? '',
  phone:          therapist?.phone ?? doctor?.phone ?? parent?.phone ?? '',
  license_number: doctor?.license_number ?? '',

  // Psikolog
  spesialisasi: psychologist?.spesialisasi ?? '',
  nomor_sip:    psychologist?.nomor_sip    ?? '',
  telepon:      psychologist?.telepon      ?? '',

  // Orang tua
  relationship: parent?.relationship ?? '',
  address:      parent?.address      ?? '',
})

function submit() {
  form.put(route('profile.update'))
}
</script>

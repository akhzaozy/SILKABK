<template>
  <AppLayout title="Tambah Pasien">
    <div class="max-w-3xl space-y-5">

      <!-- Header -->
      <div class="flex items-center gap-3">
        <Link :href="route('patients.index')" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
          <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Tambah Pasien Baru</h2>
          <p class="text-sm text-gray-500">Daftarkan data pasien ke dalam sistem</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-5">

        <!-- Data Pasien -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">
          <h3 class="font-semibold text-gray-800 pb-2 border-b border-gray-100">Data Anak / Pasien</h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Nomor Rekam Medis <span class="text-red-500">*</span></label>
              <input v-model="form.medical_record_number" type="text"
                class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="form.errors.medical_record_number ? 'border-red-400' : 'border-gray-300'"
                placeholder="Contoh: RM-2024-001"
              />
              <p v-if="form.errors.medical_record_number" class="mt-1 text-xs text-red-500">{{ form.errors.medical_record_number }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
              <input v-model="form.name" type="text"
                class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="form.errors.name ? 'border-red-400' : 'border-gray-300'"
                placeholder="Nama lengkap anak"
              />
              <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                NIK <span class="text-gray-400 font-normal">(opsional)</span>
              </label>
              <input v-model="form.nik" type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="form.errors.nik ? 'border-red-400' : 'border-gray-300'"
                placeholder="16 digit NIK anak" maxlength="20"
              />
              <p v-if="form.errors.nik" class="mt-1 text-xs text-red-500">{{ form.errors.nik }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
              <select v-model="form.gender"
                class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="form.errors.gender ? 'border-red-400' : 'border-gray-300'"
              >
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
                :class="form.errors.birth_date ? 'border-red-400' : 'border-gray-300'"
              />
              <p v-if="form.errors.birth_date" class="mt-1 text-xs text-red-500">{{ form.errors.birth_date }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Tempat Lahir <span class="text-red-500">*</span></label>
              <input v-model="form.birth_place" type="text"
                class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="form.errors.birth_place ? 'border-red-400' : 'border-gray-300'"
                placeholder="Kota tempat lahir"
              />
              <p v-if="form.errors.birth_place" class="mt-1 text-xs text-red-500">{{ form.errors.birth_place }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Orang Tua / Wali <span class="text-red-500">*</span></label>
              <select v-model="form.guardian_id"
                class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="form.errors.guardian_id ? 'border-red-400' : 'border-gray-300'"
              >
                <option value="">-- Pilih Wali --</option>
                <option v-for="g in guardians" :key="g.id" :value="g.id">{{ g.name }}</option>
              </select>
              <p v-if="form.errors.guardian_id" class="mt-1 text-xs text-red-500">{{ form.errors.guardian_id }}</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Alamat <span class="text-red-500">*</span></label>
            <textarea v-model="form.address" rows="2"
              class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="form.errors.address ? 'border-red-400' : 'border-gray-300'"
              placeholder="Alamat lengkap"
            />
            <p v-if="form.errors.address" class="mt-1 text-xs text-red-500">{{ form.errors.address }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Diagnosis Awal</label>
            <textarea v-model="form.diagnosis" rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Diagnosis awal / kondisi anak (opsional)"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Catatan</label>
            <textarea v-model="form.notes" rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Catatan tambahan (opsional)"
            />
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
          <Link :href="route('patients.index')"
            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
            Batal
          </Link>
          <button type="submit" :disabled="form.processing"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors disabled:opacity-50">
            {{ form.processing ? 'Menyimpan...' : 'Simpan Pasien' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

defineProps({
  guardians: { type: Array, default: () => [] },
})

const form = useForm({
  guardian_id:            '',
  medical_record_number:  '',
  name:                   '',
  nik:                    '',
  gender:                 '',
  birth_date:             '',
  birth_place:            '',
  address:                '',
  diagnosis:              '',
  notes:                  '',
})

function submit() {
  form.post(route('patients.store'))
}
</script>

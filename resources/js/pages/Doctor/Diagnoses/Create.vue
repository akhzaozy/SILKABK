<template>
  <AppLayout title="Tambah Diagnosa">
    <div class="max-w-2xl space-y-5">

      <div>
        <h2 class="text-lg font-semibold text-gray-800">Diagnosa Baru</h2>
        <p class="text-sm text-gray-500 mt-0.5">Catat diagnosa penyakit pasien</p>
      </div>

      <form @submit.prevent="submit" class="space-y-5">

        <!-- Pasien & Tanggal -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
          <h3 class="font-semibold text-gray-800 pb-3 border-b border-gray-100">Informasi Dasar</h3>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
              Pasien <span class="text-red-500">*</span>
            </label>
            <select v-model="form.patient_id"
              class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="form.errors.patient_id ? 'border-red-400' : 'border-gray-300'">
              <option value="">-- Pilih Pasien --</option>
              <option v-for="p in patients" :key="p.id" :value="p.id">
                {{ p.name }} ({{ p.medical_record_number }})
              </option>
            </select>
            <p v-if="form.errors.patient_id" class="mt-1 text-xs text-red-500">{{ form.errors.patient_id }}</p>
          </div>

          <!-- Info pasien terpilih -->
          <div v-if="selectedPatient" class="bg-gray-50 rounded-xl border border-gray-200 p-4 grid grid-cols-2 gap-3 text-sm">
            <div>
              <p class="text-xs font-medium text-gray-400 uppercase">Nama</p>
              <p class="font-medium text-gray-800">{{ selectedPatient.name }}</p>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-400 uppercase">No. RM</p>
              <p class="font-mono text-gray-700">{{ selectedPatient.medical_record_number }}</p>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-400 uppercase">Tanggal Lahir</p>
              <p class="text-gray-700">{{ formatDate(selectedPatient.birth_date) }}</p>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-400 uppercase">Jenis Kelamin</p>
              <p class="text-gray-700">{{ selectedPatient.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
              Tanggal Diagnosa <span class="text-red-500">*</span>
            </label>
            <input v-model="form.diagnosis_date" type="date"
              class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="form.errors.diagnosis_date ? 'border-red-400' : 'border-gray-300'" />
            <p v-if="form.errors.diagnosis_date" class="mt-1 text-xs text-red-500">{{ form.errors.diagnosis_date }}</p>
          </div>
        </div>

        <!-- Gejala & Diagnosa -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
          <h3 class="font-semibold text-gray-800 pb-3 border-b border-gray-100">Gejala & Diagnosa</h3>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
              Gejala yang Dialami <span class="text-red-500">*</span>
            </label>
            <textarea v-model="form.symptoms" rows="4"
              class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="form.errors.symptoms ? 'border-red-400' : 'border-gray-300'"
              placeholder="Deskripsikan gejala yang dialami pasien..." />
            <p v-if="form.errors.symptoms" class="mt-1 text-xs text-red-500">{{ form.errors.symptoms }}</p>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Diagnosa Penyakit <span class="text-red-500">*</span>
              </label>
              <input v-model="form.diagnosis_name" type="text"
                class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="form.errors.diagnosis_name ? 'border-red-400' : 'border-gray-300'"
                placeholder="cth: ISPA, Diare Akut, Stunting..." />
              <p v-if="form.errors.diagnosis_name" class="mt-1 text-xs text-red-500">{{ form.errors.diagnosis_name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Kode ICD-10 <span class="text-gray-400 font-normal">(opsional)</span>
              </label>
              <input v-model="form.icd_code" type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="cth: J06.9" maxlength="20" />
            </div>
          </div>
        </div>

        <!-- Penanganan & Catatan -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
          <h3 class="font-semibold text-gray-800 pb-3 border-b border-gray-100">Penanganan & Catatan</h3>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
              Penanganan / Resep Obat <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <textarea v-model="form.treatment" rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Tuliskan tindakan, resep, atau rujukan..." />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
              Catatan Tambahan <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <textarea v-model="form.notes" rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Catatan lain yang relevan..." />
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
          <Link :href="route('doctor.diagnoses.index')"
            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
            Batal
          </Link>
          <button type="submit" :disabled="form.processing"
            class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors disabled:opacity-50">
            {{ form.processing ? 'Menyimpan...' : 'Simpan Diagnosa' }}
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
  patients: { type: Array, default: () => [] },
  today:    { type: String, default: '' },
})

const form = useForm({
  patient_id:     '',
  diagnosis_date: props.today,
  symptoms:       '',
  diagnosis_name: '',
  icd_code:       '',
  treatment:      '',
  notes:          '',
})

const selectedPatient = computed(() =>
  props.patients.find(p => p.id === form.patient_id) ?? null
)

function submit() {
  form.post(route('doctor.diagnoses.store'))
}

function formatDate(val) {
  if (!val) return '—'
  const d = new Date(String(val).substring(0, 10))
  return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }).format(d)
}
</script>

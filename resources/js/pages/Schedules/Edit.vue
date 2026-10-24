<template>
  <AppLayout title="Edit Jadwal">
    <div class="max-w-2xl space-y-5">

      <!-- Header -->
      <div class="flex items-center gap-3">
        <Link :href="route('schedules.show', schedule.id)" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
          <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Edit Jadwal / Slot</h2>
          <p class="text-sm text-gray-500">{{ formatDate(schedule.schedule_date) }}</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">

        <!-- Tipe Tenaga Profesional -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Tenaga Profesional <span class="text-red-500">*</span></label>
          <div class="grid grid-cols-3 gap-3">
            <label v-for="opt in professionalTypes" :key="opt.value"
              class="flex items-center gap-2 p-3 border-2 rounded-xl cursor-pointer transition-all text-sm"
              :class="form.professional_type === opt.value
                ? 'border-blue-500 bg-blue-50 text-blue-700 font-medium'
                : 'border-gray-200 text-gray-700 hover:border-gray-300'">
              <input type="radio" v-model="form.professional_type" :value="opt.value" class="accent-blue-600" @change="clearProfessional" />
              {{ opt.label }}
            </label>
          </div>
        </div>

        <div v-if="form.professional_type === 'terapis'">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Terapis <span class="text-red-500">*</span></label>
          <select v-model="form.therapist_id"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.therapist_id ? 'border-red-400' : 'border-gray-300'">
            <option value="">-- Pilih Terapis --</option>
            <option v-for="t in therapists" :key="t.id" :value="t.id">{{ t.name }} — {{ t.specialization }}</option>
          </select>
          <p v-if="form.errors.therapist_id" class="mt-1 text-xs text-red-500">{{ form.errors.therapist_id }}</p>
        </div>

        <div v-if="form.professional_type === 'dokter'">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Dokter <span class="text-red-500">*</span></label>
          <select v-model="form.doctor_id"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.doctor_id ? 'border-red-400' : 'border-gray-300'">
            <option value="">-- Pilih Dokter --</option>
            <option v-for="d in doctors" :key="d.id" :value="d.id">Dr. {{ d.name }} — {{ d.specialization }}</option>
          </select>
          <p v-if="form.errors.doctor_id" class="mt-1 text-xs text-red-500">{{ form.errors.doctor_id }}</p>
        </div>

        <div v-if="form.professional_type === 'psikolog'">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Psikolog <span class="text-red-500">*</span></label>
          <select v-model="form.psychologist_id"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.psychologist_id ? 'border-red-400' : 'border-gray-300'">
            <option value="">-- Pilih Psikolog --</option>
            <option v-for="p in psychologists" :key="p.id" :value="p.id">Psi. {{ p.nama }} — {{ p.spesialisasi }}</option>
          </select>
          <p v-if="form.errors.psychologist_id" class="mt-1 text-xs text-red-500">{{ form.errors.psychologist_id }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Layanan <span class="text-red-500">*</span></label>
          <select v-model="form.therapy_type_id"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.therapy_type_id ? 'border-red-400' : 'border-gray-300'">
            <option value="">-- Pilih Jenis Layanan --</option>
            <option v-for="t in therapyTypes" :key="t.id" :value="t.id">
              {{ t.name }} — {{ formatRupiah(t.price_per_hour) }}/jam
            </option>
          </select>
          <p v-if="form.errors.therapy_type_id" class="mt-1 text-xs text-red-500">{{ form.errors.therapy_type_id }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
          <input v-model="form.schedule_date" type="date"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.schedule_date ? 'border-red-400' : 'border-gray-300'" />
          <p v-if="form.errors.schedule_date" class="mt-1 text-xs text-red-500">{{ form.errors.schedule_date }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Jam Mulai <span class="text-red-500">*</span></label>
          <input v-model="form.start_time" type="time"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.start_time ? 'border-red-400' : 'border-gray-300'" />
          <p class="mt-0.5 text-xs text-gray-400">
            Slot otomatis 1 jam.
            <span v-if="autoEndTime" class="text-blue-600 font-medium">Selesai: {{ autoEndTime }}</span>
          </p>
          <p v-if="form.errors.start_time" class="mt-1 text-xs text-red-500">{{ form.errors.start_time }}</p>
        </div>

        <!-- Pasien (opsional) -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Pasien <span class="text-gray-400 font-normal">(opsional — kosong = slot tersedia)</span>
          </label>
          <select v-model="form.patient_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">-- Tanpa Pasien (Slot Tersedia) --</option>
            <option v-for="p in patients" :key="p.id" :value="p.id">{{ p.name }} ({{ p.medical_record_number }})</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Catatan</label>
          <textarea v-model="form.notes" rows="2"
            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Catatan tambahan..." />
        </div>

        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
          <Link :href="route('schedules.show', schedule.id)"
            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
            Batal
          </Link>
          <button type="submit" :disabled="form.processing"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors disabled:opacity-50">
            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
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
  schedule:      { type: Object, required: true },
  patients:      { type: Array,  default: () => [] },
  therapists:    { type: Array,  default: () => [] },
  doctors:       { type: Array,  default: () => [] },
  psychologists: { type: Array,  default: () => [] },
  therapyTypes:  { type: Array,  default: () => [] },
})

const professionalTypes = [
  { value: 'terapis',  label: 'Terapis' },
  { value: 'dokter',   label: 'Dokter' },
  { value: 'psikolog', label: 'Psikolog' },
]

const form = useForm({
  professional_type: props.schedule.professional_type ?? 'terapis',
  therapist_id:      props.schedule.therapist_id      ?? '',
  doctor_id:         props.schedule.doctor_id         ?? '',
  psychologist_id:   props.schedule.psychologist_id   ?? '',
  patient_id:        props.schedule.patient_id        ?? '',
  therapy_type_id:   props.schedule.therapy_type_id,
  schedule_date:     props.schedule.schedule_date,
  start_time:        props.schedule.start_time,
  notes:             props.schedule.notes ?? '',
})

const autoEndTime = computed(() => {
  if (!form.start_time) return null
  const [h, m] = form.start_time.split(':').map(Number)
  const total  = h * 60 + m + 60
  return `${String(Math.floor(total / 60)).padStart(2, '0')}:${String(total % 60).padStart(2, '0')}`
})

function clearProfessional() {
  form.therapist_id    = ''
  form.doctor_id       = ''
  form.psychologist_id = ''
}

function submit() {
  form.put(route('schedules.update', props.schedule.id))
}

function formatRupiah(val) {
  return 'Rp ' + Number(val ?? 0).toLocaleString('id-ID')
}

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}
</script>

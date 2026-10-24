<template>
  <AppLayout title="Kelola Jadwal Praktik">
    <div class="max-w-2xl space-y-5">

      <!-- Header -->
      <div class="flex items-center gap-3">
        <Link :href="route('schedules.index')" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
          <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Kelola Jadwal Praktik</h2>
          <p class="text-sm text-gray-500">Generate otomatis satu minggu atau buat slot 1 jam secara manual</p>
        </div>
      </div>

      <!-- Mode tabs -->
      <div class="flex gap-1 bg-gray-100 rounded-xl p-1">
        <button type="button" @click="mode = 'generate'"
          :class="mode === 'generate'
            ? 'flex-1 py-2 text-sm font-semibold bg-white rounded-lg shadow-sm text-blue-700'
            : 'flex-1 py-2 text-sm text-gray-600 rounded-lg hover:text-gray-800'">
          Generate Otomatis (1 minggu)
        </button>
        <button type="button" @click="mode = 'manual'"
          :class="mode === 'manual'
            ? 'flex-1 py-2 text-sm font-semibold bg-white rounded-lg shadow-sm text-blue-700'
            : 'flex-1 py-2 text-sm text-gray-600 rounded-lg hover:text-gray-800'">
          Buat Slot Manual
        </button>
      </div>

      <!-- ─── MODE: GENERATE OTOMATIS ─────────────────────────── -->
      <form v-if="mode === 'generate'" @submit.prevent="submitGenerate"
        class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
          <p class="font-medium mb-1">Template Jadwal Praktik</p>
          <ul class="space-y-0.5 text-blue-700">
            <li>Senin – Kamis, Sabtu : 08:00 – 12:00 (4 slot × 1 jam)</li>
            <li>Jumat : 08:00 – 10:30 (2 slot × 1 jam)</li>
          </ul>
          <p class="mt-2 text-xs text-blue-600">Slot yang sudah ada tidak akan dibuat ulang (skip otomatis).</p>
        </div>

        <!-- Tipe Profesional -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Tenaga Profesional <span class="text-red-500">*</span></label>
          <div class="grid grid-cols-3 gap-3">
            <label v-for="opt in professionalTypes" :key="opt.value"
              class="flex items-center gap-2 p-3 border-2 rounded-xl cursor-pointer transition-all text-sm"
              :class="gen.professional_type === opt.value
                ? 'border-blue-500 bg-blue-50 text-blue-700 font-medium'
                : 'border-gray-200 text-gray-700 hover:border-gray-300'">
              <input type="radio" v-model="gen.professional_type" :value="opt.value" class="accent-blue-600" @change="clearGenProfessional" />
              {{ opt.label }}
            </label>
          </div>
        </div>

        <div v-if="gen.professional_type === 'terapis'">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Terapis <span class="text-red-500">*</span></label>
          <select v-model="gen.therapist_id"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="gen.errors.therapist_id ? 'border-red-400' : 'border-gray-300'">
            <option value="">-- Pilih Terapis --</option>
            <option v-for="t in therapists" :key="t.id" :value="t.id">{{ t.name }} — {{ t.specialization }}</option>
          </select>
          <p v-if="gen.errors.therapist_id" class="mt-1 text-xs text-red-500">{{ gen.errors.therapist_id }}</p>
        </div>

        <div v-if="gen.professional_type === 'dokter'">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Dokter <span class="text-red-500">*</span></label>
          <select v-model="gen.doctor_id"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="gen.errors.doctor_id ? 'border-red-400' : 'border-gray-300'">
            <option value="">-- Pilih Dokter --</option>
            <option v-for="d in doctors" :key="d.id" :value="d.id">Dr. {{ d.name }} — {{ d.specialization }}</option>
          </select>
          <p v-if="gen.errors.doctor_id" class="mt-1 text-xs text-red-500">{{ gen.errors.doctor_id }}</p>
        </div>

        <div v-if="gen.professional_type === 'psikolog'">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Psikolog <span class="text-red-500">*</span></label>
          <select v-model="gen.psychologist_id"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="gen.errors.psychologist_id ? 'border-red-400' : 'border-gray-300'">
            <option value="">-- Pilih Psikolog --</option>
            <option v-for="p in psychologists" :key="p.id" :value="p.id">Psi. {{ p.nama }} — {{ p.spesialisasi }}</option>
          </select>
          <p v-if="gen.errors.psychologist_id" class="mt-1 text-xs text-red-500">{{ gen.errors.psychologist_id }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Layanan <span class="text-red-500">*</span></label>
          <select v-model="gen.therapy_type_id"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="gen.errors.therapy_type_id ? 'border-red-400' : 'border-gray-300'">
            <option value="">-- Pilih Jenis Layanan --</option>
            <option v-for="t in therapyTypes" :key="t.id" :value="t.id">
              {{ t.name }} — {{ formatRupiah(t.price_per_hour) }}/jam
            </option>
          </select>
          <p v-if="gen.errors.therapy_type_id" class="mt-1 text-xs text-red-500">{{ gen.errors.therapy_type_id }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Tanggal dalam minggu yang ingin di-generate <span class="text-red-500">*</span>
          </label>
          <input v-model="gen.week_start" type="date"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="gen.errors.week_start ? 'border-red-400' : 'border-gray-300'" />
          <p class="mt-0.5 text-xs text-gray-400">Sistem otomatis mencari Senin dari tanggal yang dipilih.</p>
          <p v-if="gen.errors.week_start" class="mt-1 text-xs text-red-500">{{ gen.errors.week_start }}</p>
          <p v-if="previewMonday" class="mt-1 text-xs text-blue-600 font-medium">
            Minggu: Senin {{ previewMonday }} s/d Sabtu {{ previewSaturday }}
          </p>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
          <Link :href="route('schedules.index')"
            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
            Batal
          </Link>
          <button type="submit" :disabled="gen.processing"
            class="px-5 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors disabled:opacity-50">
            {{ gen.processing ? 'Membuat Slot...' : 'Generate Slot Minggu Ini' }}
          </button>
        </div>
      </form>

      <!-- ─── MODE: BUAT MANUAL ───────────────────────────────── -->
      <form v-else @submit.prevent="submitManual"
        class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Tenaga Profesional <span class="text-red-500">*</span></label>
          <div class="grid grid-cols-3 gap-3">
            <label v-for="opt in professionalTypes" :key="opt.value"
              class="flex items-center gap-2 p-3 border-2 rounded-xl cursor-pointer transition-all text-sm"
              :class="form.professional_type === opt.value
                ? 'border-blue-500 bg-blue-50 text-blue-700 font-medium'
                : 'border-gray-200 text-gray-700 hover:border-gray-300'">
              <input type="radio" v-model="form.professional_type" :value="opt.value" class="accent-blue-600" @change="clearFormProfessional" />
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

        <div class="border-t border-gray-100 pt-5">
          <div class="flex items-center justify-between mb-2">
            <label class="block text-sm font-medium text-gray-700">
              Pasien <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <span class="text-xs text-gray-500">Kosongkan → slot tersedia untuk booking orang tua</span>
          </div>
          <select v-model="form.patient_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">-- Tanpa Pasien (Slot Tersedia) --</option>
            <option v-for="p in patients" :key="p.id" :value="p.id">
              {{ p.name }} ({{ p.medical_record_number }})
            </option>
          </select>
          <div class="mt-2 text-xs px-3 py-2 rounded-lg"
            :class="form.patient_id ? 'bg-green-50 text-green-700' : 'bg-blue-50 text-blue-700'">
            {{ form.patient_id
              ? 'Jadwal langsung dikonfirmasi untuk pasien yang dipilih.'
              : 'Slot akan ditampilkan ke orang tua saat mendaftar layanan.' }}
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Catatan <span class="text-gray-400 font-normal">(opsional)</span></label>
          <textarea v-model="form.notes" rows="2"
            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Catatan tambahan..." />
        </div>

        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
          <Link :href="route('schedules.index')"
            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
            Batal
          </Link>
          <button type="submit" :disabled="form.processing"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors disabled:opacity-50">
            {{ form.processing ? 'Menyimpan...' : (form.patient_id ? 'Buat Jadwal' : 'Buat Slot Tersedia') }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

defineProps({
  patients:      { type: Array, default: () => [] },
  therapists:    { type: Array, default: () => [] },
  doctors:       { type: Array, default: () => [] },
  psychologists: { type: Array, default: () => [] },
  therapyTypes:  { type: Array, default: () => [] },
})

const mode = ref('generate')

const professionalTypes = [
  { value: 'terapis',  label: 'Terapis' },
  { value: 'dokter',   label: 'Dokter' },
  { value: 'psikolog', label: 'Psikolog' },
]

// ─── Generate form ────────────────────────────────────────────
const gen = useForm({
  professional_type: 'terapis',
  therapist_id:      '',
  doctor_id:         '',
  psychologist_id:   '',
  therapy_type_id:   '',
  week_start:        '',
})

function clearGenProfessional() {
  gen.therapist_id    = ''
  gen.doctor_id       = ''
  gen.psychologist_id = ''
}

function submitGenerate() {
  gen.post(route('schedules.generate'))
}

const previewMonday = computed(() => {
  if (!gen.week_start) return null
  const d   = new Date(gen.week_start + 'T00:00:00')
  const day  = d.getDay()
  const diff = day === 0 ? -6 : 1 - day
  d.setDate(d.getDate() + diff)
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
})

const previewSaturday = computed(() => {
  if (!gen.week_start) return null
  const d   = new Date(gen.week_start + 'T00:00:00')
  const day  = d.getDay()
  const diff = day === 0 ? -6 : 1 - day
  d.setDate(d.getDate() + diff + 5)
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
})

// ─── Manual form ──────────────────────────────────────────────
const form = useForm({
  professional_type: 'terapis',
  therapist_id:      '',
  doctor_id:         '',
  psychologist_id:   '',
  patient_id:        '',
  therapy_type_id:   '',
  schedule_date:     '',
  start_time:        '',
  notes:             '',
})

function clearFormProfessional() {
  form.therapist_id    = ''
  form.doctor_id       = ''
  form.psychologist_id = ''
}

const autoEndTime = computed(() => {
  if (!form.start_time) return null
  const [h, m] = form.start_time.split(':').map(Number)
  const total  = h * 60 + m + 60
  return `${String(Math.floor(total / 60)).padStart(2, '0')}:${String(total % 60).padStart(2, '0')}`
})

function submitManual() {
  form.post(route('schedules.store'))
}

function formatRupiah(val) {
  return 'Rp ' + Number(val ?? 0).toLocaleString('id-ID')
}
</script>

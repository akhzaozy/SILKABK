<template>
  <AppLayout title="Booking Terapi">
    <div class="space-y-5">

      <div>
        <h2 class="text-xl font-bold text-gray-800">Booking Terapi</h2>
        <p class="text-sm text-gray-500 mt-0.5">Pilih tenaga profesional dan jadwal yang tersedia</p>
      </div>

      <!-- Tidak ada slot -->
      <div v-if="availableSlots.length === 0" class="bg-amber-50 border border-amber-200 rounded-2xl p-5 space-y-1">
        <p class="text-sm text-amber-800 font-medium">Belum ada jadwal tersedia</p>
        <p class="text-sm text-amber-700">
          Semua slot untuk hari ini sudah lewat atau sudah dibooking.
          Admin perlu membuat slot jadwal untuk hari-hari mendatang.
        </p>
        <p class="text-sm text-amber-700">Silakan coba lagi nanti atau hubungi admin.</p>
      </div>

      <form v-else @submit.prevent="submit" class="space-y-5">

        <!-- ① Tipe & Data Pasien -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
          <h3 class="font-semibold text-gray-800">Data Pasien</h3>
          <div class="grid grid-cols-2 gap-3">
            <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all"
              :class="form.patient_type === 'existing' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
              <input type="radio" v-model="form.patient_type" value="existing" class="accent-blue-600" />
              <div>
                <p class="text-sm font-medium text-gray-800">Pasien Lama</p>
                <p class="text-xs text-gray-500">Anak sudah terdaftar</p>
              </div>
            </label>
            <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all"
              :class="form.patient_type === 'new' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
              <input type="radio" v-model="form.patient_type" value="new" class="accent-blue-600" />
              <div>
                <p class="text-sm font-medium text-gray-800">Pasien Baru</p>
                <p class="text-xs text-gray-500">Daftarkan anak baru</p>
              </div>
            </label>
          </div>

          <!-- Pasien lama -->
          <template v-if="form.patient_type === 'existing'">
            <div v-if="patients.length === 0" class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-xl p-4">
              Belum ada anak yang terdaftar. Hubungi admin untuk mendaftarkan data anak terlebih dahulu.
            </div>
            <div v-else>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Anak <span class="text-red-500">*</span></label>
              <select v-model="form.patient_id"
                class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="form.errors.patient_id ? 'border-red-400' : 'border-gray-300'">
                <option value="">-- Pilih Anak --</option>
                <option v-for="p in patients" :key="p.id" :value="p.id">{{ p.name }} ({{ p.medical_record_number }})</option>
              </select>
              <p v-if="form.errors.patient_id" class="mt-1 text-xs text-red-500">{{ form.errors.patient_id }}</p>
            </div>
            <div v-if="selectedPatient" class="bg-gray-50 rounded-xl border border-gray-200 p-4 grid grid-cols-2 gap-3 text-sm">
              <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Nama Lengkap</p>
                <p class="text-gray-800 font-medium">{{ selectedPatient.name }}</p>
              </div>
              <div>
                <p class="text-xs font-medium text-gray-500 uppercase">No. Rekam Medis</p>
                <p class="text-gray-800 font-mono">{{ selectedPatient.medical_record_number }}</p>
              </div>
              <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Tanggal Lahir</p>
                <p class="text-gray-800">{{ formatDate(selectedPatient.birth_date) }}</p>
              </div>
              <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Orang Tua / Wali</p>
                <p class="text-gray-800">{{ selectedPatient.guardian?.name || '—' }}</p>
              </div>
            </div>
          </template>

          <!-- Pasien baru -->
          <template v-if="form.patient_type === 'new'">
            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" v-model="form.new_name"
                  class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="form.errors.new_name ? 'border-red-400' : 'border-gray-300'"
                  placeholder="Nama lengkap anak" />
                <p v-if="form.errors.new_name" class="mt-1 text-xs text-red-500">{{ form.errors.new_name }}</p>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">NIK Anak <span class="text-gray-400 font-normal">(opsional)</span></label>
                <input type="text" v-model="form.new_nik"
                  class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="16 digit NIK anak" maxlength="20" />
                <p v-if="form.errors.new_nik" class="mt-1 text-xs text-red-500">{{ form.errors.new_nik }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                <select v-model="form.new_gender"
                  class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="form.errors.new_gender ? 'border-red-400' : 'border-gray-300'">
                  <option value="">-- Pilih --</option>
                  <option value="L">Laki-laki</option>
                  <option value="P">Perempuan</option>
                </select>
                <p v-if="form.errors.new_gender" class="mt-1 text-xs text-red-500">{{ form.errors.new_gender }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Lahir <span class="text-red-500">*</span></label>
                <input type="date" v-model="form.new_birth_date"
                  class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="form.errors.new_birth_date ? 'border-red-400' : 'border-gray-300'" />
                <p v-if="form.errors.new_birth_date" class="mt-1 text-xs text-red-500">{{ form.errors.new_birth_date }}</p>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Tempat Lahir <span class="text-red-500">*</span></label>
                <input type="text" v-model="form.new_birth_place"
                  class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="form.errors.new_birth_place ? 'border-red-400' : 'border-gray-300'"
                  placeholder="Kota tempat lahir" />
                <p v-if="form.errors.new_birth_place" class="mt-1 text-xs text-red-500">{{ form.errors.new_birth_place }}</p>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Alamat Lengkap <span class="text-red-500">*</span></label>
                <textarea v-model="form.new_address" rows="2"
                  class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="form.errors.new_address ? 'border-red-400' : 'border-gray-300'"
                  placeholder="Alamat lengkap domisili" />
                <p v-if="form.errors.new_address" class="mt-1 text-xs text-red-500">{{ form.errors.new_address }}</p>
              </div>
            </div>
          </template>

          <!-- Keluhan -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
              Keluhan / Alasan Konsultasi <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <textarea v-model="form.notes" rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Jelaskan keluhan atau kondisi anak..." />
          </div>
        </div>

        <!-- ② Booking Jadwal — dua panel -->
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Pilih Jadwal <span class="text-red-500">*</span></h3>
            <p class="text-xs text-gray-500 mt-0.5">Setiap sesi berdurasi 1 jam</p>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 min-h-80">

            <!-- Panel Kiri: Daftar Profesional -->
            <div class="border-b lg:border-b-0 lg:border-r border-gray-100 p-5 space-y-2 overflow-y-auto max-h-96 lg:max-h-none">
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Pilih Terapis / Profesional</p>

              <button v-for="prof in professionals" :key="prof.key"
                type="button"
                @click="selectProfessional(prof.key)"
                class="w-full flex items-center gap-3 p-3 rounded-xl border-2 transition-all text-left"
                :class="selectedProfKey === prof.key
                  ? 'border-blue-500 bg-blue-50'
                  : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'">
                <!-- Avatar -->
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 text-white text-xs font-bold"
                  :class="avatarColor(prof.type)">
                  {{ initials(prof.name) }}
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold text-gray-800 truncate">{{ prof.name }}</p>
                  <p class="text-xs text-gray-500 truncate">{{ prof.spec }}</p>
                </div>
                <span class="text-xs font-semibold px-2 py-1 rounded-lg flex-shrink-0"
                  :class="selectedProfKey === prof.key ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'">
                  {{ prof.slotCount }} slot
                </span>
              </button>
            </div>

            <!-- Panel Kanan: Jam Tersedia -->
            <div class="p-5">
              <!-- Placeholder belum pilih -->
              <div v-if="!selectedProfKey" class="h-full flex flex-col items-center justify-center py-12 text-center text-gray-400 gap-3">
                <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm">Pilih tenaga profesional terlebih dahulu</p>
              </div>

              <div v-else class="space-y-4">
                <!-- Info profesional terpilih -->
                <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-3">
                  <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 text-white text-xs font-bold"
                    :class="avatarColor(selectedProfessional?.type)">
                    {{ initials(selectedProfessional?.name) }}
                  </div>
                  <div>
                    <p class="text-xs text-gray-500">Terapis Terpilih:</p>
                    <p class="text-sm font-semibold text-gray-800">{{ selectedProfessional?.name }}</p>
                    <p class="text-xs text-gray-500">{{ selectedProfessional?.spec }}</p>
                  </div>
                </div>

                <!-- Pilih Tanggal (jika lebih dari 1 tanggal) -->
                <div v-if="datesForProfessional.length > 1">
                  <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Pilih Tanggal:</p>
                  <div class="flex flex-wrap gap-2">
                    <button v-for="d in datesForProfessional" :key="d"
                      type="button"
                      @click="selectedDate = d; form.schedule_id = ''"
                      class="px-3 py-1.5 text-xs font-medium rounded-xl border-2 transition-all"
                      :class="selectedDate === d
                        ? 'bg-blue-600 text-white border-blue-600'
                        : 'border-gray-200 text-gray-600 hover:border-blue-300'">
                      {{ formatDateShort(d) }}
                    </button>
                  </div>
                </div>

                <!-- Label tanggal jika hanya 1 -->
                <div v-else-if="datesForProfessional.length === 1">
                  <p class="text-xs font-semibold text-gray-500 uppercase">Tanggal:</p>
                  <p class="text-sm text-gray-700 font-medium mt-0.5">{{ formatDate(datesForProfessional[0]) }}</p>
                </div>

                <!-- Tombol jam terapi -->
                <div v-if="slotsForProfessional.length">
                  <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Pilih Jam Terapi:</p>
                  <div class="grid grid-cols-3 gap-2">
                    <button v-for="slot in slotsForProfessional" :key="slot.id"
                      type="button"
                      @click="form.schedule_id = slot.id"
                      class="py-3 text-sm font-bold rounded-xl border-2 transition-all"
                      :class="form.schedule_id == slot.id
                        ? 'bg-blue-600 text-white border-blue-600 shadow-md'
                        : 'border-gray-200 text-gray-700 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50'">
                      {{ slot.start_time.slice(0, 5) }}
                    </button>
                  </div>
                </div>
                <p v-else class="text-sm text-gray-400 text-center py-4">Tidak ada slot tersedia untuk tanggal ini.</p>

                <!-- Ringkasan slot terpilih -->
                <div v-if="selectedSlot" class="bg-blue-600 rounded-xl p-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-xs text-blue-200">Konfirmasi Booking:</p>
                      <p class="text-sm font-bold text-white">{{ selectedSlot.therapy_type?.name }}</p>
                      <p class="text-xs text-blue-200 mt-0.5">
                        {{ formatDateShort(selectedSlot.schedule_date) }} · {{ selectedSlot.start_time.slice(0, 5) }} – {{ selectedSlot.end_time.slice(0, 5) }}
                      </p>
                    </div>
                    <div class="text-right">
                      <p class="text-white font-bold text-sm">{{ formatRupiah(selectedSlot.therapy_type?.price_per_hour) }}</p>
                      <p class="text-xs text-blue-200">per sesi</p>
                    </div>
                  </div>
                </div>

                <p v-if="form.errors.schedule_id" class="text-xs text-red-500">{{ form.errors.schedule_id }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
          <Link :href="route('parent.registrations')"
            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
            Batal
          </Link>
          <button type="submit" :disabled="form.processing || !canSubmit"
            class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors disabled:opacity-50">
            {{ form.processing ? 'Mendaftarkan...' : 'Ajukan Pendaftaran' }}
          </button>
        </div>
      </form>

      <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
        <p class="text-sm text-blue-800 font-medium mb-1">Informasi</p>
        <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
          <li>Setiap sesi berdurasi 1 jam</li>
          <li>Slot yang sudah dibooking tidak ditampilkan</li>
          <li>Pendaftaran dikonfirmasi admin dalam 1–2 hari kerja</li>
        </ul>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  patients:       { type: Array, default: () => [] },
  availableSlots: { type: Array, default: () => [] },
})

const selectedProfKey = ref(null)
const selectedDate    = ref('')

const form = useForm({
  patient_type:    'existing',
  patient_id:      '',
  new_name:        '',
  new_nik:         '',
  new_gender:      '',
  new_birth_date:  '',
  new_birth_place: '',
  new_address:     '',
  notes:           '',
  schedule_id:     '',
})

// ─── Professional helpers ─────────────────────────────────────

function profKey(slot) {
  const id = slot.therapist?.id ?? slot.doctor?.id ?? slot.psychologist?.id
  return `${slot.professional_type}_${id}`
}

function profName(slot) {
  if (slot.therapist)    return slot.therapist.name
  if (slot.doctor)       return 'Dr. ' + slot.doctor.name
  if (slot.psychologist) return 'Psi. ' + slot.psychologist.nama
  return '—'
}

function profSpec(slot) {
  return slot.therapist?.specialization
    ?? slot.doctor?.specialization
    ?? slot.psychologist?.spesialisasi
    ?? slot.therapy_type?.name
    ?? '-'
}

// Daftar profesional unik dari availableSlots
const professionals = computed(() => {
  const map = new Map()
  props.availableSlots.forEach(slot => {
    const key = profKey(slot)
    if (!map.has(key)) {
      map.set(key, {
        key,
        name:      profName(slot),
        spec:      profSpec(slot),
        type:      slot.professional_type,
        slotCount: 0,
      })
    }
    map.get(key).slotCount++
  })
  return [...map.values()]
})

const selectedProfessional = computed(() =>
  professionals.value.find(p => p.key === selectedProfKey.value) ?? null
)

// Tanggal unik untuk profesional terpilih (normalisasi ke YYYY-MM-DD)
const datesForProfessional = computed(() => {
  if (!selectedProfKey.value) return []
  const seen = new Set()
  return props.availableSlots
    .filter(s => profKey(s) === selectedProfKey.value)
    .map(s => dateOnly(s.schedule_date))
    .filter(d => { if (seen.has(d)) return false; seen.add(d); return true })
    .sort()
})

// Slot untuk profesional + tanggal terpilih
const slotsForProfessional = computed(() => {
  if (!selectedProfKey.value || !selectedDate.value) return []
  return props.availableSlots.filter(s =>
    profKey(s) === selectedProfKey.value &&
    dateOnly(s.schedule_date) === selectedDate.value
  ).sort((a, b) => a.start_time.localeCompare(b.start_time))
})

const selectedSlot = computed(() =>
  props.availableSlots.find(s => s.id == form.schedule_id) ?? null
)

const selectedPatient = computed(() =>
  props.patients.find(p => p.id === form.patient_id) ?? null
)

const canSubmit = computed(() => {
  if (!form.schedule_id) return false
  if (form.patient_type === 'existing') return !!form.patient_id
  return !!(form.new_name && form.new_gender && form.new_birth_date && form.new_birth_place && form.new_address)
})

// ─── Actions ─────────────────────────────────────────────────

function selectProfessional(key) {
  selectedProfKey.value = key
  form.schedule_id      = ''
  // Auto-pilih tanggal pertama yang tersedia (sudah ternormalisasi via computed)
  const dates = props.availableSlots
    .filter(s => profKey(s) === key)
    .map(s => dateOnly(s.schedule_date))
  const unique = [...new Set(dates)].sort()
  selectedDate.value = unique[0] ?? ''
}

function submit() {
  form.post(route('parent.register.store'))
}

// ─── UI helpers ───────────────────────────────────────────────

// Selalu ambil YYYY-MM-DD saja — aman dari format ISO penuh maupun string biasa
function dateOnly(val) {
  if (!val) return ''
  return String(val).substring(0, 10)
}

// Parse tanggal tanpa timezone shift
function parseDate(val) {
  const d = dateOnly(val)
  if (!d) return null
  const [y, m, day] = d.split('-').map(Number)
  return new Date(y, m - 1, day)
}

function initials(name) {
  if (!name) return '?'
  return name.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase()
}

function avatarColor(type) {
  return {
    terapis:  'bg-blue-600',
    dokter:   'bg-emerald-600',
    psikolog: 'bg-purple-600',
  }[type] ?? 'bg-gray-500'
}

function formatDate(val) {
  const d = parseDate(val)
  if (!d) return '—'
  return d.toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })
}

function formatDateShort(val) {
  const d = parseDate(val)
  if (!d) return '—'
  return d.toLocaleDateString('id-ID', { weekday: 'short', day: '2-digit', month: 'short' })
}

function formatRupiah(val) {
  return 'Rp ' + Number(val ?? 0).toLocaleString('id-ID')
}
</script>

<template>
  <AppLayout title="Buat Pendaftaran">
    <div class="max-w-2xl space-y-5">

      <div class="flex items-center gap-3">
        <Link :href="route('registrations.index')" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
          <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Buat Pendaftaran</h2>
          <p class="text-sm text-gray-500">Daftarkan pasien ke slot jadwal yang tersedia (1 jam per sesi)</p>
        </div>
      </div>

      <div v-if="availableSlots.length === 0" class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <p class="text-sm font-medium text-amber-800">Belum ada slot jadwal tersedia</p>
        <p class="text-sm text-amber-700 mt-1">Buat slot jadwal terlebih dahulu di menu Kelola Jadwal Terapi.</p>
        <Link :href="route('schedules.create')" class="inline-block mt-3 text-sm font-medium text-blue-600 hover:text-blue-800">
          → Buat Slot Jadwal
        </Link>
      </div>

      <form v-else @submit.prevent="submit" class="space-y-5">

        <!-- Pilih Pasien -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
          <h3 class="font-semibold text-gray-800">Pilih Pasien</h3>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Pasien <span class="text-red-500">*</span></label>
            <select v-model="form.patient_id"
              class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="form.errors.patient_id ? 'border-red-400' : 'border-gray-300'">
              <option value="">-- Pilih Pasien --</option>
              <option v-for="p in patients" :key="p.id" :value="p.id">
                {{ p.name }} ({{ p.medical_record_number }}) — Wali: {{ p.guardian?.name }}
              </option>
            </select>
            <p v-if="form.errors.patient_id" class="mt-1 text-xs text-red-500">{{ form.errors.patient_id }}</p>
          </div>
        </div>

        <!-- Pilih Slot Jadwal -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
          <h3 class="font-semibold text-gray-800">Pilih Slot Jadwal <span class="text-red-500">*</span></h3>

          <select v-model="form.schedule_id"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.schedule_id ? 'border-red-400' : 'border-gray-300'">
            <option value="">-- Pilih Jadwal --</option>
            <option v-for="slot in availableSlots" :key="slot.id" :value="slot.id">
              {{ slot.therapy_type?.name }} · {{ getProfessional(slot) }} · {{ formatDate(slot.schedule_date) }} {{ slot.start_time }}–{{ slot.end_time }}
            </option>
          </select>
          <p v-if="form.errors.schedule_id" class="mt-1 text-xs text-red-500">{{ form.errors.schedule_id }}</p>

          <!-- Detail slot yang dipilih -->
          <div v-if="selectedSlot" class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-semibold text-blue-800">{{ selectedSlot.therapy_type?.name }}</p>
                <p class="text-sm text-blue-700">{{ getProfessional(selectedSlot) }}</p>
                <p class="text-xs text-blue-600 mt-0.5">
                  {{ formatDate(selectedSlot.schedule_date) }} · {{ selectedSlot.start_time }} – {{ selectedSlot.end_time }}
                </p>
              </div>
              <div class="text-right">
                <p class="text-lg font-bold text-blue-800">{{ formatRupiah(selectedSlot.therapy_type?.price_per_hour) }}</p>
                <p class="text-xs text-blue-500">per sesi (1 jam)</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Catatan -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Catatan / Keluhan <span class="text-gray-400 font-normal">(opsional)</span>
          </label>
          <textarea v-model="form.notes" rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Keluhan atau catatan tambahan..." />
        </div>

        <div class="flex items-center justify-end gap-3">
          <Link :href="route('registrations.index')"
            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
            Batal
          </Link>
          <button type="submit" :disabled="form.processing || !canSubmit"
            class="px-5 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors disabled:opacity-50">
            {{ form.processing ? 'Menyimpan...' : 'Buat Pendaftaran' }}
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
  patients:       { type: Array, default: () => [] },
  availableSlots: { type: Array, default: () => [] },
})

const form = useForm({
  patient_id:  '',
  schedule_id: '',
  notes:       '',
})

const selectedSlot = computed(() =>
  props.availableSlots.find(s => s.id === form.schedule_id) ?? null
)

const canSubmit = computed(() => form.patient_id && form.schedule_id)

function submit() {
  form.post(route('registrations.store'))
}

function getProfessional(slot) {
  if (slot.therapist)    return slot.therapist.name
  if (slot.doctor)       return 'Dr. ' + slot.doctor.name
  if (slot.psychologist) return 'Psi. ' + slot.psychologist.nama
  return '—'
}

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })
}

function formatRupiah(val) {
  return 'Rp ' + Number(val ?? 0).toLocaleString('id-ID')
}
</script>

<template>
  <AppLayout title="Catat Pembayaran">
    <div class="max-w-2xl">

      <!-- Back + Header -->
      <div class="flex items-center gap-3 mb-6">
        <Link :href="route('payments.index')"
          class="p-2 rounded-xl hover:bg-gray-100 text-gray-500 hover:text-gray-700 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Catat Pembayaran</h2>
          <p class="text-sm text-gray-500">Input transaksi pembayaran layanan terapi</p>
        </div>
      </div>

      <!-- Info jadwal (jika dibuat dari jadwal) -->
      <div v-if="selectedSchedule" class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-5">
        <p class="text-sm font-medium text-blue-800">Pembayaran untuk Sesi Terapi</p>
        <p class="text-sm text-blue-700 mt-1">
          {{ selectedSchedule.patient?.name }} —
          {{ selectedSchedule.therapy_type?.name }} —
          {{ formatDate(selectedSchedule.schedule_date) }}
        </p>
      </div>

      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
          <h3 class="text-sm font-semibold text-gray-700">Data Pembayaran</h3>
        </div>

        <form @submit.prevent="submit" class="px-6 py-6 space-y-5">

          <!-- Pasien -->
          <FormField label="Pasien" :error="form.errors.patient_id" required>
            <select v-model="form.patient_id" class="form-input"
              :class="{ 'form-input-error': form.errors.patient_id }">
              <option value="" disabled>Pilih pasien</option>
              <option v-for="p in patients" :key="p.id" :value="p.id">
                {{ p.name }} — {{ p.medical_record_number }}
              </option>
            </select>
          </FormField>

          <!-- Jadwal (opsional) -->
          <FormField label="Sesi Terapi" :error="form.errors.schedule_id"
            hint="Opsional — hubungkan dengan jadwal terapi tertentu">
            <select v-model="form.schedule_id" class="form-input">
              <option value="">Tidak terkait jadwal tertentu</option>
              <option v-if="selectedSchedule" :value="selectedSchedule.id">
                {{ selectedSchedule.therapy_type?.name }} — {{ formatDate(selectedSchedule.schedule_date) }}
              </option>
            </select>
          </FormField>

          <!-- 2 kolom: tanggal + jumlah -->
          <div class="grid grid-cols-2 gap-4">
            <FormField label="Tanggal Pembayaran" :error="form.errors.payment_date" required>
              <input v-model="form.payment_date" type="date" class="form-input"
                :class="{ 'form-input-error': form.errors.payment_date }"/>
            </FormField>

            <FormField label="Jumlah (Rp)" :error="form.errors.amount" required>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm pointer-events-none">
                  Rp
                </span>
                <input v-model="form.amount" type="number" min="0" step="1000"
                  placeholder="0" class="form-input pl-9"
                  :class="{ 'form-input-error': form.errors.amount }"/>
              </div>
            </FormField>
          </div>

          <!-- 2 kolom: metode + status -->
          <div class="grid grid-cols-2 gap-4">
            <FormField label="Metode Pembayaran" :error="form.errors.payment_method" required>
              <select v-model="form.payment_method" class="form-input"
                :class="{ 'form-input-error': form.errors.payment_method }">
                <option value="" disabled>Pilih metode</option>
                <option value="Cash">Cash</option>
                <option value="Transfer">Transfer Bank</option>
              </select>
            </FormField>

            <FormField label="Status" :error="form.errors.status" required>
              <select v-model="form.status" class="form-input"
                :class="{ 'form-input-error': form.errors.status }">
                <option value="unpaid">Belum Lunas</option>
                <option value="paid">Lunas</option>
              </select>
            </FormField>
          </div>

          <!-- Catatan -->
          <FormField label="Catatan" :error="form.errors.notes"
            hint="Opsional — keterangan tambahan">
            <textarea v-model="form.notes" rows="3"
              placeholder="Catatan pembayaran..."
              class="form-input resize-none"
              :class="{ 'form-input-error': form.errors.notes }"/>
          </FormField>

          <!-- Preview kode -->
          <div class="bg-gray-50 rounded-xl px-4 py-3 flex items-center gap-3">
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
            </svg>
            <p class="text-xs text-gray-500">
              Kode pembayaran akan di-generate otomatis saat disimpan
              <span class="font-mono text-gray-600">(PAY-YYYYMMDD-XXXX)</span>
            </p>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-3 pt-2">
            <button type="submit" :disabled="form.processing"
              class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded-xl transition-colors">
              <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
              </svg>
              {{ form.processing ? 'Menyimpan...' : 'Simpan Pembayaran' }}
            </button>
            <Link :href="route('payments.index')"
              class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2 rounded-xl hover:bg-gray-100 transition-colors">
              Batal
            </Link>
          </div>

        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import FormField from '@/components/FormField.vue'

const props = defineProps({
  patients:         { type: Array,  required: true },
  selectedSchedule: { type: Object, default: null },
})

const form = useForm({
  patient_id:     props.selectedSchedule?.patient_id ?? '',
  schedule_id:    props.selectedSchedule?.id         ?? '',
  payment_date:   new Date().toISOString().slice(0, 10),
  amount:         '',
  payment_method: '',
  status:         'unpaid',
  notes:          '',
})

function formatDate(val) {
  if (!val) return ''
  return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function submit() {
  form.post(route('payments.store'))
}
</script>

<style scoped>
.form-input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  border: 1px solid #d1d5db;
  border-radius: 0.75rem;
  background-color: white;
  transition: all 0.2s;
}
.form-input:focus {
  outline: none;
  ring: 2px;
  ring-color: #3b82f6;
  border-color: transparent;
}
.form-input:hover {
  border-color: #9ca3af;
}
.form-input-error {
  border-color: #fca5a5;
  background-color: #fef2f2;
}
</style>
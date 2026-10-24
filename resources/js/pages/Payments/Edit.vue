<template>
  <AppLayout title="Edit Pembayaran">
    <div class="max-w-2xl">

      <div class="flex items-center gap-3 mb-6">
        <Link :href="route('payments.show', payment.id)"
          class="p-2 rounded-xl hover:bg-gray-100 text-gray-500 hover:text-gray-700 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Edit Pembayaran</h2>
          <p class="text-sm text-gray-500 font-mono">{{ payment.payment_code }}</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
          <h3 class="text-sm font-semibold text-gray-700">Data Pembayaran</h3>
        </div>

        <form @submit.prevent="submit" class="px-6 py-6 space-y-5">

          <!-- Pasien (read-only — tidak bisa ganti pasien setelah dicatat) -->
          <FormField label="Pasien">
            <div class="form-input bg-gray-50 text-gray-500 cursor-not-allowed">
              {{ payment.patient?.name }} — {{ payment.patient?.medical_record_number }}
            </div>
          </FormField>

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
                  class="form-input pl-9"
                  :class="{ 'form-input-error': form.errors.amount }"/>
              </div>
            </FormField>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <FormField label="Metode Pembayaran" :error="form.errors.payment_method" required>
              <select v-model="form.payment_method" class="form-input"
                :class="{ 'form-input-error': form.errors.payment_method }">
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

          <FormField label="Catatan" :error="form.errors.notes">
            <textarea v-model="form.notes" rows="3" class="form-input resize-none"
              :class="{ 'form-input-error': form.errors.notes }"/>
          </FormField>

          <div class="flex items-center gap-3 pt-2">
            <button type="submit" :disabled="form.processing"
              class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded-xl transition-colors">
              <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
              </svg>
              {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
            <Link :href="route('payments.show', payment.id)"
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
  payment:  { type: Object, required: true },
  patients: { type: Array,  required: true },
})

const form = useForm({
  patient_id:     props.payment.patient_id,
  schedule_id:    props.payment.schedule_id ?? '',
  payment_date:   props.payment.payment_date,
  amount:         props.payment.amount,
  payment_method: props.payment.payment_method,
  status:         props.payment.status,
  notes:          props.payment.notes ?? '',
})

function submit() {
  form.put(route('payments.update', props.payment.id))
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